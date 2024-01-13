<?php

namespace App\Controllers;

use App\Core\HelperFunctions;

class ChatController extends Controller {
    protected $helper;
    public function __construct($controller, $method) {
        parent:: __construct($controller, $method);
        $this->load_model();
        $this->helper = new HelperFunctions();
    }

    public function client() {
        $this->view->page_title = "Chat";
        $this->get_model()->build_page("client");
    }
}