<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */

namespace app\admin\controller;


use app\admin\AdminController;
use \app\admin\model\AuthRule;
use constant\AppConstant;
use think\facade\Db;
use const think\ADDON_DOR;
use const think\ADDON_FRONT_DOR;

class Plugin extends AdminController
{
    private $pluginList = [
        [
            'name' => 'gardenia_addon',
            'title' => '栀子测试插件',
            'intro' => '这是一个测试插件',
            'versionCode' => '1.0.0',
            'versionNum' => 1,
            'versionDesc' => '修复了一些已知问题',
            'author' => '栀子浅香',
            'email' => '1111111@qq.com',
            'website' => 'https://www.baidu.com',
            'href' => './static/core/addon/test-addon.zip',
        ],
    ];
    public function index() {
        $request = $this->request;
        //先写死数据  后面再改
        $list = $this->pluginList;

        // 先暂定是这样获取插件列表的
        foreach ($list as &$item) {
            if (!file_exists(ADDON_DOR.$item['name'])) {
                $item['status'] = AppConstant::ADDON_STATUS_UNINSTALLED;
            } else {
                $pluginInfo = parse_ini_file(ADDON_DOR.$item['name'].'/info.ini');
                if (check_plugin_info($pluginInfo)) {
                    $item['status'] = $pluginInfo['status'];
                }
            }
        }

        if ($request->isAjax() && $request->isGet()) {
            return json($list);
        }

        return $this->view();
    }
    public function getData() {
        // 获取在线插件列表

        // 获取本地已安装的插件列表
    }
    public function install() {
        $data = $this->request->post();
        $valiErr = $this->validate($data,[
            'name|插件名称' => 'require',
        ]);
        if ($valiErr !== true) {
            error_json($valiErr);
        }
        $runTimePath = runtime_path().'addon'.DIRECTORY_SEPARATOR.time();
        if (!file_exists($runTimePath)) {
            mkdir($runTimePath,0755,true);
        }
        $pluginName = $data['name'];
        $path = ADDON_FRONT_DOR.$pluginName.'.zip';
        $zipHandle = zip_open($path);
        if (!is_resource($zipHandle)) {
            error_json('打开插件包失败，请稍候重试');
        }
        $checkFileList = [
            'app/' => [
                'is_dir' => true,
                'must' => true,
            ],
            'assets/' => [
                'is_dir' => true,
                'must' => false,
            ],//可选
            'info.ini' => [
                'is_dir' => false,
                'must' => true,
            ],
        ];
        //判断压缩文件是否合法
        $actualList = [];
        $mustKeys = [];
        while ($fileEntry = zip_read($zipHandle)) {
            //模块合法目录
            $itemName = zip_entry_name($fileEntry);
            $count = 0;
            foreach ($checkFileList as $key => $item) {
                // 如果是目录 则检测文件名最后一位是否是路径分隔符 如 /
                if ($item['is_dir']) {
                    //目录以及目录下的文件都是以目录开头的
                    if (strpos($itemName,$key) === 0) {
                        $count++;
                        $actualList[$key] = 1;
                    }
                    //如果是文件则检测文件名是否相等
                } elseif ($itemName === $key) {
                    $count++;
                    $actualList[$key] = 1;
                }
                if ($item['must']) {
                    //有可能会重复
                    $mustKeys[$key] = 1;
                }
            }
            unset($item);
            if (!$count) {
                zip_entry_close($fileEntry);
                zip_close($zipHandle);
                //删除临时目录
                remove_dir($runTimePath);
                error_json('插件文件不合法，请勿上传无关的文件或目录');
            }
            zip_entry_close($fileEntry);
        }
        zip_close($zipHandle);
        $intersect = array_keys(array_intersect_key($actualList,$mustKeys));
        sort($intersect);
        $mustKeys = array_keys($mustKeys);
        sort($mustKeys);

        if ($intersect !== $mustKeys) {
            error_json('插件文件不合法，'.implode(',',$mustKeys).'必传');
        }
        // 检测结束
        // 开始将压缩文件中写入到磁盘中
        // 该过程留待日后看看是否可以进一步优化
        $zipHandle = zip_open($path);
        if (!is_resource($zipHandle)) {
            return $zipHandle;
        }
        while ($fileEntry = zip_read($zipHandle)) {
            $itemName = zip_entry_name($fileEntry);
            $final = substr($itemName,strlen($itemName)-1,1);
            $res = zip_entry_open($zipHandle,$fileEntry,'rb');
            if (!$res) {
                //删除临时目录
                zip_entry_close($fileEntry);
                zip_close($zipHandle);
                remove_dir($runTimePath);
                error_json('打开插件文件项失败');
            }
            $str = '';
            while ($itemStr = zip_entry_read($fileEntry)) {
                $str .= $itemStr;
            }
            $targetPath = $runTimePath.DIRECTORY_SEPARATOR.$itemName;
            if ($final === DIRECTORY_SEPARATOR) {
                $res = true;
                if (!file_exists($targetPath)) {
                    $res = mkdir($targetPath,0755);
                }
            } else {
                $res = file_put_contents($targetPath,$str,LOCK_EX);

            }
            if (!$res) {
                //删除临时目录
                zip_entry_close($fileEntry);
                zip_close($zipHandle);
                remove_dir($runTimePath);
                error_json('写出插件内文件项失败');
            }
            zip_entry_close($fileEntry);
        }
        zip_close($zipHandle);

        //读取模块信息文件 info.ini
        $info = parse_ini_file($runTimePath.DIRECTORY_SEPARATOR.'info.ini');

        //检测模块信息文件是否合法
        if ($infoCheck = check_module_info($info) !== true) {
            return $infoCheck;
        }
        $moduleName = $info['name'];
        if (file_exists(ADDON_DOR.$moduleName)) {
            //逻辑处理完成  删除对应的目录
            remove_dir($runTimePath);
            error_json('插件已存在');
        }
        if (!file_exists(ADDON_DOR.$moduleName)) {
            mkdir(ADDON_DOR.$moduleName,0755);
        }
        //将模块内目录移动到相应目录
        $oldModulePath = $runTimePath.DIRECTORY_SEPARATOR.'app';
        rename($oldModulePath,ADDON_DOR.$moduleName);

        rename($runTimePath.DIRECTORY_SEPARATOR.'info.ini',ADDON_DOR.$moduleName.DIRECTORY_SEPARATOR.'info.ini');

        $publicPath = $runTimePath.DIRECTORY_SEPARATOR.'assets';
        if (file_exists($publicPath)) {
            $newPath = public_path().DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR;
            if (!file_exists($newPath)) {
                mkdir($newPath,0777);
            }
            $newPath .= 'assets';
            rename($publicPath,$newPath);
        }
        // 将插件的菜单url加入到菜单规则表中
        $addonUrl = 'addon/'.$moduleName.'/admin/index';
        Db::startTrans();
        $authRule = new AuthRule();
        $res = $authRule->save([
            'type' => AppConstant::RULE_TYPE_MENU,
            'name' => $addonUrl,
            'title' => $info['title'],
            'icon' => 'fa fa-align-justify',
            'level' => 1,
        ]);
        if ($res !== true) {
            Db::rollback();
            error_json('添加插件菜单失败');
        }
        Db::commit();
        //暂时先不校验插件请求

        //逻辑处理完成  删除对应的目录
        $res = remove_dir($runTimePath);
        if (!$res) {
            error_json('删除临时存放目录失败');
        }
        success_json('安装'.$info['title'].' 插件成功');
    }
    public function uninstall() {
        $data = $this->request->post();
        $valiErr = $this->validate($data,[
            'name|插件名称' => 'require',
        ]);
        if ($valiErr !== true) {
            error_json($valiErr);
        }
        //删除模块应用目录和静态文件目录
        $moduleName = $data['name'];
        $moduleAppPath = ADDON_DOR.$moduleName;
        $info = parse_ini_file($moduleAppPath.DIRECTORY_SEPARATOR.'info.ini');
        // 删除菜单表中的插件规则
        $authRule = AuthRule::where([
            'name' => 'addon/'.$info['name'].'/admin/index',
            'title' => $info['title'],
        ])->find();
        if (!$authRule) {
            error_json('未查询到该插件的信息');
        }
        Db::startTrans();
        if (!$authRule->delete()) {
            Db::rollback();
        }
        Db::commit();

        if (file_exists($moduleAppPath)) {
            remove_dir($moduleAppPath);
        }
        $moduleStaticPath = public_path().'static'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$moduleName;
        if (file_exists($moduleStaticPath)) {
            remove_dir($moduleStaticPath);
        }

        if (!file_exists($moduleStaticPath) && !file_exists($moduleAppPath)) {
            success_json('卸载插件：'.$moduleName.' 成功');
        } else {
            error_json('卸载'.$moduleName.' 失败');
        }
    }
}