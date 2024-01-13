<?php

namespace App\Models;
use App\Views\View;


class ChatModel extends DbModel{
    private $output;
    public function __construct(View $view){
        parent:: __construct();
        $this->output = $view;
    }

    public function build_page($page_name) {    
        $htm_src = $this->output->get_page($page_name);   
        $html = $this->output->replace_localizations($htm_src);
        $this->output->render($html);
      }
}