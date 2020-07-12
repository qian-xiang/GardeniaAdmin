<?php
namespace app\admin\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return view('index');
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,后台应用';
    }
}
