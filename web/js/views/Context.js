require('../../css/Context.css');

var ContextTemplate  = require('../../templates/Context.hbs'),
    ContextsTemplate = require('../../templates/Contexts.hbs'),
    _                = require('lodash'),
    $                = require('jquery');


module.exports = require('backbone').View.extend({
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    render: function() {
        this.el.innerHTML = ContextsTemplate();
    },
    events: {
        "click .context-name .fa": "_onArrowClick",
    },
    _onArrowClick: function(e) {
        var current = $(e.currentTarget),
            parent = $(e.currentTarget).parent();

        if (current.hasClass('fa-angle-up')) {
            parent.find('span').addClass('bold');
            parent.find('.context-value').slideDown();
            parent.find('.fa-angle-up').
                removeClass('fa-angle-up').
                addClass('fa-angle-down');
        } else {
            parent.find('span').removeClass('bold');
            parent.find('.context-value').slideUp();
            parent.find('.fa-angle-down').
                removeClass('fa-angle-down').
                addClass('fa-angle-up');
        }
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
