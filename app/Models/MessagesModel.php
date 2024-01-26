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

        $query = 'INSERT INTO messages (chat_id, sender_id, message_text, parent_message_id) VALUES (:dialogId, :senderId, :text, :parentMessageId)';
        $params = [
            'dialogId' => $data->dialogId,
            'senderId' => $data->senderId,
            'text' => $data->text,
            'parentMessageId' => isset($data->parentMessageId) ? $data->parentMessageId : null,
        ];
        $this->set_query($query, $params);
    
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

        $query = 'SELECT message_id AS messageId, sender_id AS senderId, send_date AS sendDate, message_text AS text, parent_message_id AS parentMessageId 
        FROM messages WHERE chat_id = :chatId';
        $params = [
            'chatId' => $chatId
        ];
        $messages = $this->get_query($query, $params);

        return $messages;
    }

    // Удаляем сообщение. Стоит подумать над удалением только для самого пользователя и удалением вообще --------------------------------------------------------------------------
    public function deleteMessage($data) {

        $query = 'DELETE FROM messages WHERE chat_id = :dialogId AND message_id = :messageId';
        $params = [
            'dialogId' => $data->dialogId,
            'messageId' => $data->messageId,
        ];
        unset($_SESSION['error']);
        $this->set_query($query, $params);
        print_r($_SESSION['error']);
    }

    // Изменение сообщения
    public function editMessage($dialogId, $messageId, $newText) {
        
        $query = 'UPDATE messages SET message_text = :newText WHERE chat_id = :dialogId AND message_id = :messageId';
        $params = [
            'dialogId' => $dialogId,
            'messageId' => $messageId,
            'newText' => $newText,
        ];
        unset($_SESSION['error']);
        $this->set_query($query, $params);
        print_r($_SESSION['error']);
    }
}
