require('../../css/Contexts.css');

var ContextTemplate = require('../../templates/Context.hbs'),
    _               = require('lodash');


module.exports = require('backbone').View.extend({
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    render: function() {
        //this.el.innerHTML = BreakpointsTemplate();
    },
    setContexts: function(contexts) {
        _.forEach(contexts, function(context) {
            var currentNode =  this.$el.find('table[data-context="' + context.name + '"]'),
                html = ContextTemplate({name: context.name, values: context.values});

            if (currentNode.length === 0) {
                this.$el.append(html);
            } else {
                currentNode.replaceWith(html);
            }
        }, this);
    },
});
