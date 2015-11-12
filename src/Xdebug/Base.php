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
    private $activeStack = [];
    private $activeContext = ['depth' => null, 'contexts' => []];
    private $breakOnException = ['transactionId' => 0, 'id' => 0, 'enabled' => false];

    const XML_LEN_HEADER = '/\d+<\?xml version="1.0" encoding="iso-8859-1"\?>/';

    /* Public API */
    public function setBreakpoint($file, $line) {
        if ($this->activeConn) {
            $this->emitBreakpointSet($file, $line);
        }

        $this->breakPoints[] = [
            'file' => $file,
            'line' => $line,
            'transactionId' => null,
            'id' => null,
        ];
    }
    public function removeBreakpoint($file, $line) {
        $this->breakPoints = array_filter($this->breakPoints, function($breakPoint) use ($file, $line) {
            if ($breakPoint['file'] == $file && $breakPoint['line'] == $line) {
                if ($this->activeConn) {
                    $this->emitBreakpointRemove($breakPoint['id']);
                }
                return false;
            }
            return true;
        });
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
    public function getActiveContext() {
        return $this->activeContext;
    }
    public function getActiveStack() {
        return $this->activeStack;
    }

    public function setBreakOnException($enabled) {
        if ($this->activeConn && !$enabled) {
            if ($enabled) {
                $this->emitBreakOnException();
            } else {
                $this->emitBreakpointRemove($this->breakOnException['id']);
            }
        }

        $this->breakOnException['enabled'] = $enabled;
    }
    public function getBreakOnException() {
        return $this->breakOnException['enabled'];
    }

    /* Private API */
    function __construct($xdebugConn, $Therac) {
        $this->Therac = $Therac;
        $this->xdebugConn = $xdebugConn;
        $this->connectionLoop();
    }

    private function valueResponseToString($response) {
        $attributes = $response->attributes();
        $value = (string) $response;
        if (isset($attributes['encoding']) && $attributes['encoding'] == 'base64') {
            $value = base64_decode($value);
        }

        switch ($attributes['type']) {
        case 'null':
        case 'uninitialized':
            return (string )$attributes['type'];
        case 'string':
            return "'$value'";
        case 'float':
        case 'int':
        case 'resource':
            return $value;
        case 'bool':
            return ($value == '0'? 'false':'true');
        case 'object':
            $value = "object({$attributes['classname']}) { ";
            $childCount = count($response->children());
            foreach ($response->children() as $child) {
                $childAttributes = $child->attributes();
                $value .= "{$childAttributes['facet']} \${$childAttributes['name']} = ";
                $value .= $this->valueResponseToString($child);
                if ($childCount-- > 1) {
                    $value .= ', ';
                } else {
                    $value .= ' }';
                }
            }
            return $value;
        case 'array':
            $value = '';
            $childCount = count($response->children());
            foreach ($response->children() as $child) {
                $childAttributes = $child->attributes();

                if (!is_numeric((string) $childAttributes['name'])) {
                    $value .= "'{$childAttributes['name']}'" . ' => ';
                }
                $value .= $this->valueResponseToString($child);

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
