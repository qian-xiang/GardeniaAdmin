<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;


use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\GardeniaController;
use gardenia_admin\src\core\core_class\GardeniaList;
use \think\facade\Db;

class Index extends GardeniaController
{
    public function index()
    {
        $loginCode = cookie('login_code');
        if (!$loginCode){
            $this->error('检测到您尚未登录或登录状态已过期，即将前往登录页面...',url('/Login/index'));
        }
        $gardeniaList = new GardeniaList();
        $gardeniaList->view();
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
}
