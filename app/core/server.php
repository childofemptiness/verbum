<?php

namespace App\Core;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketHandler implements MessageComponentInterface{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        echo "New client connected: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "$s" to %d other connections%s' . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection closed, remove it, as we can no longer sent it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occupered: {$e->getMessage()}\n";

        $conn->close();
    }
}