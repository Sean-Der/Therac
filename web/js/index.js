require('../css/index.css');
document.body.innerHTML = require('../templates/index.hbs')();

var $ = require('jquery'),
    webSocket = new (require('./models/WebSocket.js'));

var CodeMirrorView = require('./views/CodeMirror.js'),
    codeMirror = new CodeMirrorView({el: $('#code-mirror-container'), webSocket: webSocket});
codeMirror.render();

var FileTreeView = require('./views/FileTree.js'),
    fileTree = new FileTreeView({el: $('#file-tree-container'), webSocket: webSocket});
fileTree.render();

var REPLView = require('./views/REPL.js'),
    REPL = new REPLView({el: $('#REPL-container'), webSocket: webSocket});
REPL.render();


webSocket.setViews(fileTree, codeMirror, REPL);
