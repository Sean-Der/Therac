require('../../css/Breakpoints.css');

var BreakpointsTemplate = require('../../templates/Breakpoints.hbs'),
    BreakpointTemplate = require('../../templates/Breakpoint.hbs');


module.exports = require('backbone').View.extend({
    events: {
        "click [data-remove-breakpoint]" : "_onRemoveBreakpoint",
    },
    _onRemoveBreakpoint: function(e) {
        var parentEl = e.currentTarget.parentElement;
        this.webSocket.emitRemoveBreakpoint(parentEl.getAttribute('data-file'), parentEl.getAttribute('data-line'));
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
       this.$el.find('tr[data-file="' + file + '"][data-line="' + line + '"]').remove();
    },
});
