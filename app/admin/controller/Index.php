<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;


use constant\AppConstant;
use app\admin\AdminController;
use \app\admin\model\Admin;

class Index extends AdminController
{
    public function index()
    {
        return $this->view();
    }
    public function getData() {
        $list = Admin::withAttr('login_status',function ($value){
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
}
