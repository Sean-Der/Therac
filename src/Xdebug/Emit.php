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

    public function emitEvalAtBreakpoint($line) {
        $encoded = base64_encode($line);
        $this->emitBase("eval {$this->getNewTransactionId()} -- $encoded\00");
    }

    /* Private API */
    private function emitBreakpoint($file, $line) {
        $this->emitBase("breakpoint_set {$this->getNewTransactionId()} -t line -f $file -n $line\00");
    }

    private function emitBase($line) {
        if (!isset($this->activeConn)) {
            return $this->Therac->WebSocket->emitREPLError('No active Xdebug connection');
        }
        $this->activeConn->write($line);
    }

}
