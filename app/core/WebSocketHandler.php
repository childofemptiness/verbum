<?php

namespace App\Core;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Core\RedisSessionHandler;
use App\Models\MessagesModel;
use stdClass;

class WebSocketHandler implements MessageComponentInterface {
    protected $clients;
    protected $sessionHandler;
    protected $session;
    protected $queryParams;
    private $messages;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->sessionHandler = new RedisSessionHandler();
        $this->messages = new MessagesModel();
    }

    public function onOpen(ConnectionInterface $conn) {

        $this->queryParams = $this->getKeysFromRequest($conn);

        $sessionId = $this->queryParams['phpsessid'];

        $this->session = $this->getSessionData($sessionId);

        $clientToken = $this->queryParams['token'];

        $chatId = $this->queryParams['chatId'];

        if ($this->validateSocketConnection($this->session['token'], $clientToken, $conn)) {

            $this->clients->attach($conn, ['userId' => $this->session['id'], 'chatId' => $chatId]);
            echo "New client connected, token verified: {$conn->resourceId}\n";
        }
        else {
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        $data = json_decode($msg);

        switch($data->type) {
            case 'dialog-message':
                $this->handleDialogMessage($from, $data);
                break;
            
            case 'dialog-history':
                $this->handleDialogHistory($from, $data);
                break;

            case 'delete-message':
                $this->deleteDialogMessage($data);
                break;

            case 'edit-message':
                $this->editDialogMessage($data);
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
// Сохраняем в базе данных сообщение и отправляем собеседнику
    protected function handleDialogMessage(ConnectionInterface $from, stdClass $data) {
        // Тут $from в принципе не нуженЮ поскольку при отправке мы указываем автора, чтобы сам отправитель мог отличить свое сообщение от чужого в addMessageToDOM()
        // Сохраним сообщение в бд, даже если собеседник не онлайн

        $senderInfo = $this->clients->offsetGet($from);
        $senderId = $senderInfo['userId'];
        $data->senderId = $senderId;

        $messageId = $this->messages->setMessage($data);
        $data->messageId = $messageId;
        $this->handleMessageId($from, $messageId);

        foreach($this->clients as $client) {

            $clientInfo = $this->clients->offsetGet($client);

            if ($clientInfo['userId'] == $data->takerId && $senderInfo['chatId'] == $clientInfo['chatId']) {
                    print_r($data);
                    echo "Sending a letter to: $data->takerId\n";

                    $msg = json_encode($data);
                    $client->send($msg);
            }
        }
    }
    // Отправляем измененное сообщение в бд и собеседнику
    protected function editDialogMessage($data) {

        $this->messages->editMessage($data->dialogId, $data->messageId, $data->text);

        foreach ($this->clients as $client) {

            $clientInfo = $this->clients->offsetGet($client);

            if ($clientInfo['chatId'] == $data->dialogId && $clientInfo['userId'] == $data->takerId) {

                $msg = json_encode($data);
                $client->send($msg);
            }
        }
        
    }
    // Отправялем запрос на удаление и сообщение клиенту, чтобы он удалил элемент сообщения
    protected function deleteDialogMessage($data) {

        $this->messages->deleteMessage($data);

        foreach($this->clients as $client) {

            $clientInfo = $this->clients->offsetGet($client);

            if ($clientInfo['userId'] == $data->interlocutorId && $clientInfo['chatId'] == $data->dialogId) {

                $msg = json_encode($data);
                $client->send($msg);
            }
        }
    }
    // Вспомогательная функция, для красоты
    protected function handleMessageId(ConnectionInterface $from, $messageId) {
        
        $response = [
            'type' => 'messageId',
            'messageId' => $messageId,
        ];

        $from->send(json_encode($response));
    }
    // Получаем историю сообщений из бд и возвращаем тому, кто запросил
    protected function handleDialogHistory(ConnectionInterface $from, stdClass $data) {

        $messagesHistory = $this->messages->getChatMessages($data->dialogId);

        $data = [
            'type' => 'dialog-history',
            'messages' => $messagesHistory,
        ];
        $msg = json_encode($data);

        $from->send($msg);
    }
    // Получаем ключи из URL соединения сокетов
    protected function getKeysFromRequest(ConnectionInterface $conn) {
        $queryParams = array();
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
        return $queryParams;
    }
    // Получаем данные, сохраненные в сесии. Стоит обдумать отказ от хранеия в redis, поскольку объем данных не большой ----------------------------------------------------------------------------------
    protected function getSessionData($sessionId) {
        $sessionData = $this->sessionHandler->read($sessionId);
        $sessionDataArray = unserialize($sessionData);
        return $sessionDataArray;
    }
    // Сверяем JWT токен, который хранился в сессии с аутентификации и токен, полученный от клиента
    protected function validateSocketConnection($sessionToken, $clientToken, ConnectionInterface $conn) {
       return $sessionToken == $clientToken;
    }
}
