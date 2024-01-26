<?php

namespace App\Core;
use App\Controllers;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use App\Core\HelperFunctions;

class Router {
    private $helper;
    private $dispatcher;

    private $httpMethod;
    private $uri;
    private $controller_name;
    private $method_name;
    private $urlArray;
    public function __construct($httpMethod, $uri) {
        $this->httpMethod = $httpMethod;
        $this->uri = $uri;
        $this->urlArray = explode('/', $this->uri);
        $this->controller_name = $this->urlArray[1];
        $this->method_name = empty($this->urlArray[2]) ? '' : $this->urlArray[2];
        $this->helper = new HelperFunctions();
        $this->dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            switch($this->urlArray[2]) {
                case 'sendinterlocutorinfo':
                    $r->addRoute($this->httpMethod, '/chats/sendinterlocutorinfo/{id:\d+}', 'Chats@sendinterlocutorinfo');
                    break;
                case 'activation':
                    $r->addRoute($this->httpMethod, '/auth/activation/{token:[^/]+}', 'Auth@activation');
                    break;
                default:
                    $r->addRoute($this->httpMethod, $this->uri, ucfirst($this->controller_name)  . '@' . $this->method_name);
                    break;
            }
           
        });
    }

    public function dispatch() {
        $routeInfo = $this->dispatcher->dispatch($this->httpMethod, $this->uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                echo 99;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                echo 88;
                break;
            case Dispatcher::FOUND:

                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // Вызов соответствующего контроллера и метода
                list($controller, $method) = explode('@', $handler, 2); 

                $activationFlag = $method == 'activation' ? true : false;

                $controller_class = "App\\Controllers\\" . $controller . "Controller";

                if (!class_exists($controller_class) || empty($this->controller_name)){
                    
                    $controller = DEFAULT_CONTROLLER;
                    $method = DEFAULT_METHOD;
                }   else if (!method_exists($controller_class, $method)){
                    $method = NOT_FOUND;

                }
                if (empty($_SESSION['id']) && $controller != 'Auth' && !$activationFlag) {

                    $this->helper->redirect('auth/loginpage');
                }
                // Переопределяем класс контроллера, если вдруг контроллер изменился
                $controller_class = "App\\Controllers\\" . $controller . "Controller";

                call_user_func_array([new $controller_class($controller, $method), $method], array_values($vars));

                break;
        }
    }
}
