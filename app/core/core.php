<?php
# Autoload
require_once('./vendor/autoload.php');


# Load config
require_once(ROOT . DS . "config" . DS . "config.php");


use App\Core\Router;
// Создаем объект класса Router
$router = new Router($httpMethod, $uri);

// Вызываем метод dispatch для обработки запроса
$router->dispatch();
