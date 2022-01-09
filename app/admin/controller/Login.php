<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;

use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\AdminController;
use app\admin\model\Admin;
use Firebase\JWT\JWT;
use think\facade\Session;
use think\Validate;
use think\validate\ValidateRule;
use \constant\AppConstant as AppConstants;

class Login extends AdminController
{
    protected $noNeedLogin = ['index','login','test'];
    public function index()
    {
        return view('login');
    }
    public function login() {
        $data = $this->request->post();
        $validate = new Validate();
        $validate->rule([
           'username' => ValidateRule::isRequire(null,'用户名必填')->max(15,'用户名与所需格式不符'),
           'password' => ValidateRule::isRequire(null,'密码必填')->max(32,'密码与所需格式不符'),
           'captcha' => ValidateRule::isRequire(null,'验证码必填'),
        ]);

        if (!$validate->check($data)) {
            return $validate->getError();
        }
        if (!captcha_check($data['captcha'])) {
            error(lang('login.faultCaptcha'));
        }
        $admin = Admin::where([
            'username'=> $data['username'],
            'is_delete' => AppConstants::IS_DELETE_NO
        ])->find();
        if (!$admin) {
            error('该用户不存在或已被删除！');
        }
        if (!$admin['login_status']){
            error('您已被禁止登录！');
        }
        if (create_password($data['password'],$admin['salt']) !== $admin['password']) {
            error(lang('login.faultAccountPwd'));
        }
        $loginTime = time();
        //生成登录token存入数据库
        if (env('admin_login.login_type',AppConstants::LOGIN_TYPE_COOKIE) === AppConstants::LOGIN_TYPE_COOKIE) {
            $token = login_token_generate($admin['id']);
        } else {
            $secret = env('admin_login.jwt_secret','gardenia_1234567');
            $payload = array(
                "id" => $admin['id'],
                "username" => $admin['username'],
                'login_time' => $loginTime,
            );
            $token = JWT::encode($payload, $secret);
        }

        $updateData = [
            'login_ip'=> $this->request->ip(),
            'login_time'=> $loginTime,
            'last_login_ip'=> $admin['login_ip'] ? $admin['login_ip'] : '',
            'last_login_time'=> $admin['login_time'] ? $admin['login_time'] : '',
        ];
        if (env('admin_login.login_type',AppConstants::LOGIN_TYPE_COOKIE) === AppConstants::LOGIN_TYPE_COOKIE) {
            $updateData['login_code'] = $token;
            $res = Admin::update($updateData,['id' => $admin['id']]);
            if (!$res) {
                error(lang('login.updateLoginStatusFail'));
            }
            cookie('login_code',$token,7*24*60*60);
        }

//        trace($res ? '登录时更新数据成功！' : '登录时更新数据失败！','log');
//        trace('用户：'.$admin['username'].' 于'.date('Y-m-d H:i:s').' 登录！','log');
        $this->success(lang('login.succ'),url('/')->build());
    }

    /**
     * 注销登录
     */
    public function logout() {
        cookie('login_code',null);
        $this->success('注销成功！',url('/Login/index'));
    }

}
