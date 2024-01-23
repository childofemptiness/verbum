<?php

namespace App\Models;
use App\Views\View;

class MainModel extends DbModel{

    protected $output;
    private $usersModel;

    public function __construct(View $view){
        parent:: __construct();
        $this->output = $view;
        $this->usersModel = new UsersModel();
    }
    
    public function getUserName() {
        $userId = $_SESSION['id'];
        $userFistName = $this->usersModel->getUserFieldById('name', $userId);
        $userLastName = $this->usersModel->getUserFieldById('surname', $userId);
        $userFullName = $userFistName . ' ' . $userLastName;
        return $userFullName;
    }


    public function build_page($page_name) {    
        $htm_src = $this->output->get_page($page_name);   
        $html = $this->output->replace_localizations($htm_src);
        $this->output->render($html);
      }

}