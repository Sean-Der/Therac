<?php
namespace Therac\WebSocket;

use Therac\Main\Therac;

trait Emit {
    /* Public API */
    public function emitEvalAtBreakpoint($output) {
        $this->baseEmit('evalAtBreakpoint', [$output]);
    }
    public function emitBreak($file, $line) {
        $fullFile = str_replace('file://', "", $file);
        $relativeFile = str_replace(Therac::BASE_DIRECTORY, "", $fullFile);

        $this->emitFileContents($fullFile);
        $this->baseEmit('break', [$relativeFile, $line]);
    }
    public function emitDirectoryListing($directory) {
        $scanResult = [];
        foreach(array_diff(scandir($directory), array('..', '.')) as $file) {
            $scanResult[] = [
                'name' => $file,
                'isDir' => is_dir("$directory/$file"),
            ];
        }
        $relativeDirectory = str_replace(Therac::BASE_DIRECTORY, "", $directory);
        $this->baseEmit('directoryListing', [$relativeDirectory, $scanResult]);
    }
    public function emitFileContents($file) {
        $relativeDirectory = str_replace($file, "", str_replace(Therac::BASE_DIRECTORY, "", $file));
        $this->baseEmit('fileContents', [$relativeDirectory, file_get_contents($file)]);
    }
    public function emitBreakpointSet($file, $line) {
        $this->baseEmit('breakPointSet', [$file, $line]);
    }

    /* Private API */
    private function baseEmit($event, $data) {
        $json = json_encode([
            'event' => $event,
            'data' => $data,
        ], JSON_UNESCAPED_SLASHES);
        foreach ($this->clients as $client) {
            $client->send($json);
        }
    }
}
