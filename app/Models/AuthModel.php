<?php

namespace App\Models;

use App\Views\View;

class AuthModel extends DbModel {
    private $output;
    private $usersModel;
    public function __construct(View $view){
        parent:: __construct();
        $this->output = $view;
        $this->usersModel = new UsersModel();
    }

      // Эту функцию можно поместить в класс AuthModel

    public function loginUser($requestData) {

        $enteredUserName = $requestData['username'];
        $enteredPassword = $requestData['password'];
        $userId = $this->usersModel->getUserId($enteredUserName);
        $response = $this->authenticate($userId, $enteredPassword);

        return $response;
    }

    // Аутентификация пользователя - отдельная функция с проверками пароля и прав
    private function authenticate($userId, $password) {
        $response = [];

        if (!$userId) {
            return $this->createResponse(201, 'Такой пользователь не найден');
        }

        if (!$this->verifyPassword($password, $this->usersModel->getUserFieldById('password', $userId))) {
            return $this->createResponse(203, 'Неправильный пароль');
        } 

        if (!($this->usersModel->getUserFieldById('rights', $userId) > 0)) {
            return $this->createResponse(202, 'Подтвердите свою почту!');
        }
        
        return $this->createResponse(200, 'Успешная аутентификация!');
    }

    // Проверка пароля
    private function verifyPassword($inputPassword, $storedPasswordHash) {
        // echo $inputPassword . "\n";
        // echo $storedPasswordHash . "\n";
        // echo password_verify($inputPassword, $storedPasswordHash);
        return password_verify($inputPassword, $storedPasswordHash);
    }

    // Проверка, подтвердил ли пользователь свой email
    private function isUserVerified($user) {
        return $user['rights'] > 0;
    }

    // Обобщенная функция для создания ответа
    private function createResponse($status, $message) {
        return [
            'status' => $status,
            'message' => $message
        ];
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

    // Создание сессии
    public function createUserSession($userName)
    {
        $userId = $this->usersModel->getUserId($userName);
        $_SESSION['id'] = $userId;
    }

    public function activation($token) {
        $activation_code = $token;
        $this->usersModel->verifyUser($activation_code);
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
