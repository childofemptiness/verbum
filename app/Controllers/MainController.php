<?php
class MainController extends Controller{

    protected $model;
    public function __construct($controller, $method){
        parent:: __construct($controller, $method);
        $this->model = new MainModel();
    }

    public function home(){
    }
}