<?php

# Implement sanitize methods first
class Controller extends Application {
  
  protected $controller;
  protected $method;
  protected $model;
  protected $view;

  protected $model_name;
  
  public function __construct($controller, $method) {
    parent::__construct();
    
    $this->method = $method;
    $this->controller = $controller;  
    $this->model_name = $this->controller."_Model";  
    $this->view = new View();
  }
  
  # Load and instantiate model specific for this controller
  protected function load_model() {
    if (class_exists($this->model_name)) {
      $this->model[$this->model_name] = new $this->model_name();
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
    else {      
      return false;      
    }
  }

  
  # Return view instance
  protected function get_view() {
    return $this->view;
  }

  
  
}

?>