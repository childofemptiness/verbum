<?php

namespace App\Models;
use App\Views\View;

class MainModel extends DbModel{

    protected $output;

    public function __construct(View $view){
        parent:: __construct();
        $this->output = $view;
    }

    public function getUserName() {
        $userId = $this->sendUserId();
        $query = 'SELECT name, surname FROM users WHERE user_id = :userId';
        $params = ['userId' => $userId];
        $result = $this->get_query($query, $params);
        $fullName = $result[0]['name'] . ' ' . $result[0]['surname'];
        return $fullName;
    }

    public function build_page($page_name) {    
        $htm_src = $this->output->get_page($page_name);   
        $html = $this->output->replace_localizations($htm_src);
        $this->output->render($html);
      }

}