require('../css/index.css');
document.body.innerHTML = require('../templates/index.hbs')();

var $ = require('jquery'),
    webSocket = new (require('./models/WebSocket.js')),
    _ = require('lodash');


var HeaderView = require('./views/Header.js'),
    header = new HeaderView({el: $('#header-container'), webSocket: webSocket});
header.render();

var CodeMirrorView = require('./views/CodeMirror.js'),
    codeMirror = new CodeMirrorView({el: $('#code-mirror-container'), webSocket: webSocket});
codeMirror.render();
window.editor = codeMirror;

var REPLView = require('./views/REPL.js'),
    REPL = new REPLView({el: $('#REPL-container'), webSocket: webSocket});
REPL.render();

// Right Debugger Panel
// 1. actions
// 2. Call Stack
// 3. Context
// 4. Breakpoints

var DebugActions = require('./views/DebugActions.js'),
    debugActions = new DebugActions({el: $('#debug-actions-container'), webSocket: webSocket});
debugActions.render();

var ContextView = require('./views/Context.js'),
    context = new ContextView({el: $('#context-container'), webSocket: webSocket});
context.render();

var StacksView = require('./views/Stacks.js'),
    stacks = new StacksView({el: $('#stacks-container'), webSocket: webSocket});
stacks.render();

var BreakpointsView = require('./views/Breakpoints.js'),
    breakpoints = new BreakpointsView({el: $('#breakpoints-container'), webSocket: webSocket});
breakpoints.render();

var FileSearchView = require('./views/FileSearch.js'),
    fileSearch = new FileSearchView({el: $('#file-search-container'), webSocket: webSocket});

webSocket.setViews(codeMirror, REPL, breakpoints, context, stacks, fileSearch, debugActions);
