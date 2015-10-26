require('../../css/DebugActions.css');

var DebugActionsTemplate = require('../../templates/DebugActions.hbs'),
    Backbone = require('backbone')
    _ = require('lodash');


module.exports = Backbone.View.extend({
   events: {
        "click a.CodeMirror-step-over" : "_onStepOver",
        "click a.CodeMirror-run" : "_onRun",
        "click a.CodeMirror-step-into" : "_onStepInto",
        "click a.CodeMirror-step-out" : "_onStepOut",
    },

    initialize: function(args) {
        this.webSocket = args.webSocket;
    },

    _onRun: function (){
        this.webSocket.emitRun();
    },
    _onStepOver: function (){
        this.webSocket.emitStepOver();
    },
    _onStepInto: function (){
        this.webSocket.emitStepInto();
    },
    _onStepOut: function (){
        this.webSocket.emitStepOut();
    },

    render: function() {
        this.el.innerHTML = DebugActionsTemplate();
    },
});
