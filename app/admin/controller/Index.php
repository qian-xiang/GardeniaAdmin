<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;


//use app\admin\extend\core\core_class\GardeniaTable;
//use app\admin\extend\diy\extra_class\AppConstant;
use app\BaseController;
use \gardenia_admin\src\core\core_class\GardeniaForm;
use \think\facade\Db;

class Index extends BaseController
{
    public function index()
    {
//        $list = Db::name(AppConstant::TABLE_USER)->select()->toArray();
        $gardeniaTable = new GardeniaForm();
        return $gardeniaTable->display();

    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,后台应用';
    }
}
