<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\middleware;


use think\facade\Db;

class CheckLogin
{
    public function handle($request, \Closure $next)
    {
        //免除登录的url
        $checkWhiteList = ['Login/index','Captcha/index'];//最后一个是验证码的url

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
        $request->user = [
            'id' => $user['id'],
            'login_code' => $loginCode,
            'last_login_time' => $user['last_login_time'],
            'last_login_ip' => $user['last_login_ip'],
            'login_time' => $user['login_time'],
            'login_ip' => $user['login_ip'],
        ];
        return $next($request);
    }

}