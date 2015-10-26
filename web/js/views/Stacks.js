require('../../css/Stacks.css');

var StacksTemplate = require('../../templates/Stacks.hbs'),
    StackTemplate = require('../../templates/Stack.hbs'),
    _ = require('lodash');


module.exports = require('backbone').View.extend({
    events: {
        "click [data-frame]" : "_onJumpTo",
    },
    _onJumpTo: function(e) {
        var parentEl = e.currentTarget.parentElement;
        this.webSocket.emitSetActiveFile(parentEl.getAttribute('data-file'));
        this.webSocket.emitSetActiveLine(parentEl.getAttribute('data-line'));
        this.webSocket.emitGetContext(parentEl.getAttribute('data-depth'));
    },

    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    render: function() {
        this.el.innerHTML = StacksTemplate();
    },
    setStacks: function(stacks) {
        var tbody = this.$el.find('[data-call-stack]').empty();
        tbody.empty();
        _.forEach(stacks, function(stack) {
            tbody.append(StackTemplate({
                where: stack['where'],
                file:  stack['file'],
                line:  stack['line'],
                depth: stack['depth'],
            }));
        });
    },
});
