<?php

namespace App\Core;
use Firebase\JWT\JWT;

// Функция для перенаправления на другую страницу
class HelperFunctions{
    public function redirect($location) {
      header("Location: http://verbum/$location");
      exit(0);
    }
    // Функция для отправки json объекта
    public static function sendJson($data)
    {
        header("Content-Type: application/json; charset=utf-8"); 
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function catchJson() {
        return json_decode(file_get_contents('php://input'), true);
    }
    // Функция отправки письма на почту
    public function sendMail($requestData)
    {
      $email = $requestData['email'];
      $activation_code = $requestData['activation_code'];
      $link = "http://verbum/auth/activation/$activation_code";
      $to      = $email;
      $subject = 'Активация аккаунта';
      $message = "Спасибо за регистрацию! Для активации аккаунта нажмите <a href='{$link}'>сюда</a>";
      $headers = 'From: pochtalavrika@gmail.com' . "\r\n" .
      'Reply-To: pochtalavrik@gmail.com' . "\r\n" .
      'X-Mailer: PHP/' . phpversion() . "\r\n" .
      'MIME-Version: 1.0' . "\r\n" . // Добавлено для указания версии MIME
      'Content-type: text/html; charset=UTF-8'; // Указание типа контента и кодировки

      mail($to, $subject, $message, $headers);

    }

    public function snake_case($str) {
      return str_replace("-", "_", $str);
    }

    public function generateActivationCode()
    {
        return bin2hex(random_bytes(16));
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
}
