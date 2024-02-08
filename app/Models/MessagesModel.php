<?php

namespace App\Models;

use Exception;

class MessagesModel extends DbModel {
    public function __construct() {
        parent:: __construct();
    }

    // Сохраняем сообщение в бд и возвращаем id сообщения
   public function setMessage($data) {
    try {
        $this->beginTransaction();

        $query = 'INSERT INTO messages (chat_id, sender_id, message_text, parent_message_id) VALUES (:chatId, :senderId, :text, :parentMessageId)';
        $params = [
            'chatId' => $data->chatId,
            'senderId' => $data->senderId,
            'text' => $data->text,
            'parentMessageId' => isset($data->parentMessageId) ? $data->parentMessageId : null,
        ];

        $this->setQuery($query, $params);

        $lastMessageId = $this->lastInsertId();
    
        $this->commitTransaction();
    
        return $lastMessageId;
    }
    catch(Exception $e) {
        $this->rollBack();

        $e->getMessage();
    }
   }

    // Потом добавить функционал отметки прочитанных/непрочитанных сообщений(is_read) ------------------------------------------------------------------------------------------------
    public function getChatMessages($chatId) {

        $query = 'SELECT m.message_id AS messageId, m.sender_id AS senderId, m.send_date AS sendDate,
        m.message_text AS text, m.parent_message_id AS parentMessageId, u.username AS userName
        FROM messages m JOIN users u ON m.sender_id = u.user_id WHERE chat_id = :chatId';
        $params = [
            'chatId' => $chatId
        ];
        $messages = $this->getQuery($query, $params);
        echo "\n";
        return $messages;
    }

    // Удаляем сообщение. Стоит подумать над удалением только для самого пользователя и удалением вообще --------------------------------------------------------------------------
    public function deleteMessage($data) {

        $query = 'DELETE FROM messages WHERE chat_id = :chatId AND message_id = :messageId';
        $params = [
            'chatId' => $data->chatId,
            'messageId' => $data->messageId,
        ];

        $this->setQuery($query, $params);
    }

    // Изменение сообщения
    public function editMessage($dialogId, $messageId, $newText) {
        
        $query = 'UPDATE messages SET message_text = :newText WHERE chat_id = :dialogId AND message_id = :messageId';
        $params = [
            'dialogId' => $dialogId,
            'messageId' => $messageId,
            'newText' => $newText,
        ];
        $this->setQuery($query, $params);
    }

    public function getSenderUserName($messageId) {

        $query = 'SELECT u.username FROM messages m JOIN users u ON u.user_id = m.sender_id WHERE m.message_id = :messageId';
        $params = [
            'messageId' => $messageId,
        ];
        $results = $this->getQuery($query, $params);

        return $results[0]['username'];
    }
}
