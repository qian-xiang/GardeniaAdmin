<?php

namespace addon\fast_dev\app\admin\controller;

use think\Request;

class Test
{

    public function index(Request $request, $b= 1) {
        echo $request->method();
    }
}