<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\middleware;


use app\admin\extend\diy\extra_class\AppConstant;
use think\facade\Db;

class CheckLogin
{
    public function handle($request, \Closure $next)
    {
        //免除登录的url
        $checkWhiteList = ['Login/index','Login/login','Captcha/index'];//最后一个是验证码的url

        $server = $request->server();
        $pathInfo = $server['PATH_INFO'];

        if (strpos($pathInfo,'.'.config('view.view_suffix')) !== false){
            $arr = explode('.',$pathInfo);
        }
        $arr = explode('/',isset($arr[0]) ? $arr[0] : $pathInfo);
        $controller = '';
        $action = '';
        if (count($arr) <= 1){
            $controller = config('route.default_controller');
            $action = config('route.default_action');
        } else if (count($arr) === 2) {
            $controller = $arr[1] ? $arr[1] : config('route.default_controller');
            $action = config('route.default_action');

        } else if (count($arr) >= 3) {
            $controller = $arr[1] ? $arr[1] : config('route.default_controller');
            $action = $arr[2] ? $arr[2] : config('route.default_action');

        }
        foreach ($checkWhiteList as $item){
            $arr = explode('/',$item);
            if (strtolower($controller) === strtolower($arr[0]) && strtolower($action) === strtolower($arr[1])){
                return $next($request);
            }
        }
        $loginCode = cookie('login_code');

        if (!$loginCode){
            error('检测到您尚未登录或登录状态已过期，即将前往登录页面...',url('/Login/index'));
        }
        $user = Db::name('user')->where(['login_code' => $loginCode])->find();
        if (!$user){
            error('检测到您尚未登录或登录状态已过期，即将前往登录页面...',url('/Login/index'));
        }
        if ($user['login_status'] === AppConstant::STATUS_FORBID) {
            error('你已被禁止登录！',url('/Login/index'));
        }
        $res = Db::name('auth_group_access')->alias('a')->join('auth_group g','g.id = a.group_id')
            ->where(['a.uid' => $user['id']])->field('g.type as admin_type')->find();
        if (!$res){
            error('您没有权限访问');
        }

        $result = array_merge($user,$res);
        $request->user = $result;

        return $next($request);
    }

}