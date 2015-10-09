<?php
namespace Therac\Xdebug;

trait Handle {
    protected function handleInit($msg) {
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

        if ($cmd === 'run') {
            switch ($attributes['status']) {
            case 'break':
                $childAttributes = $msg->children('xdebug', true)->attributes();
                $this->Therac->WebSocket->emitBreak((string) $childAttributes['filename'], (string) $childAttributes['lineno']);
                break;
            case 'stopping':
                $this->emitRun();
                break;
            default:
                //var_dump($attributes);
            }
        } else if ($cmd === 'eval') {
            $this->Therac->WebSocket->emitEvalAtBreakpoint($this->valueResponseToString($msg->children()));
        }
    }


}
