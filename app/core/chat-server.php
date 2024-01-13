<?php

namespace App\Core;
// РАЗОБРАТЬСЯ, ПОЧЕМУ НЕ РАБОТАЕТ АВТОЗАГРУЗКА
require_once('../../vendor/autoload.php');
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