<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;

use app\admin\extend\core\core_class\GardeniaTable;
use app\admin\extend\diy\extra_class\AppConstant;
use app\BaseController;
use \think\facade\Db;

class Index extends BaseController
{
    public function index()
    {
        $list = Db::name(AppConstant::TABLE_USER)->select()->toArray();
        $gardeniaTable = new GardeniaTable();
        return $gardeniaTable->addTableHeader('id','ID')
            ->addTableHeader('username','用户名')
            ->addTableHeader('password','密码')
            ->addTableHeader('login_status','登录状态')
            ->addTableHeader('login_code','登录token')
//            ->addTableHeader('create_time','创建时间')
            ->setData($list)->display();

//        return view('index');
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,后台应用';
    }
}
