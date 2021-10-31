<?php
// 应用公共文件
if (!function_exists('create_salt')) {
    /**
     * 创建随机盐值
     * @return string
     */
    function create_salt() {
        return md5(uniqid((string)time(),true));
    }
}

if (!function_exists('create_password')) {
    /**
     * 根据原始密码生成哈希后的密码
     * @param string $pwd
     * @param string $salt
     * @return string
     */
    function create_password($pwd = '', $salt = '') {
        return md5($salt.env('admin_login.password_salt','gardenia').md5($pwd));
    }
}
if (!function_exists('remove_dir')) {
    /**
     * 删除目录
     * @param $path
     * @return bool
     */
    function remove_dir($path) {
        $list = scandir($path);
        foreach ($list as $item) {
            if (!in_array($item,['.','..'])) {
                $wholePath = $path.DIRECTORY_SEPARATOR.$item;
                if (is_dir($wholePath)) {
                    remove_dir($wholePath);
                } else {
                    unlink($wholePath);
                }
            }
        }
        unset($item);
        rmdir($path);
        return !file_exists($path);
    }
}
