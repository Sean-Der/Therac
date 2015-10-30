require('../../css/Breakpoints.css');

var BreakpointsTemplate = require('../../templates/Breakpoints.hbs'),
    BreakpointTemplate = require('../../templates/Breakpoint.hbs');


module.exports = require('backbone').View.extend({
    events: {
        "click [data-remove-breakpoint]" : "_onRemoveBreakpoint",
        "click [data-breakpoint]" : "_onShowBreakpoint",
    },
    _onRemoveBreakpoint: function(e) {
        var parentEl = e.currentTarget.parentElement;
        this.webSocket.emitRemoveBreakpoint(parentEl.getAttribute('data-file'), parentEl.getAttribute('data-line'));
    },
    _onShowBreakpoint: function(e) {
        var parentEl = e.currentTarget.parentElement;
        this.webSocket.emitSetActiveFile(parentEl.getAttribute('data-file'));
        this.webSocket.emitSetActiveLine(parentEl.getAttribute('data-line'));
    },

    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    render: function() {
        this.el.innerHTML = BreakpointsTemplate();
    },
    setBreakpoint: function(file, line) {
        if (this.$el.find('[data-file="' + file + '"][data-line="' + line + '"]').length === 0) {
            this.$el.find('[data-breakpoints]').append(BreakpointTemplate({file: file, line: line}));
        }
    },
    removeBreakpoint: function(file, line) {
       this.$el.find('div[data-file="' + file + '"][data-line="' + line + '"]').remove();
    },
});
