var _ = require('lodash'),
    $ = require('jquery');

module.exports = require('backbone').Model.extend({
  conn: null,
  viewsSet: $.Deferred(),

  initialize: function() {
      this.conn = new WebSocket('ws://' + window.location.host + '/websocket');
      this.conn.onopen = this._onOpen;
      this.conn.onmessage = _.bind(this._onMessage, this);
      this.conn.onerror = this._onError;
  },

  /* Public API */
  setViews: function(fileTree, codeMirror, REPL, breakpoints, contexts) {
    this.fileTree = fileTree;
    this.codeMirror = codeMirror;
    this.REPL = REPL;
    this.breakpoints = breakpoints;
    this.contexts = contexts;

    this.viewsSet.resolve();
  },

  emitGetFileContents: function(file) {
    this._emitterBase('getFileContents', [file]);
  },
  emitGetDirectoryListing: function(directory) {
    this._emitterBase('getDirectoryListing', [directory]);
  },
  emitSetBreakpoint: function(file, line) {
    this._emitterBase('setBreakpoint', [file, line]);
  },
  emitRemoveBreakpoint: function(file, line) {
    this._emitterBase('removeBreakpoint', [file, line]);
  },
  emitREPLInput: function(input) {
    this._emitterBase('REPLInput', [input]);
  },
  emitRun: function() {
    this._emitterBase('run', []);
  },
  emitStepOver: function() {
    this._emitterBase('stepOver', []);
  },
  emitStepInto: function() {
    this._emitterBase('stepInto', []);
  },
  emitStepOut: function() {
    this._emitterBase('stepOut', []);
  },

  /* Handlers */
  _handleDirectoryListing: function(directory, children) {
    this.fileTree.renderDirectory(directory, children);
  },
  _handleFileContents: function(file, fileContents) {
    this.codeMirror.setEditorValue(file, fileContents);
  },
  _handleBreakPointSet: function(file, lineNum) {
    this.codeMirror.setBreakpoint(file, lineNum);
    this.breakpoints.setBreakpoint(file, lineNum);
  },
  _handleBreakPointRemove: function(file, lineNum) {
    this.codeMirror.removeBreakpoint(file, lineNum);
    this.breakpoints.removeBreakpoint(file, lineNum);
  },
  _handleBreak: function(file, lineNum) {
    this.codeMirror.setBreak(file, lineNum);
  },
  _handleREPLInput: function(input) {
    this.REPL.writeInput(input);
  },
  _handleREPLOutput: function(output) {
    this.REPL.writeOutput(output);
  },
  _handleREPLError: function(error) {
    this.REPL.writeError(error);
  },
  _handleREPLStdout: function(stdout) {
    this.REPL.writeStdout(stdout);
  },
  _handleActiveContexts: function(contexts) {
    this.contexts.setContexts(contexts);
  },
  _handleActiveLineSet: function(line) {
    this.codeMirror.setActiveLine(line);
  },

  _onMessage: function(e) {
    var msg = JSON.parse(e.data),
        handler = this['_handle' + _.capitalize(msg.event)];

    if (!_.isFunction(handler)) {
      throw 'No WebSocket handler for ' + msg.event;
    }
    this.viewsSet.then(_.bind(function() {
      handler.apply(this, msg.data);
    }, this));
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
