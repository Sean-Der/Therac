<?php
namespace Therac\Xdebug;
use React\Promise\Timer;

trait Handle {

    protected $streamOutCache = '', $streamErrorCache = '';
    protected function handleStream($msg) {
        $attributes = $msg->attributes();
        $value = (string) $msg;
        if (isset($attributes['encoding']) && $attributes['encoding'] == 'base64') {
            $value = base64_decode($value);
        }
        $value = trim($value);
        $value = str_replace("\n", "\r\n", $value);

        if ((string) $attributes['type'] === 'stdout') {
            $this->streamOutCache .= "$value \r\n";
        } else if ((string) $attributes['type'] === 'stderr') {
            $this->streamErrorCache .= "$value \r\n";
        }
    }

    protected function maybeEmitStreamCaches() {
        if (!empty($this->streamOutCache)) {
            $this->Therac->WebSocket->emitREPLStdout($this->streamOutCache);
            $this->streamOutCache = '';
        }

        if (!empty($this->streamErrorCache)) {
            $this->Therac->WebSocket->emitREPLError($this->streamErrorCache);
            $this->streamErrorCache = '';
        }
    }


    protected function handleInit($msg) {
        $this->emitStdout();

        if (empty($this->breakPoints)) {
            return $this->closeActiveConn();
        }
        foreach ($this->breakPoints as $breakPoint) {
            $this->emitBreakpointSet($breakPoint['file'], $breakPoint['line']);
        }
    }

    protected function handleResponse($msg) {
        $attributes = $msg->attributes();
        $cmd = (string) $attributes['command'];

        switch ($cmd) {
        case 'run': case 'step_over': case 'step_out': case 'step_into':
            if ((string) $attributes['status'] === 'break') {
                $childAttributes = $msg->children('xdebug', true)->attributes();
                $file = str_replace('file://', "", $childAttributes['filename']);
                $line = (string) $childAttributes['lineno'];
                $this->activeBreak = [
                    'file' => $file,
                    'line' => $line,
                ];
                $this->Therac->WebSocket->emitFileContents($file, $line);
                $this->emitContextNames();
            } else if ((string) $attributes['status'] === 'stopping') {
                $this->activeBreak = NULL;
                $this->emitRun();
                $this->Therac->WebSocket->emitBreak(null, null);
                $this->closeActiveConn();
            }
            break;
        case 'eval':
            try {
                $this->Therac->WebSocket->emitREPLOutput($this->valueResponseToString($msg->children()));
                $this->emitContextNames();
            } catch (\Exception $e) {
                $this->Therac->WebSocket->emitREPLError($e->getMessage());
            }
            break;
        case 'context_names':
            $this->activeContexts = [];
            foreach ($msg->children() as $child) {
                $this->activeContexts[(string) $child['id']] = [
                    'name'   => (string) $child['name'],
                    'id'     => (string) $child['id'],
                    'values' => [],
                ];
                $this->emitContextGet($child['id']);
            }
            break;
        case 'context_get':
            $contextId = (string) $msg->attributes()['context'];

            foreach ($msg->children() as $child) {
                $childAttributes = $child->attributes();
                try {
                    $this->activeContexts[$contextId]['values'][] =  [
                        'name'  => (string) $childAttributes['name'],
                        'value' => $this->valueResponseToString($child),
                    ];
                } catch (\Exception $e) {
                    var_dump($e);
                }
            }
            $this->Therac->WebSocket->emitActiveContexts();
            break;
        }
        $this->maybeEmitStreamCaches();
    }
}
