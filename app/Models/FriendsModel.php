<?php

namespace App\Models;

use App\Views\View;

class FriendsModel extends DbModel {
    private $output;
    public function __construct(View $view) {
        parent:: __construct();
        $this->output = $view;
    }

    public  function setFriendRequest($data) {
        echo 666;
        $takerTag = $data['tag'];
        $takerId = $takerTag - 666666;
        $senderId = 22;

        $query = 'SELECT COUNT(*) FROM friends WHERE (taker_id = :takerId1 AND sender_id = :senderId1) OR (taker_id = :senderId2 AND sender_id = :takerId2)';
        $params = [
            'senderId1' => $senderId,
            'takerId1' => $takerId,
            'senderId2' => $senderId,
            'takerId2' => $takerId
        ];

        $matches = $this->getQuery($query, $params)[0]['COUNT(*)'];

        if ($matches === 0) { 

            $query = 'INSERT INTO friends (sender_id, taker_id, is_accepted) VALUES (:senderId, :takerId, FALSE)';
            $params = ['takerId' => $takerId, 'senderId' => $senderId];
            $this->setQuery($query, $params);
        }
    }

    public function acceptFriendRequest($senderId) {

        $takerId = $this->getUserIdFromSession();
        $query = 'UPDATE friends SET is_accepted = 1 WHERE sender_id = :senderId AND taker_id = :takerId';
        $params = ['senderId' => $senderId, 'takerId' => $takerId];

        $this->setQuery($query, $params);
    }

    // Подумать насчет параметра для этих функций
    public function deleteAFriendRelation($senderId) {

        $takerId = $this->getUserIdFromSession();

        $query = 'DELETE FROM friends WHERE sender_id = :senderId AND taker_id = :takerId';
        $params = ['senderId' => $senderId, 'takerId' => $takerId];

        $this->setQuery($query, $params);
    }
    // Запросы на дружбу, отправленные пользователем
    public function getAllSentRequests() {
            $senderId = $this->getUserIdFromSession();

            $query = 'SELECT taker_id FROM friends WHERE sender_id = :senderId AND is_accepted = FALSE';
            $params = ['senderId' => $senderId];

            $results = $this->getQuery($query, $params);

            if ($results) {
                foreach($results as $item) {
                    $senderList[] = $item['taker_id'];
                }
    
                $placeholders = implode(',', array_fill(0, count($senderList), '?'));
                // Используем плейлсхолдеры
                $query = "SELECT user_id, name, surname, username FROM users WHERE user_id IN ($placeholders)";
                $results = $this->getQuery($query, $senderList, 1);
                if ($results) $invites = $this->formalizeDataInTheForm($results);
                else $invites = null;
    
                return $invites;
            }
            else {
                return null;
            }
            
        }

    // Запросы на дружубу, полученные пользователем 
    public function getAllReceivedRequests() {

        $takerId = $this->getUserIdFromSession();

        $query = 'SELECT sender_id FROM friends WHERE taker_id = :takerId AND is_accepted = FALSE';
        $params = ['takerId' => $takerId];

        $results = $this->getQuery($query, $params);

       if ($results) {
        foreach($results as $item) {
            $senderList[] = $item['sender_id'];
        }
      
        $placeholders = implode(',', array_fill(0, count($senderList), '?'));
        // Constructing the query with named placeholders
        $query = "SELECT user_id, name, surname, username FROM users WHERE user_id IN ($placeholders)";
        $results = $this->getQuery($query, $senderList, 1);

        $invites = $this->formalizeDataInTheForm($results);

        return $invites;
       }
       
       else {
        return null;
       }

    }
    // Преобразовать информацию о запросах в дружбу в удобный вид
    protected function formalizeDataInTheForm($data) {
        $invites = [];
        foreach ($data as $item) {
            $invites[] = [
                'userId' => $item['user_id'],
                'fullName' => $item['name'] . ' ' . $item['surname'],
                'userName' => $item['username'],
            ];
        }  
        return $invites;      
    }
    // Отправить список друзей пользователя
    public function getFriendsList() {

        $userId = $this->getUserIdFromSession();

        $query = 'SELECT users.name, users.surname, users.username, users.user_id AS friendId
        FROM users
        WHERE users.user_id IN (
            (SELECT sender_id FROM friends WHERE taker_id = :userId1 AND is_accepted = 1)
            UNION
            (SELECT taker_id FROM friends WHERE sender_id = :userId2 AND is_accepted = 1)
        )';

        $params = [
            'userId1' => $userId,
            'userId2' => $userId
        ];

        $results = $this->getQuery($query, $params);

        $friendsList = [];
        foreach($results as $item) {
            $friendsList[] = [
                'tag' => $item['friendId'] + 666666,
                'fullName' => $item['name'] . ' ' . $item['surname'],
                'userName' => $item['username'],
            ];
        }

        return $friendsList;
    }

    public function deleteFriend($data) {

        $friendId = $data['tag'] - 666666;
        $userId = $this->getUserIdFromSession();

        $query = 'DELETE FROM friends WHERE ((sender_id = :userId1 AND taker_id = :friendId1) OR (sender_id = :friendId2 AND taker_id = :userId2)) AND is_accepted = 1';
        $params = [
            'userId1' => $userId,
            'friendId1' => $friendId,
            'userId2' => $userId,
            'friendId2' => $friendId
        ];
        $this->setQuery($query, $params);
    }

    public function build_page($page_name) {   
        $htm_src = $this->output->get_page($page_name);   
        $html = $this->output->replace_localizations($htm_src);
        $this->output->render($html);
    }
}
