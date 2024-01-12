<?php
# Autoload
require_once '../verbum/vendor/autoload.php';


# Load config
require_once(ROOT . DS . "config" . DS . "config.php");
// require_once(ROOT . DS. "core" . DS . "helper_functions.php");


use App\Core\Router;

// Создаем объект класса Router
$router = new Router($httpMethod, $uri);

// Вызываем метод dispatch для обработки запроса
$router->dispatch();
