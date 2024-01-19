<?php

namespace App\Models;

use App\Views\View;
use Firebase\JWT\JWT;

class AuthModel extends DbModel{
    private $output;
    public function __construct(View $view){
        parent:: __construct();
        $this->output = $view;
    }

      // Функция для авторизации пользователя РАЗДЕЛИТЬ НА БОЛЕЕ МЕЛКИЕ ФУНКЦИИ!!!
      public function loginUser($requestData) {
        $password = $requestData['password'];
        // Проверка, есть ли пользователь с такой почтой
        $user = $this->isUserExists($requestData);
        // Здесь будут ошибки при авторизации для отправки на front
        $response = array();

        if (!$user) {
            $response['status'] = 201; // Ситуация 1: Пользователь с такой почтой не найден
            $response['message'] = 'Такой пользователь не найден';
        } else {
            // Сравнение паролей
               if($password == $user[0]['password']) {
                if ($this->isUserVerified($requestData)[0]['rights'] > 0)
                { // Ситуация 2: Пользователь авторизован успешно
                    $response['status'] = 200;
                    $response['message'] = 'Успешная аутентификация!';
                } 
                else 
                { // Пользователь не подтвердил свою почту
                    $response['status'] = 202;
                    $response['message'] = 'Подтвердите свою почту!';
                }
            } 
            else {
                { // Ситуация 3: Неправильный пароль
                    $response['status'] = 203;
                    $response['message'] = 'Неправильный пароль';
                }
            }
        }
        return $response;
    }

    
    // Проверка данных из формы авторизации на правильность их формата
    public function validateLogData($requestData)
    {
        $response = [];
        if(empty(trim($requestData['username'])) || empty(trim($requestData['password']))) 
        {
            $response['status'] = 204;
            $response['message'] = 'Все поля должны быть заполнены';
        }
        return $response;
    }
    // Проверка данных из формы регистрации
    public function validateRegData($requestData) {
        
        $errors = [];
    
        // Проверка наличия обязательных полей
        $requiredFields = ['name', 'surname', 'username', 'password', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($requestData[$field])) {
                $errors[$field] = $field.' Это поле обязательно для заполнения.';
            }
        }
        // Проверка имени и фамилии на наличие ненужных символов
        $namePattern = '/^[a-zA-Zа-яА-ЯёЁ\s]+$/u';
        if (!empty($requestData['name']) && !preg_match($namePattern, $requestData['name'])) {
            $errors['name'] = 'Имя содержит недопустимые символы.';
        }
    
        if (!empty($requestData['surname']) && !preg_match($namePattern, $requestData['surname'])) {
            $errors['surname'] = 'Фамилия содержит недопустимые символы.';
        }

        if (!empty($requestData['username']) && !preg_match($namePattern, $requestData['username'])) {
            $errors['username'] = 'Логин содержит недопустимые символы.';
        }
    
        // Проверка корректности электронной почты
        if (!empty($requestData['email']) && !filter_var($requestData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный формат электронной почты.';
        }
    
        // Проверка требования по минимальной длине пароля
        if (!empty($requestData['password']) && strlen($requestData['password']) < 6) {
            $errors['password'] = 'Пароль должен быть не менее 6 символов.';
        }
        
        if(!empty($errors))
        {
            $errors['status'] = 201;
            $errors['message'] = 'Ошибка при валидации данных';
        }
        return $errors;
    }
    public function generateActivationCode()
    {
        return bin2hex(random_bytes(16));
    }

    public function activation($token) {
 
        $activation_code = $token;
        $result = $this->verifyUser($activation_code);
        return $result;
    }
    
    public function generateJWTToken($userId) {
        $key = 'IAmBatman';

        $payload = [
            "iss" => "http://verbum/", // Издатель токена
            "aud" => "http://verbum/", // Аудитория токена
            "iat" => time(), // Время, когда токен был выпущен
            "nbf" => time(), // Время, до которого токен не может быть принят
            "exp" => time() + 7200, // Срок действия токена (например, 2 часа)
            "sub" => $userId, // Идентификатор пользователя
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');

        $_SESSION['token'] = $jwt;
    }

    // Добавляем пользователя в бд, но без присвоения каких-либо прав
    public  function addUserInDB($requestData)
    {
        $requestData['activation_code'] = $this->generateActivationCode();
        $requestData['rights'] = 0;
        $sql = 'INSERT INTO users (name, surname, username, password, email, activation_code, rights) VALUES (:name, :surname, :username, :password, :email, :activation_code, :rights)';
        $this->set_query($sql, $requestData);
        return $requestData;
    }

    // Запуск сесси пользователя
    public function createUserSession($requestData)
    {
        $username = $requestData['username'];
        $query = 'SELECT user_id FROM users WHERE :username = username';
        $result = $this->get_query($query, ['username'=> $username]);
        $_SESSION['id'] = $result[0]['user_id'];
        $this->generateJWTToken($_SESSION['id']);
    }
    
     // Проверяем, существует ли пользователь в бд
     public function isUserExists($requestData)
     {
         $username = $requestData['username'];
         $query = 'SELECT * FROM users WHERE username = :username';
         $result = $this->get_query($query, ['username'=> $username]);
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

    public function logOut() {
        unset( $_SESSION['id'] );
        session_destroy();
    }


    public function build_page($page_name) {    
        $htm_src = $this->output->get_page($page_name);   
        $html = $this->output->replace_localizations($htm_src);
        $this->output->render($html);
      }
}