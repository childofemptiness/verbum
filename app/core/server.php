<?php

namespace App\Core;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Core\RedisSessionHandler;
use Predis\Client;

class WebSocketHandler implements MessageComponentInterface {
    protected $clients;
    private $redis;
    protected $sessData;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->redis = new RedisSessionHandler();
    }

    public function onOpen(ConnectionInterface $conn) {
        $sessionId = $this->getSessionIdFromRequest($conn);

        $sessData = $this->redis->read($sessionId);

        $sessionDataArray = unserialize($sessData);

        $this->clients->attach($conn);
        echo "New client connected: {$conn->resourceId}\n";
        $token = $this->getTokenFromRequest($conn);
        $this->validateJWTToken($token, $sessionDataArray['token'], $conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        
        $data = json_decode($msg);
        switch($data->type) {
            case 'friend-request';
            $this->handleFriendRequest($from, $data);
            break;
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

    public function handleFriendRequest(ConnectionInterface $from, $data) {

    }
    protected function getTokenFromRequest(ConnectionInterface $conn) {
        $queryParams = array();
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
        $token = $queryParams['token'] ?? null;
        return $token;
    }

    protected function getSessionIdFromRequest(ConnectionInterface $conn) {
        $queryParams = array();
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
        $sessionId = $queryParams['phpsessid'];
        return $sessionId;
    }

    protected function validateJWTToken($sessionToken, $clientToken, ConnectionInterface $conn) {
        echo $sessionToken . "\n";
        echo $clientToken . "\n";
        if (($sessionToken !== $clientToken)) {
            $conn->close();
        }
        echo "Token verified\n";
    }
 
}