<?php
namespace Therac\Xdebug;

class Base {
    use Handle, Emit;
    protected $Therac;

    private $activeConn;
    private $transaction_id;
    private $breakPoints = [];

    /* Public API */
    public function setBreakpoint($file, $line) {
        $this->breakPoints[] = [
            'file' => $file,
            'line' => $line,
        ];
    }

    public function removeBreakpoint($file, $line) {
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
                $xml = simplexml_load_string(strstr($input, '<'));
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
}
