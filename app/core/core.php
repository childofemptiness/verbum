<?php

# Autoloader
spl_autoload_register(function ($class_name) {
    if (file_exists(ROOT . DS . "core" . DS . strtolower($class_name) . ".php")) {
      require_once (ROOT . DS . "core" . DS . strtolower($class_name) . ".php");
    }
});
// Создаем объект класса Router
$router = new Router();

// Получаем HTTP метод и URI из запроса (например, из $_SERVER['REQUEST_METHOD'] и $_SERVER['REQUEST_URI'])

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Вызываем метод dispatch для обработки запроса
$router->dispatch($httpMethod, $uri);
