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
            $this->emitBreakpoint($breakPoint['file'], $breakPoint['line']);
        }
    }

    protected function handleResponse($msg) {
        $attributes = $msg->attributes();
        $cmd = (string) $attributes['command'];

        if ($cmd === 'run' || $cmd === 'step_over') {
            switch ($attributes['status']) {
            case 'break':
                $childAttributes = $msg->children('xdebug', true)->attributes();
                $file = str_replace('file://', "", $childAttributes['filename']);
                $this->activeBreak = [
                    'file' => $file,
                    'line' => (string) $childAttributes['lineno'],
                ];
                $this->Therac->WebSocket->emitFileContents($file);
                break;
            case 'stopping':
                $this->activeBreak = NULL;
                $this->emitRun();
                $this->Therac->WebSocket->emitBreak(null, null);
                $this->closeActiveConn();
                break;
            default:
                //var_dump($attributes);
            }
        } else if ($cmd === 'eval') {
            try {
                $this->Therac->WebSocket->emitREPLOutput($this->valueResponseToString($msg->children()));
            } catch (\Exception $e) {
                $this->Therac->WebSocket->emitREPLError($e->getMessage());
            }
        }

        $this->maybeEmitStreamCaches();
    }
}
