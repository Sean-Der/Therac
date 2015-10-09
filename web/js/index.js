var WebSocket = new (require('./models/WebSocket.js'));

setTimeout(function() {
WebSocket.emitSetBreakpoint('/home/sdubois/development/playground/index.php', 7);
}, 500);

