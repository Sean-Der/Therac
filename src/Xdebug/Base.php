<?php
namespace Therac\Xdebug;

class Base {
    use Handle, Emit;
    protected $Therac;

    private $activeConn;
    private $transaction_id;
    private $breakPoints = [];
    private $activeBreak = NULL;

    const XML_LEN_HEADER = '/<\?xml version="1.0" encoding="iso-8859-1"\?>/';

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
    function __construct($xdebugConn, $Therac) {
        $this->Therac = $Therac;
        $xdebugConn->on('connection', function($conn) {
            if (isset($this->activeConn)) {
                return $conn->close();
            }
            $this->setState($conn);

            $this->activeConn->on('data', function ($input) {
                $docs = preg_split(self::XML_LEN_HEADER, $input, -1, PREG_SPLIT_NO_EMPTY);
                for ($i = 0; $i < count($docs); $i++) {
                    if (($i % 2) == 0) unset ($docs[$i]);
                }

                foreach ($docs as $doc) {
                    $xml = simplexml_load_string($doc);
                    $event = $xml->getName();
                    if (!$event) {
                        return $this->closeActiveConn();
                    }

                    switch ($event) {
                    case 'init':
                        $this->handleInit($xml);
                        if (isset($this->activeConn)) {
                            $this->emitRun();
                        }
                        break;
                    case 'response':
                        $this->handleResponse($xml);
                        break;
                    default:
                        var_dump($input);
                        $this->closeActiveConn();
                    }

                }

            });

        });
    }

    private function getNewTransactionId () {
        return '-i ' . $this->transaction_id++;
    }
    private function closeActiveConn() {
        $this->activeConn->close();
        $this->activeConn = null;
    }
    private function setState($conn) {
            $this->transaction_id = 0;
            $this->activeConn = $conn;
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
