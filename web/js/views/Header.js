require('../../css/Header.css');

var HeaderTemplate = require('../../templates/Header.hbs');


module.exports = require('backbone').View.extend({
    events: {
        "click #find-file-container" : "_onFindFileClick",
    },
    _onFindFileClick: function() {
        this.webSocket.emitFileSearch('', true);
    },
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    render: function() {
        this.el.innerHTML = HeaderTemplate();
    },
});
