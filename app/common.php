<?php
use \constant\AppConstant;
use think\helper\Str;

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
if (!function_exists('check_plugin_info')) {
    /**
     * 检测插件文件的信息是否合法
     * @param $info
     * @return bool|string
     */
    function check_plugin_info($info) {
        return check_module_info($info);
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
if (!function_exists('load_addon_lib')) {
    function load_addon_lib($addonName = '') {
        $appName = app('http')->getName();

        //加载插件config.php和插件应用config.php
        $addonCommon = \think\ADDON_DOR.$addonName.'app'.DIRECTORY_SEPARATOR.'config.php';
        if (file_exists($addonCommon)) {
            include $addonCommon;
        }
        $addonCommon = \think\ADDON_DOR.$addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$appName.DIRECTORY_SEPARATOR.'config.php';
        if (file_exists($addonCommon)) {
            include $addonCommon;
        }

        //加载插件common.php和插件应用common.php
        $addonCommon = \think\ADDON_DOR.DIRECTORY_SEPARATOR.$addonName.DIRECTORY_SEPARATOR.'appcommon.php';
        if (file_exists($addonCommon)) {
            include $addonCommon;
        }
        $addonCommon = \think\ADDON_DOR.$addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$appName.DIRECTORY_SEPARATOR.'common.php';
        if (file_exists($addonCommon)) {
            include $addonCommon;
        }
        //加载插件控制器
        $appList = get_addon_app($addonName);

        if (in_array($appName,$appList) !== false) {
            load_addon_controller(\think\ADDON_DOR.$addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$appName.DIRECTORY_SEPARATOR.'controller');
        }

//        $_appList = array_diff($appList,[$appName]);
//        foreach ($_appList as $item) {
//            load_addon_controller(\think\ADDON_DOR.$addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$item.DIRECTORY_SEPARATOR.'controller');
//        }
//        unset($item);

    }
}
if (!function_exists('load_addon_controller')) {
    /**
     * 加载插件控制器文件
     * @param string $dir
     */
    function load_addon_controller($dir = '') {
        //先加载插件
        $list = glob($dir.'/*.php');
        $list = array_reverse($list);
        foreach ($list as $item) {
            if (is_dir($item)) {
                load_addon_controller($item);
            } else {
                require_once $item;
            }

        }
        unset($item);
    }
}
if (!function_exists('get_addon_app')) {
    /**
     * 获取插件的应用列表
     * @param string $addonName
     * @return array|string[]
     */
    function get_addon_app($addonName = '') {
        $list = glob(\think\ADDON_DOR.$addonName.'/app/*/');
        $list = array_map(function ($value) {
            return basename($value);
        },$list);
        return $list;
    }
}
if (!function_exists('parse_addon_url')) {
    /**
     * 解析插件url
     * @return array
     * @throws Exception
     */
    function parse_addon_url() {
        $request = request();
        $url = $request->url();
        if (strpos($url,'-') !== false) {
            throw new Exception('url中不能含有-');
        }
        $depr = config('route.pathinfo_depr');
        $url = trim($url,$depr);
        $arr = explode($depr,$url);
        //插件名称
        if (empty($arr[2])) {
            throw new Exception('url中的插件名称必传');
        }
        $addonName = $arr[2];
        //转换插件名称
        if (!file_exists(\think\ADDON_DOR.DIRECTORY_SEPARATOR.$arr[2])) {
            $arr[2] = Str::snake($arr[2]);
        }
        //控制器
        if (empty($arr[3])) {
            $arr[3] = 'Index';
        } else {
            $arr[3] = str_replace('.',$depr,$arr[3]);
        }
        $addonController = $arr[3];
        $_controllerList = explode('.',$addonController);
        $controllerName = $_controllerList[count($_controllerList) - 1];
        $originController = str_replace($depr,'.',$addonController);

        $originAction = '';
        // 控制器方法
        if (empty($arr[4])) {
            $arr[4] = 'index';
        } else {
            $_actionList = explode('?',$arr[4]);
            $originAction = $arr[4] = $_actionList[0];
        }
        return [
            //插件名称
            'addonName' => $addonName,
            'controllerName' => $controllerName,
            'originController' => $originController,
            'controller' => $addonController,
            'action' => $arr[4],
            'originAction' => $originAction,
        ];
    }
}
if (!function_exists('get_addon_action_param')) {
    /**
     * 获取插件控制方法参数
     * @param string $name 参数名称
     * @param string $default 默认值
     * @return mixed|string
     * @throws Exception
     */
    function get_addon_action_param($name = '',$default = '') {
        $depr = config('route.pathinfo_depr');
        $url = request()->url();
        $parseList = parse_addon_url();
        $appName = app()->http->getName();
        $prefix = $depr.$appName.$depr.'addon'.$depr.$parseList['addonName'].$depr.$parseList['originController'].
            ($parseList['originAction'] ? '/'.$parseList['originAction'] : '');

        $paramStr = substr($url,strpos($url,$prefix) + strlen($prefix));

        $suffix = config('route.url_html_suffix');
        $suffix = $suffix ? '.'.$suffix : '';
        $paramStr = trim(trim($paramStr,$suffix),$depr);

        $list = explode($depr,$paramStr);
        $len = count($list);
        $param = [];
        $key = 0;
        while ($key < $len) {
            $param[$list[$key]] = $list[$key + 1];
            $key += 2;
        }
        unset($key);
        return $name ? $param[$name] : $param;
    }
}
if (!function_exists('is_addon_request')) {
    /**
     * 是否是插件请求
     * @return bool
     */
    function is_addon_request() {
        return defined('ADDON_REQUEST');
    }
}

