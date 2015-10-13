<?php
namespace Therac\WebSocket;

use Therac\Main\Therac;

trait Handle {
    protected function handleRun() {
        $this->Therac->Xdebug->emitRun();
    }
    protected function handleStepOver() {
        $this->Therac->Xdebug->emitStepOver();
    }
    protected function handleSetBreakpoint($file, $line) {
        $this->Therac->Xdebug->setBreakpoint(Therac::BASE_DIRECTORY . $file, $line);
        $this->emitBreakpointSet($file, $line);
    }
    protected function handleRemoveBreakpoint($file, $line) {
        $this->Therac->Xdebug->removeBreakpoint(Therac::BASE_DIRECTORY . $file, $line);
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

    //TODO -- make sure these don't escape the project root
    protected function handleGetDirectoryListing($directory) {
        $this->emitDirectoryListing(Therac::BASE_DIRECTORY . $directory);
    }
    protected function handleGetFileContents($file) {
        $this->emitFileContents(Therac::BASE_DIRECTORY . $file);
    }

}
