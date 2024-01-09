<?php
// Функция для перенаправления на другую страницу
function redirect($location){
    header("Location: http://mvc-php-master/$location");
    exit();
}
// Функция для отправки json объекта
function send_json($data)
{
    header("Content-Type: application/json; charset=utf-8"); 
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

// Функция отправки письма на почту
function sendMail($requestData)
{
    $email = $requestData['email'];
    $activation_code = $requestData['activation_code'];
    $link = "http://localhost/mvc-php/index/activation?code=$activation_code";
    $to      = $email;
    $subject = 'Активация аккаунта';
    $message = "Спасибо за регистрацию! Для активации аккаунта нажми <a href='$link'>сюда</a>";
    $headers = 'From: huneyhunter@gmail.com' . "\r\n" .
    'Reply-To: hunryhunter@gmail.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion() . "\r\n" .
    'Content-type: text/html; charset=UTF-8';

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

?>