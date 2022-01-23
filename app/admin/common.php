<?php
// 应用公共文件
use think\App;

if (!function_exists('password_encrypt')) {
    /**
     * 后台数据库用户表的密码加密方式
     * @param $password
     * @return false|string|null
     */
    function password_encrypt($password) {
        return password_hash($password,PASSWORD_DEFAULT);
    }
}

if (!function_exists('login_token_generate')) {
    /**
     * 生成登录token
     * @param string $data
     * @return string
     */
    function login_token_generate($data = '') {
        return md5($data.uniqid('login_token',true));
    }
}
if (!function_exists('get_client_ip')) {
    /**
     * 获取客户端IP地址
     * @return mixed
     */
    function get_client_ip() {
        return $_SERVER['REMOTE_ADDR'];
    }
}




