#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';
use Therac\Main\Therac;


$options = getopt("x::w::b::h", [
  "base-directory",
  "xdebug-port::",
  "websocket-port::",
  "help"
]);

if (isset($options['help']) || isset($options['h'])) {
  echo "-h, --help
    Display this help and exit

-b, --base-directory
    Base directory to serve file tree from

-x, --xdebug-port
    Port to listen for incoming Xdebug connnections, defaults to 9089

-w, --websocket-port
    Port to listen for incoming Websocket connnections, defaults to 4433";
  return 0;
}

$xdebugPort = 9089;
if (isset($options['xdebug-port'])) {
  $xdebugPort = $options['xdebug-port'];
} else if (isset($options['x'])) {
  $xdebugPort = $options['x'];
}

$websocketPort = 4433;
if (isset($options['websocket-port'])) {
  $websocketPort = $options['websocket-port'];
} else if (isset($options['w'])) {
  $websocketPort = $options['w'];
}

$baseDirectory = '/var/www/data';
if (isset($options['base-directory'])) {
  $baseDirectory = $options['base-directory'];
} else if (isset($options['b'])) {
  $baseDirectory = $options['b'];
}


new Therac($xdebugPort, $websocketPort, $baseDirectory);
