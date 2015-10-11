<?php
namespace Therac\WebSocket;

use Therac\Main\Therac;

trait Handle {
    protected function handleSetBreakpoint($file, $line) {
        $this->Therac->Xdebug->setBreakpoint(Therac::BASE_DIRECTORY . $file, $line);
        $this->emitBreakpointSet($file, $line);
    }
    protected function handleEvalAtBreakpoint($line) {
        $this->Therac->Xdebug->emitEvalAtBreakpoint($line);
    }

    //TODO -- make sure these don't escape the project root
    protected function handleGetDirectoryListing($directory) {
        $this->emitDirectoryListing(Therac::BASE_DIRECTORY . $directory);
    }
    protected function handleGetFileContents($file) {
        $this->emitFileContents(Therac::BASE_DIRECTORY . $file);
    }

}
