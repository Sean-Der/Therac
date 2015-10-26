require('../../css/FileTree.css');

var FileTreeTemplate = require('../../templates/FileTree.hbs'),
    Handlebars = require("hbsfy/runtime");

Handlebars.registerPartial('FileTree', FileTreeTemplate);

module.exports = require('backbone').View.extend({
    events: {
        "click span.file-tree-directory" : "onOpenDirectory",
        "click span.file-tree-file"      : "onOpenFile",
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
        var parent = e.target.parentElement,
            fileName = parent.getAttribute('data-directory') + parent.getAttribute('data-name');
        this.webSocket.emitSetActiveFile(fileName);
    },
    onOpenDirectory: function(e) {
        var parent = e.target.parentElement,
            directory = parent.getAttribute('data-directory') + parent.getAttribute('data-name');
        this.webSocket.emitGetDirectoryListing(directory);

    },

});
