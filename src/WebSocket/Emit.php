<?php
namespace Therac\WebSocket;

trait Emit {
    /* Public API */
    public function emitEvalAtBreakpoint($output) {
        $this->baseEmit('evalAtBreakpoint', [$output]);
    }

    /* Private API */
    private function baseEmit($event, $data) {
        $json = json_encode([
            'event' => $event,
            'data' => $data,
        ]);
        foreach ($this->clients as $client) {
            $client->send($json);
        }
    }
}
