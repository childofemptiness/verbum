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

    public function setfriendrequest() {
        $data = $this->helper->catchJson();
        $this->get_model()->setFriendRequest($data);
    }

    public function getuserid() {
        $userId = $this->get_model()->getUserIdFromSession();
        $this->helper->send_json($userId);
    }

    public function getallsentrequests() {
        $requests = $this->get_model()->getAllSentRequests();
        $this->helper->send_json($requests);
    }

    public function getallreceivedrequests() {
        $requests = $this->get_model()->getAllReceivedRequests();
        $this->helper->send_json($requests);
    }

    public function getresponsetorequest() {
        $data = $this->helper->catchJson();
        if ($data['flag']) $this->get_model()->acceptFriendRequest($data['userId']);
        else $this->get_model()->deleteAFriendRelation($data['userId']);
    }

    public function sendfriendslist() {
        $data = $this->get_model()->getFriendsList();
        $this->helper->send_json($data);
    }

    public function getfriendtagtodelete() {
        $data = $this->helper->catchJson();
        $this->get_model()->deleteFriend($data);
    }
}