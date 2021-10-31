<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */

namespace app\admin\controller;


use app\admin\AdminController;
use gardenia_admin\src\core\core_class\GardeniaList;

class Module extends AdminController
{
    public function index() {
        $request = request();
        $gardeniaList = new GardeniaList();
        $gardeniaList->setTableAttr('url',url('/'.$request->controller().'/getData')->build())
            ->addTableHead('choose','选择',['type' => 'checkbox'])
            ->addTableHead('username','用户名')
            ->addTableHead('login_status','状态')
            ->addTableHead('last_login_time','上次登录时间')
            ->addTableHead('login_time','登录时间')
            ->addTableHead('operate','操作',['type' => 'normal'])
            ->addTopOperateButton('gardenia','新增','create',['id'=> 'create',
                'onclick'=> 'location.href="'.url('/'.request()->controller().'/create')->build().'"'])
            ->addTopOperateButton('gardenia','删除','delete',['id'=> 'delete'])
            ->addColumnOperateButton('operate','查看','gardenia','read','/Admin/read',['name'=> "item_read",'lay-event' => 'read'])
            ->addColumnOperateButton('operate','编辑','gardenia','edit','/Admin/edit',['name'=> "item_edit",'lay-event' => 'edit'],[
                'redirect-url' => url('/'.request()->controller().'/edit')->build()])
            ->addColumnOperateButton('operate','删除','gardenia','delete','/Admin/delete',['name' => 'item_delete','lay-event' => 'delete'])
            ->display();
    }
    public function getData() {
        // 获取在线模块列表

        // 获取本地已安装的模块列表
    }
}