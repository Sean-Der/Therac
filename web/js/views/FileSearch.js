require('../../css/FileSearch.css');

var FileSearchTemplate = require('../../templates/FileSearch.hbs'),
    _               = require('lodash');


module.exports = require('backbone').View.extend({
    isOpen: null,
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    events: {
        "keypress #file-search-input" : "_onFileSearch",
        "click .file-search-result" : "_onResultClick",
    },
    _onFileSearch: function(e) {
        this.webSocket.emitFileSearch(e.target.value + String.fromCharCode(e.keyCode), true);
    },
    _onResultClick: function(e) {
        this.webSocket.emitFileSearch('', false);
        this.webSocket.emitSetActiveFile(e.target.innerText);
    },

    setSearch: function(search, isOpen, results) {
        this.isOpen = isOpen;
        this.el.innerHTML = FileSearchTemplate({search: search, isOpen: isOpen, results: results});

        var input = this.$el.find('#file-search-input'),
            val = input.val();

        input.focus();
        input.val('');
        input.val(val);
    },
    handleOpen: function(e) {
        if (e.keyCode == 79 && e.ctrlKey) {
            this.webSocket.emitFileSearch('', !this.isOpen);
        }
    }
});
