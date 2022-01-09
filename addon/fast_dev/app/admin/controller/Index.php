<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace addon\fast_dev\app\admin\controller;

use app\admin\AdminController;
use think\Request;

class Index extends AdminController
{
    public function index(Request $request) {
        $this->view();
    }
}