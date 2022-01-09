<?php


namespace app\admin\library;


use think\Request;
use think\Exception;

class AddonRoute
{
    public function handleAddonRoute(Request $request) {
        $paramList = get_addon_action_param();
        $parseList = parse_addon_url();
        $addonController = $parseList['controller'];
        $addonName = $parseList['addonName'];
        $appName = 'admin';
        $addonControllerPath = \think\ADDON_DOR.$addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$appName.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.$addonController.'.php';
        if (!file_exists($addonControllerPath)) {
            throw new Exception('插件的控制器文件不存在');
        }
//    include $addonControllerPath;
//    $queryStr = $request->query();
//    $queryStr = $queryStr ? '?'.$queryStr : '';


        //加载插件控制器、配置文件、common.php
//    load_addon_lib($addonName);
        // 对当前访问的插件的控制器方法执行参数绑定

//    $request->setUrl('/'.$originController.'/'.$arr[4].$queryStr);
//    $controList = explode('/',$addonController);
//    $request->setController($controList[count($controList) -1]);
//    $request->setAction($arr[4]);
        $action = $parseList['action'];

        $addonController = '\\addon\\'.$addonName.'\\app\\'.$appName.'\\controller\\'.$addonController;

        $reflect  = new \ReflectionClass($addonController);
        if (!$reflect->hasMethod($action)) {
            throw new Exception('控制器：'.$addonController.' 不存在方法：'.$action);
        }

        define('ADDON_REQUEST',1);
        define('ADDON_APP',$appName);
        define('ADDON_NAME',$addonName);
        //更改应用目录
    $addonAppDir = \think\ADDON_DOR.DIRECTORY_SEPARATOR.$parseList['addonName']
        .DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$appName.DIRECTORY_SEPARATOR;
        define('ADDON_APP_PATH',$addonAppDir);

        $construct = $reflect->getConstructor();
        if ($construct) {
            $conParams = $construct->getParameters();
            $_param = [];
            foreach ($conParams as $param) {
                $type = $param->getType();
                if (is_null($type) || $type->isBuiltin()) {

                } elseif ($param->getName() instanceof \Closure) {
                    //复合数据类型
                } else {
                    //实例化这个参数 目前是 new App
                    $_param[] = $param->getClass()->newInstanceArgs([root_path()]);
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
                $params[] = $paramList[$param->getName()] ?? null;

            } elseif ($param->getName() instanceof \Closure) {
                //复合数据类型
            } else {
                $class = $param->getClass();
                $params[] = $class->newInstanceArgs();
            }
        }

        $res = $reflectMethod->invoke(new $addonController,...$params);
        if ($res && (is_string($res) || is_int($res) || is_float($res))) {
            echo $res;
        } elseif (!is_null($res)) {
            throw new Exception('只能输出字符串和数值类型的数据');
        }
    }
}