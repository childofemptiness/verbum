<?php

namespace App\Controllers;

use App\Core\HelperFunctions;

class FriendsController extends Controller {
    protected $helper;
    public function __construct($controller, $method) {
        parent:: __construct($controller, $method);
        $this->load_model();
        $this->helper = new HelperFunctions();
    }

    public function home() {
        $this->view->page_title = "Friends";
        $this->get_model()->build_page("home");
    }

    public function getuserid() {
        $userId = $this->get_model()->getUserId();
        $this->helper->send_json($userId);
    }

}