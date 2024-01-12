<?php
namespace App\Controllers;
use App\Core\HelperFunctions;

class AuthController extends Controller{
    protected $helper;

    public function __construct($controller, $method){
        parent::__construct($controller, $method);
        $this->load_model();
        $this->helper = new HelperFunctions();
    }

    public function loginpage(){
        $this->view->page_title = "Login";
        $this->get_model()->build_page("login");
    }

    public function registerpage(){
        $this->view->page_title = "Register";
        $this->get_model()->build_page("register");
    }

    public function login(){
        $requestData = $this->get_model()->catchJson();
        // Проверяем данные на валидность
        $response = $this->get_model()->validateLogData($requestData);
        // Если никаких ошибок нет, проверяем, авторизован ли пользователь
        if(empty($response)) {
            $response = $this->get_model()->loginUser($requestData);
            if ($response['status'] == 200) {
                $this->get_model()->createUserSession($requestData);    
              //  $this->helper->redirect('main/home');
            } 
        }
        $this->helper->send_json($response);
    }

     // Регистрация
    public function register() {
        // Получаем данные с формы регистрации
        $requestData = $this->get_model()->catchJson();
        // Проверяем данные на валидность
        $response = $this->get_model()->validateRegData($requestData);
        if(empty($response)) {
            if ($this->get_model()->isUserExists($requestData)) {
                $response['status'] = 202;
                $response['message'] = 'Такой пользователь уже существует';
            }
            else {
                unset($_SESSION['error']);
                $requestData = $this->get_model()->addUserInDB($requestData);
                $this->helper->sendMail($requestData);
                $response['status'] = 200;
                $response['message'] = 'Письмо с подтверждением отправлено на почту';
            }
        }
    $this->helper::send_json($response);
    }

    public function activation($args){
        $result = $this->get_model()->activation($args);
        if ($result) $this->helper->redirect('main/home');
    }

    public function logout(){
    $this->loginpage();
    $this->get_model()->logout();
    }
    
}