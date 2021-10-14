<?php
namespace app\index\controller;

use app\BaseController;

class Index
{
    public function index()
    {
        return "<div>欢迎来到GardeniaAdmin &nbsp;栀子后台管理系统</div>";
    }
    public function test() {
        $salt = create_salt();
        $password = create_password('123456',$salt);
        dump($salt,$password);
    }
}
