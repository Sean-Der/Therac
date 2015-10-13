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

    private function emitStdout() {
        $this->emitBase("stdout {$this->getNewTransactionId()} -c 1\00");
    }

    /* Private API */
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
