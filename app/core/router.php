<?php

namespace App\Core;
use App\Controllers;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class Router {
    private $dispatcher;

    private $httpMethod;
    private $uri;
    
    private $controller_name;
    private $method_name;
    public function __construct($httpMethod, $uri) {
        $this->httpMethod = $httpMethod;
        $this->uri = $uri;
        $url_array = explode('/', $this->uri);
        $this->controller_name = $url_array[1];
        $this->method_name = $url_array[2];
        $this->dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $r->addRoute('GET', '/users', 'get_all_users_handler');
            // {id} must be a number (\d+)
            $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
            // The /{title} suffix is optional
            $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');

            if (strpos($this->uri, 'activation') !== false) {
                $r->addRoute('GET', '/auth/activation/{token}', 'AuthController@activation');  // ИЗБАВИТЬСЯ ОТ КОСТЫЛЯ!!!
            } else {
            $r->addRoute($this->httpMethod, $this->uri, ucfirst($this->controller_name) . 'Controller' . '@' . $this->method_name);
            }
        });
    }

    public function dispatch() {
        $routeInfo = $this->dispatcher->dispatch($this->httpMethod, $this->uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
               // echo 404;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
               // echo 405;
                break;
            case Dispatcher::FOUND:
                //echo 777;
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // Вызов соответствующего контроллера и метода
                list($controller, $method) = explode('@', $handler, 2); 
                $controller_class = "App\\Controllers\\" . ucfirst($this->controller_name) . "Controller";

                if (!class_exists($controller_class)){

                    $controller = DEFAULT_CONTROLLER;
                    $method = DEFAULT_METHOD;
                }  else if (!method_exists($controller_class, $method)){
                    $method = NOT_FOUND;
                }
                
                call_user_func_array([new $controller_class($controller, $method), $method], $vars);

                break;
        }
    }
}
