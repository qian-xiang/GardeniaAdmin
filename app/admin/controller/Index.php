<?php
namespace app\admin\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return '后台应用';
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,后台应用';
    }
}
