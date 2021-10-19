<?php
namespace app\index\controller;



use app\BaseController;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        return "<div>欢迎来到GardeniaAdmin &nbsp;栀子后台管理系统</div>";
    }
    public function test() {
        View::assign('demo_time',time());
        return view();
    }
}
