module.exports = require('backbone').Model.extend({
  conn: null,

  initialize: function() {
      this.conn = new WebSocket('ws://' + window.location.host + '/websocket');
      this.conn.onopen = this._onOpen;
      this.conn.onmessage = this._onMessage;
      this.conn.onerror = this._onError;
  },

  /* Emitters */
  emitSetBreakpoint: function(file, line) {
    this._emitterBase('setBreakpoint', [file, line]);
  },
  emitEvalAtBreakpoint: function(line) {
    this._emitterBase('evalAtBreakpoint', [line]);
  },

  /* Handlers */
  handleFoobar: function() {
    debugger;
  },

  /* WebSocket Handlers */
  _onMessage: function(e) {
    console.log(e);
  },
  _onOpen: function() {
  },
  _onError: function(e) {
    console.log(e);
  },
  _emitterBase: function(event, data) {
    this.conn.send(JSON.stringify({
      'event': event,
      'data': data,
    }));

  },



});
