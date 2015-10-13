<?php
namespace Therac\Xdebug;

trait Connection {

    private function connectionLoop() {
        $this->xdebugConn->listen($this->listenPort);
        $this->xdebugConn->on('connection', function($conn) {
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
                    case 'stream':
                        $this->handleStream($xml);
                        break;
                    default:
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
        $this->xdebugConn->shutdown();
        sleep(1);
        $this->connectionLoop();
    }
    private function setState($conn) {
            $this->transaction_id = 0;
            $this->activeConn = $conn;
    }


}
