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

    public function emitDirectoryListing($directory) {
        $scanResult = [];
        foreach(array_diff(scandir($directory), array('..', '.')) as $file) {
            $scanResult[] = [
                'name' => $file,
                'isDir' => is_dir("$directory/$file"),
            ];
        }
        $relativeDirectory = str_replace($this->Therac->BASE_DIRECTORY, "", $directory);
        $this->baseEmit('directoryListing', [$relativeDirectory, $scanResult]);
    }

    public function emitFileContents($file) {
        $this->lastEmittedFile = $file;
        $relativeDirectory = str_replace($file, "", str_replace($this->Therac->BASE_DIRECTORY, "", $file));
        $this->baseEmit('fileContents', [$relativeDirectory, file_get_contents($file)]);

        if (($break = $this->Therac->Xdebug->getActiveBreak()) != NULL && $break['file'] === $file) {
                $this->baseEmit('break', [$break['file'], $break['line']]);
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
