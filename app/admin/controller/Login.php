<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\controller;

use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\GardeniaController;
use think\facade\Db;
use think\Validate;
use think\validate\ValidateRule;

class Login extends GardeniaController
{
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
            $this->error('验证码错误！');
        }
        $result = Db::name(AppConstant::TABLE_USER)->where([
            'username'=> $data['username'],
        ])->find();
        if (!$result || $result['is_delete']) {
            $this->error('该用户不存在或已被删除！');
        }
        if (!$result['login_status']){
            $this->error('您已被禁止登录！');
        }
        if (!password_verify($data['password'],$result['password'])) {
            $this->error('用户名或密码错误，请重新输入！');
        }

        //生成登录token存入数据库
        $token = login_token_generate($result['id']);

        $updateData = [
            'id'=> $result['id'],
            'login_code'=> $token,
            'login_ip'=> get_client_ip(),
            'login_time'=> time(),
            'last_login_ip'=> $result['login_ip'],
            'last_login_time'=> $result['login_time'],
        ];
        $res = Db::name(AppConstant::TABLE_USER)->save($updateData);
        trace($res ? '登录时更新数据成功！' : '登录时更新数据失败！','log');
        trace('用户：'.$result['username'].' 于'.date('Y-m-d H:i:s').' 登录！','log');
        //登录有效期，7天
        setcookie('login_code',$updateData['login_code'],time() + 7*24*60*60);
        $this->success('登录成功！即将跳转到首页...',url('/index/index'));
    }
    public function test() {
        return password_encrypt('122333');
    }
}
