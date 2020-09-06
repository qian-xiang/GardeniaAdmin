<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;


use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\GardeniaController;
use \think\facade\Db;

class Index extends GardeniaController
{
    public function index()
    {
//        return 1111;
//        $gardeniaList = new GardeniaList();
//        $gardeniaList->addListHead('id','ID')
//            ->addListHead('username','用户名')
//            ->addListHead('login_code','登录标识')
//            ->addListHead('login_status','状态')
//            ->display();
    }
    public function getData() {
        $list = Db::name(AppConstant::TABLE_USER)
            ->withAttr('login_status',function ($value){
                return AppConstant::getStatusAttr($value);
            })->select()->toArray();

        $data = [
            'code' => AppConstant::CODE_SUCCESS,
            'msg' => '获取成功！',
            'count' => count($list),
            'data' => $list
        ];

        return response($data,200,[],'json');
    }
    public function test()
    {
        $auth = new \Auth();
        dump($auth);
    }
}
