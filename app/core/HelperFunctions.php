<?php

namespace App\Core;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Функция для перенаправления на другую страницу
class HelperFunctions{
    function redirect($location){
      header("Location: http://verbum/$location");
      exit();
  }
  // Функция для отправки json объекта
  static function send_json($data)
  {
      header("Content-Type: application/json; charset=utf-8"); 
      echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  // Функция отправки письма на почту
  function sendMail($requestData)
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
  function is_controller($str) {
    if (file_exists(ROOT . DS . "controllers" . DS . strtolower($str) . ".php")){
      return true;
    }
    else {
      // echo "   ";
      // echo strtolower($str);
      // echo "   ";
      return false;
      
    }
  }

  function snake_case($str) {
    return str_replace("-", "_", $str);
  }
}

?>