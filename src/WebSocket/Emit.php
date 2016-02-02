<?php
namespace Therac\WebSocket;

trait Emit {
    /* Public API */
    public function emitBreakpointSet($file, $line) {
        $this->baseEmit('breakPointSet', [$file, $line]);
    }
    public function emitBreakpointRemove($file, $line) {
        $this->baseEmit('breakPointRemove', [$file, $line]);
    }
    public function emitBreak($file, $line) {
        $this->baseEmit('break', [$file, $line]);
    }

    public function emitFileContents($file) {
        $this->activeFile['file'] = $file;
        $this->baseEmit('fileContents', [$file, file_get_contents($file)]);

        if (($break = $this->Therac->Xdebug->getActiveBreak()) != NULL && $break['file'] === $file) {
            $this->emitBreak($break['file'], $break['line']);
        }
        foreach($this->Therac->Xdebug->getBreakpoints($file) as $breakPoint) {
            $this->emitBreakpointSet($breakPoint['file'], $breakPoint['line']);
        }
    }

    public function emitREPLInput($input) {
        $this->baseREPL(self::REPLInput, $input);
    }
    public function emitREPLOutput($output) {
        $this->baseREPL(self::REPLOutput, $output);
        $this->newREPLPrompt();
    }
    public function emitREPLError($error) {
        $this->baseREPL(self::REPLError, $error);
        $this->newREPLPrompt();
    }
    public function emitREPLStdout($error) {
        $this->baseREPL(self::REPLStdout, $error);
        $this->newREPLPrompt();
    }

    public function emitActiveContext() {
        $this->baseEmit('activeContext', [$this->Therac->Xdebug->getActiveContext()]);
    }

    public function emitActiveLineSet($line = null) {
        if ($line !== null) {
            $this->activeFile['line'] = $line;
        }
        $this->baseEmit('activeLineSet', [$this->activeFile['line']]);
    }

    public function emitActiveStack() {
        $this->baseEmit('activeStack', [$this->Therac->Xdebug->getActiveStack()]);
    }

    public function emitActiveFileSearch() {
        $this->baseEmit('activeFileSearch', [$this->activeSearch['search'], $this->activeSearch['isOpen'], $this->activeSearch['results'], $this->activeSearch['uniqID']]);
    }

    public function emitUniqID($conn) {
        $this->baseEmit('uniqID', [$conn->uniqID], [$conn]);
    }

    public function emitBreakOnExceptionSet() {
        $this->baseEmit('breakOnExceptionSet', [ $this->Therac->Xdebug->getBreakOnException() ]);
    }

    /* Private API */
    private function baseREPL($type, $data) {
        $lastLine = end($this->REPLState);
        if ($type === self::REPLInput && $type === $lastLine['type']) {
            $newLine = array_pop($this->REPLState);

            if ("\x08 \x08" == $data) {
                if ($newLine['data'] === self::REPLPrompt) {
                    $data = '';
                } else {
                    $newLine['data'] = substr($newLine['data'], 0, -1);
                }
            } else {
                $newLine['data'] .= $data;
            }

        } else {
            if ($type !== self::REPLInput) {
                $data .= "\r\n";
            }

            $newLine = [
                'type'  => $type,
                'data' =>  $data,
            ];
        }

        $this->REPLState[] = $newLine;
        if (count($this->REPLState) > 250) {
            array_shift($this->REPLState);
        }

        $this->baseEmit($type, [$data]);
    }

    private function newREPLPrompt() {
        $this->emitREPLInput(self::REPLPrompt);
    }

    private function baseEmit($event, $data, $clients = []) {
        $json = json_encode([
            'event' => $event,
            'data' => $data,
        ], JSON_UNESCAPED_SLASHES);

        $clients = (empty($clients)) ? $this->clients : $clients;
        foreach ($clients as $client) {
            $client->send($json);
        }
    }
}
