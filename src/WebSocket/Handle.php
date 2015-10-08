<?php
namespace Therac\WebSocket;

trait Handle {
    protected function handleSetBreakpoint($file, $line) {
        $this->Therac->Xdebug->setBreakpoint($file, $line);
    }
    protected function handleEvalAtBreakpoint($line) {
        $this->Therac->Xdebug->emitEvalAtBreakpoint($line);
    }
    protected function handleGetDirectory($directory) {
        var_dump($data);
    }
    protected function handleGetFile($file) {
        var_dump($data);
    }

}
