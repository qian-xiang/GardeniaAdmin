<?php

namespace addon\fast_dev\app\admin\controller;

use think\Request;

class Test
{
    public function __construct()
    {
        dump('ces');
    }
    public function index(Request $request, $b= 1) {
        echo 3333;
    }
}