<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\middleware;

use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\GardeniaController;
use think\facade\Db;

class CheckAccess extends GardeniaController
{
    public function handle($request, \Closure $next)
    {
        $server = $request->server();
        $pathInfo = $server['PATH_INFO'];
        if (strpos($pathInfo,'.'.config('view.view_suffix')) !== false){
            $pathInfo = explode('.',$pathInfo);
            $pathInfo = $pathInfo[0];
        }
//        $pathInfo = explode('/',isset($pathInfo[0]) ? $pathInfo[0] : $pathInfo);

//        $controller = '';
//        $action = '';
//        if (count($arr) <= 1){
//            $controller = config('route.default_controller');
//            $action = config('route.default_action');
//        } else if (count($arr) === 2) {
//            $controller = $arr[1] ? $arr[1] : config('route.default_controller');
//            $action = config('route.default_action');
//
//        } else if (count($arr) === 3) {
//            $controller = $arr[1] ? $arr[1] : config('route.default_controller');
//            $action = $arr[2] ? $arr[2] : config('route.default_action');
//
//        } else {
//            $this->error('输入的url有误，请重新输入');
//        }
        $loginCode = cookie('login_code');
        if (!$loginCode) {
            $this->error('检测到您尚未登录，请先登录。',url('/Login/index'));
        }

        $res = Db::name('auth_rule')->alias('r')->where('r.id','in',function ($query) use ($loginCode) {
            $_query = $query->name('auth_group_access')->alias('a')->join('user u','u.id = a.uid')
                ->where(['u.login_code' => $loginCode])->field('a.group_id')->select();
            $query->table($_query.' t')->join('auth_group g','g.id = t.group_id')
                ->where(['g.status'=> AppConstant::STATUS_FORMAL])->field('g.rules');
        })->field('r.name')->select()->toArray();
        halt($res);
        if (!$res){
            $this->error('您没有权限访问');
        }
        $res = $res[0];
        $accessArr = explode(',',$res['rules']);
        $access = $pathInfo;
        if (!in_array($access,$accessArr)) {
            $this->error('您没有权限访问');
        }
        return $next($request);
    }
}