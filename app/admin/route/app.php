<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use think\helper\Str;
use think\Exception;

Route::post('login', 'login/login')->token();

Route::any('addon', function (\think\Request $request) {
    $url = $request->url();
    if (strpos($url,'-') !== false) {
        throw new Exception('url中不能含有-');
    }
    $url = trim($url,'/');
    $depr = config('route.pathinfo_depr');
    $arr = explode($depr,$url);
    //插件名称
    if (empty($arr[2])) {
        throw new Exception('url中的插件名称必传');
    }
    $addonApp = $arr[2];
    //转换插件名称
    if (!file_exists(\think\ADDON_DOR.DIRECTORY_SEPARATOR.$arr[2])) {
        $arr[2] = Str::snake($arr[2]);
    }
    //控制器
    if (empty($arr[3])) {
        $arr[3] = 'Index';
    } else {
        $arr[3] = str_replace('.','/',$arr[3]);
    }
    $addonController = $arr[3];
    $originController = str_replace('/','.',$addonController);

    // 控制器方法
    if (empty($arr[4])) {
        $arr[4] = 'index';
    } else {
        $_actionList = explode('?',$arr[4]);
        $arr[4] = $_actionList[0];
    }
    $appName = 'admin';
    $addonControllerPath = \think\ADDON_DOR.DIRECTORY_SEPARATOR.$arr[2].DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$appName.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.$arr[3].'.php';
    if (!file_exists($addonControllerPath)) {
        throw new Exception('插件的控制器文件不存在');
    }
    include $addonControllerPath;
    $queryStr = $request->query();
    $queryStr = $queryStr ? '?'.$queryStr : '';

    $request->setUrl('/'.$originController.'/'.$arr[4].$queryStr);
    $controList = explode('/',$addonController);
    $request->setController($controList[count($controList) -1]);
    $request->setAction($arr[4]);
    $addonController = '\\'.$arr[2].'\\app\\'.$appName.'\\controller\\'.$addonController;
    $instance = new $addonController();
    $action = $arr[4];
    $res = $instance->$action();
    if ($res && (is_string($res) || is_int($res) || is_float($res))) {
        echo $res;
    } else {
        throw new Exception('只能输入字符串和数值类型的数据');
    }
})->token();
