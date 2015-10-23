require('../../css/Context.css');

var ContextTemplate = require('../../templates/Context.hbs'),
    _               = require('lodash');


module.exports = require('backbone').View.extend({
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    setContext: function(context) {
        if (_.isNull(context.depth)) {
            this.$el.empty();
        }
        _.forEach(context.contexts, function(context) {
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
