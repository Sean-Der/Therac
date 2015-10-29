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
        $this->Therac->Xdebug->setBreakpoint($file, $line);
        $this->emitBreakpointSet($file, $line);
    }
    protected function handleRemoveBreakpoint($file, $line) {
        $this->Therac->Xdebug->removeBreakpoint($file, $line);
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
    protected function handleGetContext($depth) {
        $this->Therac->Xdebug->emitContextNames($depth);
    }

    //TODO -- make sure these don't escape the project root
    protected function handleFileSearch($search, $isOpen) {
        $this->activeSearch['results'] = [];
        $this->activeSearch['isOpen'] = $isOpen;
        $this->activeSearch['search'] = $search;


        if ($this->activeSearch['isOpen']) {
            $recursiveDirSearch = function() {
                $dirStack = $this->Therac->SEARCH_DIRECTORIES;
                while ($dir = array_shift($dirStack)) {
                    $subDirs = glob($dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
                    if($subDirs) {
                        $dirStack = array_merge($dirStack, $subDirs);
                    }
                    yield $dir;
                }
            };
            foreach ($recursiveDirSearch() as $dir) {
                $this->activeSearch['results'] = array_merge(glob($dir . '/' . $this->activeSearch['search'] . '*.php', GLOB_NOSORT), $this->activeSearch['results']);
                if (count($this->activeSearch['results']) >= 25) {
                    break;
                }
            }
        }

        $this->emitActiveFileSearch();
    }

    protected function handleSetActiveFile($file) {
        $this->emitFileContents($file);
    }
    protected function handleSetActiveLine($line) {
        $this->emitActiveLineSet($line);
    }


}
