<?php
if (session_id() == '') session_start();
class MainModel extends DbModel{

    protected $model;
    protected $view;


    public function __construct(){
        $this->view = new View();
    }

}