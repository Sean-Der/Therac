<?php
namespace Therac\WebSocket;

trait Handle {
    protected function handleRun() {
        $this->Therac->Xdebug->emitRun();
    }
    protected function handleStepOver() {
        $this->Therac->Xdebug->emitStepOver();
    }
    protected function handleStepInto() {
        $this->Therac->Xdebug->emitStepInto();
    }
    protected function handleStepOut() {
        $this->Therac->Xdebug->emitStepOut();
    }
    protected function handleSetBreakpoint($file, $line) {
        $this->Therac->Xdebug->setBreakpoint($this->Therac->BASE_DIRECTORY . $file, $line);
        $this->emitBreakpointSet($file, $line);
    }
    protected function handleRemoveBreakpoint($file, $line) {
        $this->Therac->Xdebug->removeBreakpoint($this->Therac->BASE_DIRECTORY . $file, $line);
        $this->emitBreakpointRemove($file, $line);
    }
    protected function handleREPLInput($input) {
        if ($input === "\r") {
            $currentREPLInput = str_replace(self::REPLPrompt, "", end($this->REPLState)['data']);
            $this->Therac->Xdebug->emitEvalAtBreakpoint($currentREPLInput);
            reset($this->REPLState);
        } else {
            $this->emitREPLInput($input);
        }
    }
    protected function handleSetActiveLine($line) {
        $this->emitActiveLineSet($line);
    }


    //TODO -- make sure these don't escape the project root
    protected function handleGetDirectoryListing($directory) {
        $this->emitDirectoryListing($this->Therac->BASE_DIRECTORY . $directory);
    }
    protected function handleGetFileContents($file) {
        $this->emitFileContents($this->Therac->BASE_DIRECTORY . $file, 0);
    }

}
