<?php
namespace Therac\WebSocket;

trait Handle {
    protected function handleRun($conn) {
        $this->Therac->Xdebug->emitRun();
    }
    protected function handleStepOver($conn) {
        $this->Therac->Xdebug->emitStepOver();
    }
    protected function handleStepInto($conn) {
        $this->Therac->Xdebug->emitStepInto();
    }
    protected function handleStepOut($conn) {
        $this->Therac->Xdebug->emitStepOut();
    }
    protected function handleSetBreakpoint($conn, $file, $line) {
        $this->Therac->Xdebug->setBreakpoint($file, $line);
        $this->emitBreakpointSet($file, $line);
    }
    protected function handleRemoveBreakpoint($conn, $file, $line) {
        $this->Therac->Xdebug->removeBreakpoint($file, $line);
        $this->emitBreakpointRemove($file, $line);
    }
    protected function handleREPLInput($conn, $input) {
        if ($input === "\r") {
            $currentREPLInput = str_replace(self::REPLPrompt, "", end($this->REPLState)['data']);
            $this->Therac->Xdebug->emitEvalAtBreakpoint($currentREPLInput);
            reset($this->REPLState);
        } else {
            $this->emitREPLInput($input);
        }
    }
    protected function handleGetContext($conn, $depth) {
        $this->Therac->Xdebug->emitContextNames($depth);
    }

    //TODO -- make sure these don't escape the project root
    protected function handleFileSearch($conn, $search, $isOpen) {
        $this->activeSearch['results'] = [];
        $this->activeSearch['isOpen'] = $isOpen;
        $this->activeSearch['search'] = $search;
        $this->activeSearch['uniqID'] = $conn->uniqID;


        $explodedSearch = explode('/', $this->activeSearch['search']);
        $searchFile = array_pop($explodedSearch);
        $searchDirectory = array_pop($explodedSearch);
        if ($searchDirectory !== null) {
            $searchDirectory = '/' . $searchDirectory;
        }

        if ($this->activeSearch['isOpen']) {
            $recursiveDirSearch = function() use ($searchDirectory) {
                $dirStack = $this->Therac->SEARCH_DIRECTORIES;
                while ($dir = array_shift($dirStack)) {
                    if (in_array($dir, $this->Therac->EXCLUDED_DIRECTORIES)) {
                        continue;
                    }

                    $subDirs = glob($dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
                    if($subDirs) {
                        $dirStack = array_merge($dirStack, $subDirs);
                    }
                    if ($searchDirectory !== null && substr_compare($dir, $searchDirectory, -strlen($searchDirectory)) !== 0) {
                        continue;
                    }

                    yield $dir;
                }
            };
            foreach ($recursiveDirSearch() as $dir) {
                $this->activeSearch['results'] = array_merge(glob($dir . '/' . $searchFile . '*.php', GLOB_NOSORT), $this->activeSearch['results']);
                if (count($this->activeSearch['results']) >= 25) {
                    $this->activeSearch['results'] = array_slice($this->activeSearch['results'], 0, 25);
                    break;
                }
            }
        }

        $this->emitActiveFileSearch();
    }

    protected function handleSetActiveFile($conn, $file) {
        $this->emitFileContents($file);
    }
    protected function handleSetActiveLine($conn, $line) {
        $this->emitActiveLineSet($line);
    }


}
