<?php
namespace Therac\Xdebug;

trait Connection {

    private $dataCache = '';
    private function connectionLoop() {
        $this->xdebugConn->listen($this->Therac->XDEBUG_PORT);
        $this->xdebugConn->on('connection', function($conn) {

            if (isset($this->activeConn)) {
                return $conn->close();
            }
            $this->setState($conn);

            $this->activeConn->on('data', function ($data) {

                $data = preg_replace( '/[^[:print:]]/', '', $data);
                if (!empty($this->dataCache)) {
                    $data = $this->dataCache . $data;
                    $this->dataCache = '';
                }

                if (!$this->endsWith($data, '</init>')      &&
                    !$this->endsWith($data, '</response>')  &&
                    !$this->endsWith($data, '</stream>')
                ) {
                    return $this->dataCache .= $data;
                }

                $docs = preg_split(self::XML_LEN_HEADER, $data, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($docs as $doc) {
                    $xml = simplexml_load_string($doc);
                    $event = $xml->getName();
                    if (!$event) {
                        return $this->closeActiveConn();
                    }

                    switch ($event) {
                    case 'init':
                        $this->handleInit($xml);
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
    private function endsWith($haystack, $needle) {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

}
