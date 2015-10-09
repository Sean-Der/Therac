<?php
namespace Therac\WebSocket;

trait Emit {
    /* Public API */
    public function emitEvalAtBreakpoint($output) {
        $this->baseEmit('evalAtBreakpoint', [$output]);
    }
    public function emitBreak($file, $line) {
        $this->baseEmit('break', [$file, $line]);
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
