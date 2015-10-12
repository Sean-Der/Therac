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
                $this->emitBreakpoint(null, null);
                $this->emitRun();
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
    }


}
