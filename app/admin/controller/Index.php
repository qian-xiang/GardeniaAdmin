<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;


use constant\AppConstant;
use app\admin\AdminController;
use gardenia_admin\src\core\core_class\GardeniaList;
use \app\admin\model\Admin;

class Index extends AdminController
{
    public function index()
    {
//        View::assign('gardeniaLayout',[
//            'left' => [
//                'type' => 'content',
//                'content' => '<dl class="layui-nav-child">
//                                    <dd><a href="">基本资料</a></dd>
//                                    <dd><a href="">安全设置</a></dd>
//                                </dl>',
//                'vars' => [],
//            ],
//        ]);
        $gardeniaList = new GardeniaList();
        $gardeniaList->view();
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
