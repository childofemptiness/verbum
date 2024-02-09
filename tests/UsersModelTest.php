<?php

define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(__FILE__) . DS . "app");

use App\Models\UsersModel;
use PHPUnit\Framework\TestCase;


require_once('./app/config/config.php');


class UsersModelTest extends TestCase {
    private $usersModel;

    public function setUp(): void {
        $this->usersModel = new UsersModel();
        $this->usersModel->beginTransaction();
    }

    public function testGetUserId_ReturnsUserId_WhenUsernameExists() {
        $userName = 'mamgus';
        $expectedUserId = '18';
        
        // Вызов метода getUserId и проверка результата
        $actualUserId = $this->usersModel->getUserId($userName);
        $this->assertEquals($expectedUserId, $actualUserId);
    }

    public function testGetUserid_ReturnsUserIs_WhenUsernameNotExists() {
        $userName = 'notExists';
        
        $actualId = $this->usersModel->getUserId($userName);
        $this->assertNull($actualId);
    }
    public function tearDown(): void {
        $this->usersModel->rollBack();
    }
}