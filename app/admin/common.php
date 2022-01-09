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
if (!function_exists('success')) {
    function success($content = '',$redirectUrl = null,$second = 3) {
        $redirectUrl = $redirectUrl === null ? url('/'.request()->controller()) : $redirectUrl;
        if (is_addon_request()) {
            $viewConfig = require_once root_path().'addon/fast_dev/config/view.php';
            $thinkView = new \think\view\driver\Think(new App(),$viewConfig);
        } else {
            $thinkView = new \think\view\driver\Think(new App());
        }
        $thinkView->fetch('common/success',[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ]);
//        view('common/success',[
//            'content' => $content,
//            'redirectUrl' => $redirectUrl,
//            'second' => $second
//        ])->send();
    }

}
if (!function_exists('error')) {
    function error($content = '',$redirectUrl = null,$second = 3) {
        $appPath =  app_path();
        // 如果是插件请求，更改视图默认的访问位置
        $viewConfig = [];
        if (is_addon_request()) {
            $appPath = $appPath.ADDON_APP.DIRECTORY_SEPARATOR;
            $viewConfig = require_once root_path().'app/common/addon/config/view.php';
        } else {
            $viewConfig = config('view');
        }
        $app = new App();
        $app->setAppPath($appPath);
        $thinkView = new \think\view\driver\Think($app,$viewConfig);

        $template = $appPath.$viewConfig['view_dir_name'].DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'error'.($viewConfig['view_suffix'] ? '.'.$viewConfig['view_suffix'] : '');

        $thinkView->fetch($template,[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ]);
    }
}



