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

  const WEBSOCK_PORT = 4433;
  const XDEBUG_PORT = 9089;
  const BASE_DIRECTORY = '/home/sdubois/development/playground';

  public function start() {
    $loop = Factory::create();

    $xdebugConn = new Server($loop);
    $xdebugConn->listen(self::XDEBUG_PORT);
    $this->Xdebug = new TheracXdebug($xdebugConn, $this);

    $webSock = new Server($loop);
    $webSock->listen(self::WEBSOCK_PORT, '0.0.0.0');
    $this->WebSocket = new TheracWebSocket($this);
    new IoServer(new HttpServer(new WsServer($this->WebSocket)), $webSock);

    $loop->run();
  }

}
