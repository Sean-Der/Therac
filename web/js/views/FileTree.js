require('../../css/FileTree.css');

var FileTreeTemplate = require('../../templates/FileTree.hbs'),
    Handlebars = require("hbsfy/runtime");

Handlebars.registerPartial('FileTree', FileTreeTemplate);

module.exports = require('backbone').View.extend({
    events: {
        "click li.file-tree-directory" : "onOpenDirectory",
        "click li.file-tree-file" : "onOpenFile",
    },
    initialize: function(args) {
        this.webSocket = args.webSocket;
    },
    renderDirectory: function(directory, children) {
        var el = FileTreeTemplate({children: children, directory: directory});
        if (directory === "") {
            this.el.innerHTML = el;
        } else {
            directory.substring(directory.lastIndexOf('/'));
            var before = directory.substring(0, directory.lastIndexOf('/') + 1),
                after = directory.substring(directory.lastIndexOf('/') + 1);
            this.$el.find('li[data-directory="' + before + '"][data-name="' + after +'"] .file-tree-directory-contents').
                html(el);
        }
    },
    onOpenFile: function(e) {
        var fileName = e.target.getAttribute('data-directory') + e.target.getAttribute('data-name');
        this.webSocket.emitGetFileContents(fileName);
    },
    onOpenDirectory: function(e) {
        var directory = e.target.getAttribute('data-directory') + e.target.getAttribute('data-name');
        this.webSocket.emitGetDirectoryListing(directory);

    },

});
