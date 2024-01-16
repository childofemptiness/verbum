<?php

namespace App\Controllers;
use App\Core\HelperFunctions;
use Ratchet\Server\IoServer;

class MainController extends Controller{

    protected $helper;
    public function __construct($controller, $method){
        parent:: __construct($controller, $method);
        $this->load_model();
        $this->helper = new HelperFunctions();
    }

    public function home(){
        $this->view->page_title = "Home";
        $this->get_model()->build_page($this->method);
    }

    public function getusername() {
        $userFullName = $this->get_model()->getUserName();
        $this->helper->send_json($userFullName);
    }
}