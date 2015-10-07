<?php
namespace Therac\WebSocket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Base implements MessageComponentInterface {
    protected $clients;
    protected $Therac;

    public function __construct($Therac) {
        $this->Therac = $Therac;
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
