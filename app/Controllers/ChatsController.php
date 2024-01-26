<?php

namespace App\Controllers;

use App\Core\HelperFunctions;

class ChatsController extends Controller {
    protected $helper;
    public function __construct($controller, $method) {
        parent:: __construct($controller, $method);
        $this->load_model();
        $this->helper = new HelperFunctions();
    }

    public function dialog() { 
        $this->view->page_title = "Chat";
        $this->get_model()->build_page('chat');
    }

    public function senddialogid() {
        $data = $this->helper->catchJson();
        $interlocutorId = $data['tag'] - 666666;
        $dialogId = $this->get_model()->getDialogId($interlocutorId);

        if (!$dialogId) $dialogId = $this->get_model()->startNewDialog($interlocutorId);
        $this->helper->sendJson($dialogId);
    }

    public function sendinterlocutorinfo($dialogId) {
        $interlocutorId = $this->get_model()->getInterlocutorId($dialogId);
        $info = $this->get_model()->getInterlocutorInfo($interlocutorId);
        $this->helper->sendJson($info);
    }

    public function senduserid() {
        $userId = $this->get_model()->getUserIdFromSession();
        $this->helper->sendJson($userId);
    }
}