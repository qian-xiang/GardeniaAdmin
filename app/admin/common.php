<?php
// 应用公共文件
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
if (!function_exists('success')) {
    function success($content = '',$redirectUrl = null,$second = 3) {
        $redirectUrl = $redirectUrl === null ? url('/'.request()->controller()) : $redirectUrl;
        view('common/success',[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ])->send();
    }

}
if (!function_exists('error')) {
    function error($content = '',$redirectUrl = null,$second = 3) {
        $redirectUrl = $redirectUrl === null ? request()->header('referer') : $redirectUrl;
        view('common/error',[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ])->send();
    }
}


