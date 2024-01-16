<?php

# Implement sanitize methods first
namespace App\Controllers;
use App\Models;
use App\Views\View;
use App\Core\HelperFunctions;

class Controller extends Application {
  
  protected $controller;
  protected $method;
  protected $model;
  protected $view;
  protected $model_name;
  protected $helper;
  public function __construct($controller, $method) {
    parent::__construct();
    $this->helper = new HelperFunctions();
    $this->controller = $controller;
    $this->method = $method;   
    $this->model_name = 'App\\Models\\' . $this->controller."Model"; 
    $this->view = new View($controller); 
  }
  
  # Load and instantiate model specific for this controller
  protected function load_model() {
    if (class_exists($this->model_name)) {
      $this->model[$this->model_name] = new $this->model_name($this->view);
    }
    else {
      return false;
    }
  }
  
  # Implement instantiated model methods
  protected function get_model() {
    if (isset($this->model[$this->model_name]) && is_object($this->model[$this->model_name])) {
      return $this->model[$this->model_name];
    }     
    
    return false;      
  }

  
  // # Return view instance
  // protected function get_view() {
  //   return $this->view;
  // }
}

?>