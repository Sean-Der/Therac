require('../../css/REPL.css');

var TermJS = require('term.js'),
    _ = require('lodash');

module.exports = require('backbone').View.extend({
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    render: function() {
        var self = this;

        this.term = TermJS({
            cols: 120,
            rows: 24,
            useStyle: true,
            cursorBlink: true,
            handler: function(data) {
                // 0x7f is the UTF-8 character for delete
                // this handles backspaces
                if (data === '\x7f') {
                    data = '\b \b';
                }

                self.webSocket.emitREPLInput(data);
            }
        });

        this.term.open(this.el);
    },

    writeInput: function(input) {
        this.term.write(input);
    },
    writeOutput: function(output) {
        this.term.write('\r\n\x1b[32m'+ output + '\x1b[m');
    },
    writeError: function(error) {
        this.term.write('\r\n\x1b[31m'+ error + '\x1b[m');
    },
    writeStdout: function(stdout) {
        this.term.write('\r\n\x1b[33m'+ stdout + '\x1b[m');
    }

});
