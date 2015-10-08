<?php
namespace Therac\Xdebug;

trait Handle {
    protected function handleInit($msg) {
        if (empty($this->breakPoints)) {
            return $this->closeActiveConn();
        }
        foreach ($this->breakPoints as $breakPoint) {
            var_dump($breakPoint);
            $this->emitBreakpoint($breakPoint['file'], $breakPoint['line']);
        }
    }

    protected function handleResponse($msg) {
        $attributes = $msg->attributes();
        $cmd = (string) $attributes['command'];

        if ($cmd === 'run') {
            switch ($attributes['status']) {
            case 'break':
                break;
            case 'stopping':
                $this->emitRun();
                break;
            default:
                //var_dump($attributes);
            }
        } else if ($cmd === 'eval') {
            $this->Therac->WebSocket->emitEvalAtBreakpoint($this->evalResponseToString($msg->children()));
        }
    }


    private function evalResponseToString($response) {
        $attributes = $response->attributes();
        $value = (string) $response;
        if (isset($attributes['encoding']) && $attributes['encoding'] == 'base64') {
            $value = base64_decode($value);
        }

        switch ($attributes['type']) {
        case 'int':
        case 'string':
        case 'bool':
        case 'null':
            return $value;
        case 'array':
            var_dump($response);
        default:
            return 'failed to decode ' . $attributes['type'];

        }
    }
}
