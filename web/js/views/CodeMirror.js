require('../../css/CodeMirror.css');

var CodeMirror = require('codemirror'),
    _ = require('lodash'),
    CodeMirrorPanel = require('../../templates/CodeMirrorPanel.hbs'),
    $ = require('jquery');


require('codemirror/addon/edit/matchbrackets.js');
require('codemirror/addon/selection/active-line.js');

require('codemirror/mode/htmlmixed/htmlmixed.js');
require('codemirror/mode/javascript/javascript.js');
require('codemirror/mode/xml/xml.js');
require('codemirror/mode/css/css.js');
require('codemirror/mode/clike/clike.js');
require('codemirror/mode/php/php.js');

module.exports = require('backbone').View.extend({
    BREAK_CSS_CLASS: 'CodeMirror-linebreak',
    ACTIVE_CSS_CLASS: 'CodeMirror-activeline',
    BREAKPOINT_CSS_CLASS: "CodeMirror-breakpoints",

    activeFile: '',
    currentBreak: null,
    activeLine: null,


    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    render: function() {
        this.editor = CodeMirror(this.el, {
            mode:  "php",
            lineNumbers: true,
            matchBrackets: true,
            readOnly: true,
            showCursorWhenSelecting: false,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-breakpoints"],
            viewportMargin: Infinity,
        });

        this.editor.on("gutterClick", _.bind(function(cm, n) {
            if (this.editor.lineInfo(n).gutterMarkers) {
                this.webSocket.emitRemoveBreakpoint(this.activeFile, n + 1);
            } else {
                this.webSocket.emitSetBreakpoint(this.activeFile, n + 1);
            }
        }, this));

        var lineHeight = this.editor.defaultTextHeight();
        this.editor.on("scroll", _.bind(function(cm) {
            if (cm.state.focused === true) {
                var scrollInfo = cm.getScrollInfo();
                this.webSocket.emitSetActiveLine(Math.ceil(((scrollInfo.clientHeight / 2) + scrollInfo.top) / lineHeight));

            }
        }, this));

        this.onWindowResize();
        $(window).resize(_.bind(this.onWindowResize, this));

    },
    setEditorValue: function(file, value) {
        this.activeFile = file;
        this.editor.setValue(value);
    },
    setBreakpoint: function(file, lineNum) {
        if (file !== this.activeFile) {
            return
        }
        var marker = document.createElement("div");
        marker.style.color = "red";
        marker.innerHTML = "➜";
        this.editor.setGutterMarker(lineNum - 1, this.BREAKPOINT_CSS_CLASS, marker);
    },
    removeBreakpoint: function(file, lineNum) {
        this.editor.setGutterMarker(lineNum - 1, this.BREAKPOINT_CSS_CLASS, null);
    },
    setBreak: function(file, lineNum) {
        if (file === null || lineNum === null) {
            if (this.currentBreak !== null) {
                this.editor.removeLineClass(this.currentBreak, 'background', this.BREAK_CSS_CLASS);
            }
            this.currentBreak = null;
        } else {
            this.editor.scrollIntoView({line: lineNum, ch: 0});
            lineNum = parseInt(lineNum) - 1;
            this.currentBreak = lineNum;
            this.editor.addLineClass(lineNum, 'background', this.BREAK_CSS_CLASS);
        }
    },
    setActiveLine: function(lineNum) {
        if (this.activeLine !== null) {
            this.editor.removeLineClass(this.activeLine, 'background', this.ACTIVE_CSS_CLASS);
            this.activeLine = null;
        }

        if (lineNum === null) {
            return
        }
        this.editor.scrollIntoView({line: lineNum, ch: 0});

        lineNum = parseInt(lineNum) - 1;
        this.activeLine = lineNum;
        this.editor.addLineClass(lineNum, 'background', this.ACTIVE_CSS_CLASS);

    },
    onWindowResize: function() {
        var parent = this.$el.parent()
        this.editor.setSize(this.$el.width() - 10, (parent.height() - parent.children().first().height()) - 10);
        this.editor.refresh();
    },
});
