require('../css/index.css');
document.body.innerHTML = require('../templates/index.hbs')();

var $ = require('jquery'),
    webSocket = new (require('./models/WebSocket.js'));

var CodeMirrorView = require('./views/CodeMirror.js'),
    codeMirror = new CodeMirrorView({el: $('#code-mirror-container'), webSocket: webSocket});
codeMirror.render();

var REPLView = require('./views/REPL.js'),
    REPL = new REPLView({el: $('#REPL-container'), webSocket: webSocket});
REPL.render();

var BreakpointsView = require('./views/Breakpoints.js'),
    breakpoints = new BreakpointsView({el: $('#breakpoints-container'), webSocket: webSocket});
breakpoints.render();

var ContextView = require('./views/Context.js'),
    context = new ContextView({el: $('#context-container'), webSocket: webSocket});
context.render();

var StacksView = require('./views/Stacks.js'),
    stacks = new StacksView({el: $('#stacks-container'), webSocket: webSocket});
stacks.render();

webSocket.setViews(codeMirror, REPL, breakpoints, context, stacks);
