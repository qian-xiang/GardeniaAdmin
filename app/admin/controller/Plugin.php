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

class Plugin extends AdminController
{
    public function index() {
        return 1111;
    }
    public function getData() {
        // 获取在线插件列表

        // 获取本地已安装的插件列表
    }
    public function install() {
        $runTimePath = runtime_path().'addon'.DIRECTORY_SEPARATOR.time();
        if (!file_exists($runTimePath)) {
            mkdir($runTimePath,0755,true);
        }
        $path = './static/core/addon/test-addon.zip';
        $zipHandle = zip_open($path);
        if (!is_resource($zipHandle)) {
            return $zipHandle;
        }
        $checkFileList = [
            'app/' => [
                'is_dir' => true,
                'must' => true,
            ],
            'asset/' => [
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
                return '模块文件不合法，请勿上传无关的文件或目录';
            }
            zip_entry_close($fileEntry);
        }
        zip_close($zipHandle);
        $intersect = array_keys(array_intersect_key($actualList,$mustKeys));
        sort($intersect);
        $mustKeys = array_keys($mustKeys);
        sort($mustKeys);

        if ($intersect !== $mustKeys) {
            return '模块文件不合法，'.implode(',',$mustKeys).'必传';
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
                return '打开模块文件项失败';
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
                return '写出模块内文件项失败';
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
            return '插件已存在';
        }
        if (!file_exists(ADDON_DOR.$moduleName)) {
            mkdir(ADDON_DOR.$moduleName,0777);
        }
        //将模块内目录移动到相应目录
        $oldModulePath = $runTimePath.DIRECTORY_SEPARATOR.'app';
        rename($oldModulePath,ADDON_DOR.$moduleName);

        rename($runTimePath.DIRECTORY_SEPARATOR.'info.ini',ADDON_DOR.$moduleName.DIRECTORY_SEPARATOR.'info.ini');

        $publicPath = $runTimePath.DIRECTORY_SEPARATOR.'asset';
        if (file_exists($publicPath)) {
            $newPath = public_path().DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$moduleName;
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
            return '添加插件菜单失败';
        }
        Db::startTrans();
        //暂时先不校验插件请求

        //逻辑处理完成  删除对应的目录
        $res = remove_dir($runTimePath);
        if (!$res) {
            return '删除临时存放目录失败';
        }
        return '安装'.$info['title'].' 模块成功';
    }
}