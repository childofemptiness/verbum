<?php

namespace App\Models;

use App\Views\View;
use Exception;

class ChatsModel extends DbModel{
    private $output;
    private $userId;
    public function __construct(View $view = null){
        parent:: __construct();
        $this->output = $view;
        $this->userId = $this->getUserIdFromSession();
    }
// Если между пользвователями еще не было диалога, начинаем его
    public function startNewDialog($interlocutorId) {

        try {
            $this->beginTransaction();

            $query = 'INSERT INTO chats (chat_type) VALUES ("dialog")';
            $this->setQuery($query);
            $lastChatId = $this->lastInsertId();
            $query = 'INSERT INTO user_chats (user_id, chat_id) VALUES (:userId, :chatId1), (:interlocutorId, :chatId2)';
            $params = [
                'userId' => $this->userId,
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

        $userId = $userId ? $userId : $this->userId;

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

        $query = 'SELECT user_id FROM user_chats WHERE chat_id = :dialogId AND user_id != :userId';

        $params = [
            'userId' => $this->userId,
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
    // Создаем группу
    public function createGroup($data) {
        try {
            $this->beginTransaction();
    
            $query = 'INSERT INTO chats (chat_name, chat_type) VALUES (:groupName, "group")';
            $params = [
                'groupName' => $data['groupName'],
            ];
            $this->setQuery($query, $params);
    
            $lastGroupId = $this->lastInsertId();
            $userId = $this->getUserIdFromSession();

            // Подготовка запроса для вставки данных пользователей
            $query = "INSERT INTO user_chats (user_id, chat_id, status) VALUES 
            ($userId, $lastGroupId, 'admin')";
    
            // Добавление значений для каждого участника группы
            $valuesToInsert = '';
            foreach ($data['groupMembers'] as $index => $groupMemberTag) {

                $groupMemberId = $groupMemberTag - 666666;
                $valuesToInsert .= ",\n ($groupMemberId, $lastGroupId, 'member')";
            }
    
            // Конкатенация всего финального запроса
            $query .= $valuesToInsert;

            // Выполнение запроса на добавление участников группы
            $this->setQuery($query);

            // Сохранение результатов транзакции
            $this->commitTransaction();
        } catch (Exception $e) {
            // Откат в случае ошибки
            $this->rollBack();
            error_log($e->getMessage()); // Логирование исключения
        }
    }
    
    public function getChatsList() {

        $dialogsList = $this->getDialogsList();
        $chatsList = [];

        foreach($dialogsList as $index => $dialogInfo) {
            $chatsList[] = [
                'chatName' => $dialogInfo['userName'],
                'chatId' => $dialogInfo['dialogId'],
                'chatType' => $dialogInfo['chatType'],
            ];
        }

        $groupsList = $this->getGroupsList();
        foreach($groupsList as $index => $groupInfo) {
            $chatsList[] = [
                'chatName' => $groupInfo['groupName'],
                'chatId' => $groupInfo['groupId'],
                'chatType' => $groupInfo['chatType'],
            ];
        }

        return $chatsList;
    }

    protected function getDialogsList() {

        $query = "SELECT DISTINCT u2.username AS userName, c.chat_id AS dialogId, c.chat_type AS chatType
        FROM chats c
        JOIN user_chats uc1 ON c.chat_id = uc1.chat_id
        JOIN user_chats uc2 ON c.chat_id = uc2.chat_id
        JOIN users u1 ON uc1.user_id = u1.user_id
        JOIN users u2 ON uc2.user_id = u2.user_id
        WHERE c.chat_type = 'dialog' AND u1.user_id = :userId1 AND u2.user_id != :userId2;
        ";
        $params = [
            'userId1' => $this->userId,
            'userId2' => $this->userId,
        ];
        $results = $this->getQuery($query, $params);

        return $results;
    }

    protected function getGroupsList() {

        $query = "SELECT c.chat_name AS groupName, c.chat_id AS groupId, c.chat_type AS chatType 
        FROM chats c
        JOIN user_chats ON c.chat_id = user_chats.chat_id 
        WHERE c.chat_type = 'group' AND user_chats.user_id = :userId";
        $params = [
            'userId' => $this->userId,
        ];
        $results = $this->getQuery($query, $params);

        return $results;
    }

    public function getgroupname($data) {
        
        $groupId = $data['groupId'];

        $query = 'SELECT chat_name FROM chats WHERE chat_id = :groupId';
        $params = [
            'groupId' => $groupId,
        ];
        $results = $this->getQuery($query, $params);

        return $results[0]['chat_name'];
    }
}
