#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';
use Therac\Main\Therac;


$options = getopt("x::w::s::e::b::h", [
  "search-directories::",
  "excluded-directories::",
  "xdebug-port::",
  "websocket-port::",
  "blacklisted-files::",
  "help"
]);

if (isset($options['help']) || isset($options['h'])) {
  echo "-h, --help
    Display this help and exit

-s, --search-directories
    Directories that are searchable via the file finder

-e, --exclude-directories
    Directories that are excluded via the file finder

-b, --blacklisted-files
    Files that Therac will never start a debugging session in, even for breaks/exceptions

-x, --xdebug-port
    Port to listen for incoming Xdebug connnections, defaults to 9000

-w, --websocket-port
    Port to listen for incoming Websocket connnections, defaults to 4433";
  return 0;
}

$xdebugPort = 9000;
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

$searchDirectories = ['/var/www/data'];
if (isset($options['search-directories'])) {
  $searchDirectories = $options['search-directories'];
} else if (isset($options['s'])) {
  $searchDirectories = $options['s'];
}

$excludedDirectories = [];
if (isset($options['excluded-directories'])) {
  $excludedDirectories = $options['excluded-directories'];
} else if (isset($options['e'])) {
  $excludedDirectories = $options['e'];
}

$blacklistedFiles = [];
if (isset($options['blacklisted-files'])) {
  $blacklistedFiles = $options['blacklisted-files'];
} else if (isset($options['b'])) {
  $blacklistedFiles = $options['b'];
}


if (!is_array($searchDirectories)) {
  $searchDirectories = [$searchDirectories];
}
if (!is_array($excludedDirectories)) {
  $excludedDirectories = [$excludedDirectories];
}
if (!is_array($blacklistedFiles)) {
  $blacklistedFiles = [$blacklistedFiles];
}


new Therac($xdebugPort, $websocketPort, $searchDirectories, $excludedDirectories, $blacklistedFiles);
