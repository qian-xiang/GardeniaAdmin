<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;


//use app\admin\extend\core\core_class\GardeniaTable;
//use app\admin\extend\diy\extra_class\AppConstant;
use \gardenia_admin\src\core\core_class\GardeniaForm;
use \think\facade\Db;

class Index
{
    public function index()
    {
//        $list = Db::name(AppConstant::TABLE_USER)->select()->toArray();
        $gardeniaForm = new GardeniaForm();
        $gardeniaForm = $gardeniaForm->addFormItem('用户名啊啊','text','username','嘿嘿和')
            ->addFormItem('ooo','switch','switch1');
        $gardeniaForm->display();

    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,后台应用';
    }
}
