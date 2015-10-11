require('../css/index.css');
document.body.innerHTML = require('../templates/index.hbs')();

var $ = require('jquery'),
    webSocket = new (require('./models/WebSocket.js'));

var CodeMirror = require('./views/CodeMirror.js'),
    codeMirror = new CodeMirror({el: $('#code-mirror-container'), webSocket: webSocket});
codeMirror.render();

var FileTree = require('./views/FileTree.js'),
    fileTree = new FileTree({el: $('#file-tree-container'), webSocket: webSocket});
fileTree.render();

webSocket.setViews(fileTree, codeMirror, false);

//window.codeMirrorPublic = codeMirror;
//var TermJS = require('term.js');

//setTimeout(function() {
//WebSocket.emitSetBreakpoint('/home/sdubois/development/playground/index.php', 7);
//}, 500);

//var term = TermJS({
//  cols: 80,
//  rows: 24,
//  useStyle: true,
//  cursorBlink: true,
//});
//
//term.on('data', function(data) {
//  term.write(data);
//});
//
//term.on('title', function(title) {
//  document.title = title;
//});
//
//term.open(document.getElementById('term'));
