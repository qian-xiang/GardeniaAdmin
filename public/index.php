<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';
const ADDON_DOR = '../addon/';
// 前台插件目录
const ADDON_FRONT_DOR = './static/core/addon/';
// 前台模块目录
const MODULE_FRONT_DOR = './static/core/module/';

// 执行HTTP应用并响应
$http = (new App())->http;
//->name('index')
$response = $http->run();

$response->send();

$http->end($response);
