<?php

namespace App\Models;
use App\Core\HelperFunctions;

class UsersModel extends DbModel {
    protected $helper;
    public function __construct($dbName = null) {
        parent:: __construct($dbName);
        $this->helper = new HelperFunctions();
    }

       // Добавляем пользователя в бд, но без присвоения каких-либо прав
       public  function addUserInDB($requestData)
       {
           $requestData['activation_code'] = $this->helper->generateActivationCode();
           $requestData['rights'] = 0;
           $sql = 'INSERT INTO users (name, surname, username, password, email, activation_code, rights) VALUES (:name, :surname, :username, :password, :email, :activation_code, :rights)';
           $this->setQuery($sql, $requestData);
           return $requestData;
       }
       
       public function setUserIsActive($userId) {
           $query = 'UPDATE users SET is_active = true WHERE user_id = :userId';
           $params = ['userId' => $userId];
           $this->setQuery($query, $params);
       }
       // Получить id пользователя по никнейнму
       public function getUserId($userName) {
        
        $query = 'SELECT user_id FROM users WHERE username = :userName';
        $params = ['userName' => $userName];
        $results = $this->getQuery($query, $params);
        
        if (empty($results)) {
            // Обработка случая, когда пользователь не найден, например вернуть null или выбросить исключение.
            return null; // Или throw new \Exception("User not found");
        }
        
        $userId = $results[0]['user_id'];
        return $userId;
    }
    

        // Универсальная функция, возвращает любое поле по любому ключу(Имеет смысл использовать, если нужно одно поле)
        public function getUserFieldById($fieldName, $userId)
        {
            $query = "SELECT $fieldName FROM users WHERE user_id = :userId";
            $params = ['userId' => $userId];
            if ($fieldName == '*') {
                $result = $this->getQuery($query, $params)[0];
            }
            else $result = $this->getQuery($query, $params)[0][$fieldName];
            return $result;
        }
        // Если выше стоящая функция по ключу * выдает всю инфу, эту функцию можно удалить
        public function getUserById($userId) {
            $query = 'SELECT * FROM users WHERE user_id = :userId';
            $params = ['userId' => $userId];
            $result = $this->getQuery($query, $params)[0]['*'];
            return $result;
        }
      
       // Подтвердить пользователя в БД
       public function verifyUser($activationCode)
       {
           $query = 'UPDATE users SET rights = 1 WHERE activation_code = :activationCode';
           $params = ['activationCode' => $activationCode];
           $this->setQuery($query, $params);
       }

    // Проверка, в сети ли пользователь
    public function isUserActive($userId) {
        $sql = 'SELECT is_active FROM users where user_id = :$userId';
        $params = ['userId' => $userId];
        $result = $this->getQuery($sql, $params)[0]['is_active'];
        return $result;
    }
}
