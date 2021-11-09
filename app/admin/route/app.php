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
use think\Exception;
use \think\facade\Config;


Route::post('login', 'login/login')->token();

Route::any('addon', function (\think\Request $request) {
    $paramList = get_addon_action_param();
    $parseList = parse_addon_url();
    $addonController = $parseList['controller'];
    $addonName = $parseList['addonName'];
    $appName = 'admin';
    $addonControllerPath = \think\ADDON_DOR.DIRECTORY_SEPARATOR.$addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$appName.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.$addonController.'.php';
    if (!file_exists($addonControllerPath)) {
        throw new Exception('插件的控制器文件不存在');
    }
//    include $addonControllerPath;
//    $queryStr = $request->query();
//    $queryStr = $queryStr ? '?'.$queryStr : '';
    spl_autoload_register(function ($class) {
        $path = root_path().$class.'.php';
        $path = str_replace('\\','/',$path);
       require_once $path;
    });

    //加载插件控制器、配置文件、common.php
//    load_addon_lib($addonName);
    // 对当前访问的插件的控制器方法执行参数绑定

//    $request->setUrl('/'.$originController.'/'.$arr[4].$queryStr);
//    $controList = explode('/',$addonController);
//    $request->setController($controList[count($controList) -1]);
//    $request->setAction($arr[4]);

    $action = $parseList['action'];

    $addonController = '\\addon\\'.$addonName.'\\app\\'.$appName.'\\controller\\'.$addonController;
    $reflect  = new ReflectionClass($addonController);
    if (!$reflect->hasMethod($action)) {
        throw new Exception('控制器：'.$addonController.' 不存在方法：'.$action);
    }

    define('ADDON_REQUEST',1);
    define('ADDON_APP','admin');

    $construct = $reflect->getConstructor();
    if ($construct) {
        $conParams = $construct->getParameters();
        $_param = [];
        foreach ($conParams as $param) {
            $type = $param->getType();
            if (is_null($type) || $type->isBuiltin()) {

            } elseif ($param->getName() instanceof Closure) {
                //复合数据类型
            } else {
                //实例化这个参数 目前是 new App
                $_param[] = $param->getClass()->newInstanceArgs();
            }
        }
        unset($param);
        $reflect->newInstanceArgs($_param);
    }


    $reflectMethod = $reflect->getMethod($action);
    $reflectParams = $reflectMethod->getParameters();

    $params = [];
    foreach ($reflectParams as $param) {
        $type = $param->getType();
        if (is_null($type) || $type->isBuiltin()) {
            $params[] = $paramList[$param->getName()];

        } elseif ($param->getName() instanceof Closure) {
            //复合数据类型
        } else {
            $class = $param->getClass();
            $params[] = $class->newInstanceArgs();
        }
    }
    $res = $reflectMethod->invoke(new $addonController,...$params);
//    $instance = new $addonController(new \think\App());
    if ($res && (is_string($res) || is_int($res) || is_float($res))) {
        echo $res;
    } elseif (!is_null($res)) {
        throw new Exception('只能输出字符串和数值类型的数据');
    }
})->token();
