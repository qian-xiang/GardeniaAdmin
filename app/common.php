<?php
use \constant\AppConstant;
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
if (!function_exists('check_module_item')) {
    /**
     * 检测模块的文件是否合法
     * @param array $arr 文件名数组
     * @param array $rules 规则数组
     * @return bool
     */
    function check_module_item($arr = [],$rules = []) {
        foreach ($rules as $item) {
            $count = 0;
            foreach ($arr as $_item) {
                if (substr($item,strlen($item) - 1,1) === DIRECTORY_SEPARATOR) {
                    if (strpos($_item,$item) === 0) {
                        $count++;
                    }
                } elseif ($_item === $item) {
                    $count++;
                }
            }
            unset($_item);
            if (!$count) {
                return false;
            }
        }
        unset($item);
        return true;
    }
}
if (!function_exists('check_module_info')) {
    /**
     * 检测模块信息文件的信息是否合法
     * @param $info
     * @return bool|string
     */
    function check_module_info($info) {
        $list = [
            'name' => '名称',
            'title' => '标题',
            'intro' => '介绍',
            'versionCode' => '三段版本号',// 1.0.0
            'versionNum' => '版本号', // 100
            'versionDesc' => '版本描述',
            'status' => '状态',
            'author' => '作者',
            'website' => '官网',
        ];
        $keys = array_keys($list);
        $errMsg = '模块信息文件中需要包含以下信息：'.implode(',',$keys).'，分别代表：'.implode(',',array_values($list));
        if (!$info || !is_array($info)) {
            return $errMsg;
        }
        if (substr($info['name'],strlen($info['name']) - 1,1) === '.') {
            return '模块名称最后一位不能是.';
        }
        if (implode(',',array_keys(array_intersect_key($list,$info))) === implode(',',$keys)) {
            return true;
        } else {
            return $errMsg;
        }
    }
}
if (!function_exists('reply_json')) {
    /**
     * 返回json信息
     * @param string $msg
     * @param array $data
     * @param string $redirectUrl 跳转的url
     * @param int $code
     */
    function reply_json($msg = '', $data = [], $redirectUrl = '', $code = AppConstant::CODE_SUCCESS) {
        json([
            'msg' => $msg,
            'data' => $data,
            'code' => $code,
            'redirectUrl' => $redirectUrl,
        ])->send();
    }
}
if (!function_exists('success_json')) {
    /**
     * 以json形式返回成功信息
     * @param string $msg
     * @param array $data
     * @param string $redirectUrl 跳转的url
     */
    function success_json($msg = '',$data = [], $redirectUrl = '') {
        reply_json($msg,$data, $redirectUrl,AppConstant::CODE_SUCCESS);
    }
}
if (!function_exists('error_json')) {
    /**
     * 以json形式返回错误信息
     * @param string $msg
     * @param array $data
     * @param string $redirectUrl 跳转的url
     */
    function error_json($msg = '',$data = [], $redirectUrl = '') {
        reply_json($msg,$data, $redirectUrl,AppConstant::CODE_ERROR);
    }
}
if (!function_exists('build_toolbar_btn')) {
    function build_toolbar_btn($btns = '') {
        if (!$btns) {
            return '';
        }
        $btnList = [
            'add',
            'del'
        ];
        $arr = explode(',',$btns);
        $arrIntersect = array_intersect($arr,$btnList);
        if ($arrIntersect !== $arr) {
            throw new \think\Exception('toolbar的按钮类型仅支持：'.implode(',',$btnList));
        }
        // 按钮类型合法 开始构建html模板
        $html = '';
        foreach ($arr as $item) {
            $tmp = '';
            switch ($item) {
                case 'add':
                    $tmp = '<a class="btn btn-primary btn-operate-'.$item.'" href="'.url('add')->build().'">
                            <i class="fa fa-plus-square"></i> '.lang('btn-operate-'.$item).'
                        </a> ';
                    break;
                case 'del':
                    $tmp = '<a class="btn btn-danger btn-operate-'.$item.'" href="javascript: void(0)">
                            <i class="fa fa-trash"></i> '.lang('btn-operate-'.$item).'
                        </a> ';
                    break;
                default:
                    break;
            }
            $html .= $tmp;
        }
        unset($item);

        return $html;
    }
}
