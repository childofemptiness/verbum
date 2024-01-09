<?php

require_once '../verbum/vendor/autoload.php';
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class Router {
    private $dispatcher;

    public function __construct() {
        $this->dispatcher = \FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/users', 'get_all_users_handler');
            // {id} must be a number (\d+)
            $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
            // The /{title} suffix is optional
            $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
        });
    }

    public function dispatch($httpMethod, $uri) {
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                // Обработка ошибки 404
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                // Обработка ошибки 405
                break;
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // Вызов соответствующего контроллера и метода
                list($controller, $method) = explode('@', $handler, 2);

                $controller = ucfirst($controller) . 'Controller';

                if (!class_exists($controller)){
                    $controller = DEFAULT_CONTROLLER;
                    $method = DEFAULT_METHOD;
                }  else if (!method_exists($controller, $method)){
                    $method = NOT_FOUND;
                }

                call_user_func_array([new $controller, $method], $vars);

                break;
        }
    }
}
