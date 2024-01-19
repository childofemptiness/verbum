<?php

namespace App\Core;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Core\RedisSessionHandler;

class WebSocketHandler implements MessageComponentInterface {
    protected $clients;
    protected $redis;
    protected $session;
    protected $queryParams;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->redis = new RedisSessionHandler();
    }

    public function onOpen(ConnectionInterface $conn) {

        $this->queryParams = $this->getKeysFromRequest($conn);

        $sessionId = $this->queryParams['phpsessid'];

        $this->session = $this->getSessionData($sessionId);

        $clientToken = $this->queryParams['token'];

        if ($this->validateSocketConnection($this->session['token'], $clientToken, $conn)) {
            $this->clients->attach($conn, $this->session['id']);
            echo "New client connected, token verified: {$conn->resourceId}\n";
        }
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
        $addresseeID = $data->userTagValue - 666666;
    }
    protected function getKeysFromRequest(ConnectionInterface $conn) {
        $queryParams = array();
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
        return $queryParams;
    }

    protected function getSessionData($sessionId) {
        $sessionData = $this->redis->read($sessionId);
        $sessionDataArray = unserialize($sessionData);
        return $sessionDataArray;

    }

    protected function validateSocketConnection($sessionToken, $clientToken, ConnectionInterface $conn) {
        if (($sessionToken !== $clientToken)) {
            $conn->close();
        }
        echo "Token verified\n";
    }
}