<?php

namespace App\Controllers;
use App\Core\HelperFunctions;

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
}