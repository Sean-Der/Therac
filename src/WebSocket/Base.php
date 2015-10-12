<?php
namespace Therac\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Therac\Main\Therac;

class Base implements MessageComponentInterface {
    use Handle, Emit;

    protected $clients;
    protected $Therac;

    private $lastEmittedFile = NULL;

    const REPLInput = 'REPLInput', REPLOutput = 'REPLOutput', REPLError = 'REPLError';
    const REPLPrompt = 'therac> ';

    private $REPLState = [
        ['data' => self::REPLPrompt, 'type' => self::REPLInput]
    ];

    function __construct($Therac) {
        $this->Therac = $Therac;
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        $this->emitDirectoryListing(Therac::BASE_DIRECTORY);

        if ($this->lastEmittedFile) {
            $this->emitFileContents($this->lastEmittedFile);
        }

        foreach ($this->REPLState as $line) {
            $this->baseEmit($line['type'], [$line['data']], [$conn]);
        }

    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $msg = json_decode($msg, true);
        if (isset($msg) && is_array($msg) && isset($msg['event']) && isset($msg['data'])) {
            try {
                $method = new \ReflectionMethod(__CLASS__, 'handle' . $msg['event']);
                if ($method->getNumberOfParameters() === count($msg['data'])) {
                    $method->setAccessible(true);
                    $method->invokeArgs($this, $msg['data']);
                } else {
                    echo "Parameter count mismatch for: {$msg['event']}\n";
                }
            } catch (\Exception $e) {
                echo "No such WebSocket handler: {$e->getMessage()}\n";
            }
        } else {
            echo "Invalid WebSocket Input\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
