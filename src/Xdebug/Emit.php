<?php
namespace Therac\Xdebug;

trait Emit {

    /* Public API */
    public function emitRun() {
        $this->emitBase("run {$this->getNewTransactionId()}\00");
    }
    public function emitStepOver() {
        $this->emitBase("step_over {$this->getNewTransactionId()}\00");
    }
    public function emitStepInto() {
        $this->emitBase("step_into {$this->getNewTransactionId()}\00");
    }
    public function emitStepOut() {
        $this->emitBase("step_out {$this->getNewTransactionId()}\00");
    }
    public function emitEvalAtBreakpoint($line) {
        $encoded = base64_encode($line);
        $this->emitBase("eval {$this->getNewTransactionId()} -- $encoded\00");
    }
    public function emitContextNames($depth) {
        $this->activeContext['depth'] = $depth;
        $this->emitBase("context_names -d $depth {$this->getNewTransactionId()}\00");
    }

    /* Private API */
    private function emitStackGet() {
        $this->emitBase("stack_get {$this->getNewTransactionId()}\00");
    }

    private function emitContextGet($contextId) {
        $this->emitBase("context_get -c $contextId -d {$this->activeContext['depth']} {$this->getNewTransactionId()}\00");
    }

    private function emitStdout() {
        $this->emitBase("stdout {$this->getNewTransactionId()} -c 1\00");
    }

    private function emitBreakOnException() {
        $transaction_id = $this->getNewTransactionId();
        $this->emitBase("breakpoint_set $transaction_id -t exception -x *\00");
        $this->breakOnException['transactionId'] = str_replace('-i ', '', $transaction_id);
    }

    private function emitBreakpointSet($file, $line) {
        $transaction_id = $this->getNewTransactionId();
        $this->emitBase("breakpoint_set $transaction_id -t line -f file://$file -n $line\00");

        foreach ($this->breakPoints as &$breakPoint) {
            if ($breakPoint['file'] === $file && $breakPoint['line'] === $line) {
                 $breakPoint['transactionId'] = str_replace('-i ', '', $transaction_id);
            }
        }
    }

    private function emitBreakpointRemove($id) {
        $this->emitBase("breakpoint_remove {$this->getNewTransactionId()} -d $id\00");
    }

    private function emitBase($line) {
        if (!isset($this->activeConn)) {
            return $this->Therac->WebSocket->emitREPLError('No active Xdebug connection');
        }
        $this->activeConn->write($line);
    }

}
