<?php

# Define current directory
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(__FILE__) . DS . "app");

// Получаем HTTP метод и URI из запроса (например, из $_SERVER['REQUEST_METHOD'] и $_SERVER['REQUEST_URI'])
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL);

if (session_id() == '')session_start();

require_once(ROOT . DS . "core" . DS . "core.php");
