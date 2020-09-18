<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\middleware;

use app\admin\extend\diy\extra_class\AppConstant;
use think\facade\Db;

class CheckAccess
{
    public function handle($request, \Closure $next)
    {
        $loginCode = cookie('login_code');

        //免除权限校验的url
        $checkWhiteList = ['Login/index','Login/login','Captcha/index'];//最后一个是验证码的url

        $server = $request->server();
        $pathInfo = $server['PATH_INFO'];

        if (strpos($pathInfo,'.'.config('view.view_suffix')) !== false){
            $pathInfo = explode('.',$pathInfo);
            $pathInfo = $pathInfo[0];
        }
        $controller = '';
        $action = '';
        if (!$pathInfo) {
            $pathInfo = ['',config('route.default_controller'),config('route.default_action')];
            $controller = config('route.default_controller');
            $action = config('route.default_action');
        } else {
            if ($pathInfo !== '/'){
                $pathInfo = explode('/',$pathInfo);
                $controller = isset($pathInfo[1]) ? $pathInfo[1] : config('route.default_controller');
                $action = isset($pathInfo[2]) ? $pathInfo[2] : config('route.default_action');
            } else {
                $pathInfo = ['',config('route.default_controller'),config('route.default_action')];
                $controller = config('route.default_controller');
                $action = config('route.default_action');
            }
        }
        foreach ($checkWhiteList as $item){
            $arr = explode('/',$item);
            if (strtolower($controller) === strtolower($arr[0]) && strtolower($action) === strtolower($arr[1])){
                $request->accessWhiteList = $checkWhiteList;
                return $next($request);
            }
        }

        if ($request->user['admin_type'] === AppConstant::GROUP_TYPE_ADMIN){
            $query = Db::name('auth_group_access')->alias('a')->join('user u','u.id = a.uid')
                ->where(['u.login_code' => $loginCode])->field('a.group_id')->buildSql(true);

            $query = Db::table($query.' t')->join('auth_group g','g.id = t.group_id')
                ->where(['status'=> AppConstant::STATUS_FORMAL])->field('g.rules')->find();
            $query = $query['rules'];

            $accessArr = Db::name('auth_rule')->where('id','in',$query)
                ->where(['status'=> AppConstant::STATUS_FORMAL])->column('name');

        } else {
            $accessArr = Db::name('auth_rule')
                ->where(['status'=> AppConstant::STATUS_FORMAL])->column('name');
        }

        if (!$accessArr){
            error('您没有权限访问');
        }
        foreach ($accessArr as &$item) {
            if ($item === '/'){
                $item = '/'.config('route.default_controller').'/'.config('route.default_action');
            }
        }
        $access = '/'.$controller.'/'.$action;

        if (!in_array($access,$accessArr)) {
            error('您没有权限访问');
        }
        $user = $request->user;
        $user['access_list'] = $accessArr;
        $request->user = $user;

        return $next($request);
    }
}