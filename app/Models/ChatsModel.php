<?php

namespace App\Models;

use App\Views\View;
use Exception;

class ChatsModel extends DbModel{
    private $output;
    public function __construct(View $view = null){
        parent:: __construct();
        $this->output = $view;
    }
// Если между пользвователями еще не было диалога, начинаем его
    public function startNewDialog($interlocutorId) {

        $userId = $this->getUserIdFromSession();

        try {
            $this->beginTransaction();

            $query = 'INSERT INTO chats (chat_type) VALUES ("dialog")';
            $this->setQuery($query);
            $lastChatId = $this->lastInsertId();
            $query = 'INSERT INTO user_chats (user_id, chat_id) VALUES (:userId, :chatId1), (:interlocutorId, :chatId2)';
            $params = [
                'userId' => $userId,
                'chatId1' => $lastChatId,
                'interlocutorId' => $interlocutorId,
                'chatId2' => $lastChatId
            ];

            $this->setQuery($query, $params);

            $this->commitTransaction();
  
            return $lastChatId[0]['chat_id'];
        }

        catch (Exception $e) {
            $this->rollBack();

            $e->getMessage();
        }
    }
// Если диалог существует, возвращаем его id, иначе - null
    public function getDialogId($interlocutorId, $userId = false) {

        $userId = $userId ? $userId : $this->getUserIdFromSession();

        $query = 'SELECT uc1.chat_id
                    FROM user_chats AS uc1
                    JOIN user_chats AS uc2
                    ON uc1.chat_id = uc2.chat_id
                    JOIN chats AS c
                    ON uc2.chat_id = c.chat_id
                    WHERE uc1.user_id = :userId
                    AND uc2.user_id = :interlocutorId
                    AND c.chat_type = "dialog"';

        $params = [
            'userId' => $userId,
            'interlocutorId' => $interlocutorId,
        ];
            
        $results = $this->getQuery($query, $params);

        if (!$results) {

            return $results;
        }

        return $results[0]['chat_id'];
    }

    public function getInterlocutorInfo($interlocutorId) {

        $query = 'SELECT name, surname, username FROM users WHERE user_id = :interlocutorId';
        $params = ['interlocutorId' => $interlocutorId];
        $results = $this->getQuery($query, $params);

        $interlocutorInfo['id'] = $interlocutorId;
        $interlocutorInfo['fullName'] = $results[0]['name'] . ' ' . $results[0]['surname'];
        $interlocutorInfo['nick'] = $results[0]['username'];

       return $interlocutorInfo;
    }

    public function getInterlocutorId($dialogId) {

        $userId = $this->getUserIdFromSession();

        $query = 'SELECT user_id FROM user_chats WHERE chat_id = :dialogId AND user_id != :userId';

        $params = [
            'userId' => $userId,
            'dialogId' => $dialogId,
        ];

        $interlocutorId = $this->getQuery($query, $params)[0]['user_id'];
;
        return $interlocutorId;
    }
// Сделать уже этот метод в DbModel---------------------------------------------------------------------------------------------
    public function build_page($page_name) {    
        $htm_src = $this->output->get_page($page_name);   
        $html = $this->output->replace_localizations($htm_src);
        $this->output->render($html);
      }
}
