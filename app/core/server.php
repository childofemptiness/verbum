<?php

namespace App\Core;

use Predis\Client;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Core\RedisSessionHandler;

class WebSocketHandler implements MessageComponentInterface {
    protected $clients;
    protected $sessionHandler;
    protected $session;
    protected $queryParams;
    protected $redis;


    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->sessionHandler = new RedisSessionHandler();
        $this->redis = new Client();
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
        else {
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        switch($data->type) {
            
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


    protected function getKeysFromRequest(ConnectionInterface $conn) {
        $queryParams = array();
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
        return $queryParams;
    }

    protected function getSessionData($sessionId) {
        $sessionData = $this->sessionHandler->read($sessionId);
        $sessionDataArray = unserialize($sessionData);
        return $sessionDataArray;
    }

    protected function validateSocketConnection($sessionToken, $clientToken, ConnectionInterface $conn) {
       return $sessionToken === $clientToken;
    }
}
