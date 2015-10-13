<?php
namespace Therac\Xdebug;

class Base {
    use Handle, Emit, Connection;
    protected $Therac;
    protected $xdebugConn;

    private $activeConn;
    private $transaction_id;
    private $breakPoints = [];
    private $activeBreak = NULL;

    const XML_LEN_HEADER = '/\d+<\?xml version="1.0" encoding="iso-8859-1"\?>/';

    /* Public API */
    public function setBreakpoint($file, $line) {
        $this->breakPoints[] = [
            'file' => $file,
            'line' => $line,
        ];
    }
    public function removeBreakpoint($file, $line) {
    }

    public function getBreakpoints($file) {
        $breakPoints = $this->breakPoints;
        if ($file) {
            $breakPoints = array_filter($breakPoints, function($breakPoint) use ($file) {
                return ($breakPoint['file'] === $file);
            });
        }
        return $breakPoints;

    }
    public function getActiveBreak() {
        return $this->activeBreak;
    }

    /* Private API */
    function __construct($xdebugConn, $listenPort, $Therac) {
        $this->Therac = $Therac;
        $this->xdebugConn = $xdebugConn;
        $this->listenPort = $listenPort;
        $this->connectionLoop();
    }

    private function valueResponseToString($response) {
        $attributes = $response->attributes();
        $value = (string) $response;
        if (isset($attributes['encoding']) && $attributes['encoding'] == 'base64') {
            $value = base64_decode($value);
        }

        switch ($attributes['type']) {
        case 'string':
            return "\"$value\"";
        case 'int':
            return $value;
        case 'bool':
            return ($value == '0'? 'false':'true');
        case 'null':
            return 'null';
        case 'array':
            $value = '';
            $childCount = count($response->children());
            foreach ($response->children() as $child) {
                $value = $value . $this->valueResponseToString($child);
                if ($childCount-- > 1) {
                    $value = $value . ', ';
                }
            }
            return "[ $value ]";
        default:
            throw new \Exception('failed to decode ' . $attributes['type']);
        }
    }

}
