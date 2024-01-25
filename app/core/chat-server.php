<?php

namespace App\Core;
// РАЗОБРАТЬСЯ, ПОЧЕМУ НЕ РАБОТАЕТ АВТОЗАГРУЗКА
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(__FILE__) . DS . "app");

require_once('../../vendor/autoload.php');
require_once('../config/config.php');
require_once('./server.php');

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketHandler()
        )
    ),
    8081
);
echo "Server started\n";
$server->run();
