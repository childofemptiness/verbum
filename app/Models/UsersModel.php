<?php

namespace App\Models;
use App\Core\HelperFunctions;

class UsersModel extends DbModel {
    protected $helper;
    public function __construct() {
        parent:: __construct();
        $this->helper = new HelperFunctions();
    }

       // Добавляем пользователя в бд, но без присвоения каких-либо прав
       public  function addUserInDB($requestData)
       {
           $requestData['activation_code'] = $this->helper->generateActivationCode();
           $requestData['rights'] = 0;
           $sql = 'INSERT INTO users (name, surname, username, password, email, activation_code, rights) VALUES (:name, :surname, :username, :password, :email, :activation_code, :rights)';
           $this->set_query($sql, $requestData);
           return $requestData;
       }
   

       public function getUserIdByUserName($userName) {
        $query = 'SELECT user_id FROM users WHERE :username = username';
        $params = ['userName' => $userName];
        $userId = $this->get_query($query, $params)[0]['user_id'];
        return $userId;
       }

       public function getUserIdBySession() {
        return $_SESSION['id'];
       }

       public function setUserIsActive($userId) {
           $query = 'UPDATE users SET is_active = true WHERE user_id = :userId';
           $params = ['userId' => $userId];
           $this->set_query($query, $params);
       }
       
        // Проверяем, существует ли пользователь в бд
        public function getUserInfo($userName)
        {
            $query = 'SELECT * FROM users WHERE username = :username';
            $params = ['userName' => $userName];
            $result = $this->get_query($query, $params)[0]['*'];
            return $result;
        }
   
       // Проверка, подтвердил ли пользователь почту
       public function isUserVerified($requestData)
       {
           $username = $requestData['username'];
           $query = 'SELECT rights FROM users WHERE username = :username';
           $rights = $this->get_query($query, ['username'=> $username]);
           return $rights;
       }
      
       // Подтвердить пользователя в БД
       public function verifyUser($activation_code)
       {
           $query = 'UPDATE users SET rights = 1 WHERE activation_code = :activation_code';
           $this->set_query($query, ['activation_code'=> $activation_code]);
           return 1;
       }

    // Проверка, в сети ли пользователь
    public function isUserActive($userId) {
        $sql = 'SELECT is_active FROM users where user_id = :$userId';
        $params = ['userId' => $userId];
        $result = $this->get_query($sql, $params)[0]['is_active'];
        return $result;
    }

}