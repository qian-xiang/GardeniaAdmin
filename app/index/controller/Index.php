<?php
namespace app\index\controller;



use app\BaseController;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        return "<div>欢迎来到GardeniaAdmin &nbsp;栀子后台管理系统</div>";
    }
    public function test() {
        return create_password('admin123456','12345678');
//        View::assign('demo_time',time());
        return view();
    }
    public function install() {
        $runTimePath = runtime_path().time();
        if (!file_exists($runTimePath)) {
            mkdir($runTimePath,0755);
        }
        $path = './static/core/module/test-module.zip';
        $zipHandle = zip_open($path);
        if (!is_resource($zipHandle)) {
            return $zipHandle;
        }
        $items = 0;
        while ($fileEntry = zip_read($zipHandle)) {
            //模块合法目录
            $itemName = zip_entry_name($fileEntry);
            $final = substr($itemName,strlen($itemName)-1,1);
            if (strpos($itemName,'app'.DIRECTORY_SEPARATOR) !== false || strpos($itemName,'public'.DIRECTORY_SEPARATOR) !== false) {
                $res = zip_entry_open($zipHandle,$fileEntry,'rb');
                if (!$res) {
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
                    return '写出模块内文件项失败';
                }
                $items += 1;
                zip_entry_close($fileEntry);
            }
        }
        zip_close($zipHandle);
        if ($items < 2) {
            return '模块目录不符合要求';
        }
        //将模块内目录移动到相应目录
        $fileList = scandir($runTimePath);
        $moduleName = '';

        foreach ($fileList as $item) {
            if (!in_array($item,['.','..'])) {
                if ($item === 'app') {
                    $nextFileList = scandir($runTimePath.DIRECTORY_SEPARATOR.$item);
                    foreach ($nextFileList as $_item) {
                        if (!in_array($_item,['.','..'])) {
                            $moduleName = $_item;
                            break;
                        }
                    }
                    if (!$moduleName) {
                        return '未发现有模块存在';
                    }
                    $oldModulePath = $runTimePath.DIRECTORY_SEPARATOR.$item.DIRECTORY_SEPARATOR.$moduleName;
                    if (!(base_path().$moduleName)) {
                        return '模块已存在';
                    }
                    rename($oldModulePath,base_path().$moduleName);
                } elseif ('public') {
                    $newPath = public_path().DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.$moduleName;
                    rename($runTimePath.DIRECTORY_SEPARATOR.$item,$newPath);
                }

            }
        }

        //逻辑处理完成  删除对应的目录
        $res = remove_dir($runTimePath);
        if (!$res) {
            return '删除临时存放目录失败';
        }
        return '安装成功';
    }
    public function uninstall() {
        //删除模块应用目录和静态文件目录
        $moduleName = 'gardenia_module';
        $moduleAppPath = base_path().$moduleName;
        if (file_exists($moduleAppPath)) {
            remove_dir($moduleAppPath);
        }
        $moduleStaticPath = public_path().'static'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.$moduleName;
        if (file_exists($moduleStaticPath)) {
            remove_dir($moduleStaticPath);
        }
        if (!file_exists($moduleStaticPath) && !file_exists($moduleAppPath)) {
            return '卸载模块：'.$moduleName.' 成功';
        } else {
            return '卸载'.$moduleName.' 失败';
        }
    }
}
