<?php

namespace Therac\Main;

use React\EventLoop\Factory;
use React\Socket\Server;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;

use Therac\WebSocket\Base as TheracWebSocket;
use Therac\Xdebug\Base as TheracXdebug;

class Therac {
  public $webSocket;
  public $Xdebug;

  public $XDEBUG_PORT;
  public $SEARCH_DIRECTORIES;

  function __construct($xdebugPort, $websocketPort, $searchDirectories) {
    $this->XDEBUG_PORT = $xdebugPort;
    $this->SEARCH_DIRECTORIES = $searchDirectories;

    $loop = Factory::create();

    $xdebugConn = new Server($loop);
    $this->Xdebug = new TheracXdebug($xdebugConn, $this);

    $webSock = new Server($loop);
    $webSock->listen($websocketPort, '0.0.0.0');
    $this->WebSocket = new TheracWebSocket($this);
    new IoServer(new HttpServer(new WsServer($this->WebSocket)), $webSock);

    $loop->run();
  }

}
