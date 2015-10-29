require('../../css/Context.css');

var ContextTemplate = require('../../templates/Context.hbs'),
    ContextsTemplate = require('../../templates/Contexts.hbs'),
    _               = require('lodash');


module.exports = require('backbone').View.extend({
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    render: function() {
        this.el.innerHTML = ContextsTemplate();
    },
    setContext: function(context) {
        var $contexts = this.$el.find('[data-contexts]');
        if (_.isNull(context.depth)) {
            $contexts.empty();
        }
        _.forEach(context.contexts, function(context) {
            var currentNode =  $contexts.find('[data-context="' + context.name + '"]'),
                html = ContextTemplate({name: context.name, values: context.values});

            if (currentNode.length === 0) {
                $contexts.append(html);
            } else {
                currentNode.replaceWith(html);
            }
        }, this);
    },
});
