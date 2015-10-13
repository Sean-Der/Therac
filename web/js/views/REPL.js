require('../../css/REPL.css');

var TermJS = require('term.js'),
    _ = require('lodash');

module.exports = require('backbone').View.extend({
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    _onData: function(data) {
        this.webSocket.emitREPLInput(data);
    },
    render: function() {
        this.term = TermJS({
            cols: 120,
            rows: 24,
            useStyle: true,
            cursorBlink: true,
        });

        this.term.on('data', _.bind(this._onData, this));
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
