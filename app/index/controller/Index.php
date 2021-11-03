<?php
namespace app\index\controller;



use app\BaseController;
use app\common\core\lib\GardeniaList;
use think\App;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        return "<div>欢迎来到GardeniaAdmin &nbsp;栀子后台管理系统</div>";
    }
    public function test() {
//        return create_password('admin123456','12345678');
        View::assign('demo_time',time());
        return view();
    }
    public function test2() {
        if ($this->request->isAjax() && $this->request->isGet()) {
            $data = [];
            for ($i = 0; $i < 50; $i++) {
                $data[] = [
                    'id' => $i + 1,
                    'name' => 'test'.time(),
                    'title' => '测试'.time(),
                    'createtime' => time(),
                ];
            }
            return json($data);
        }
        $gar = new GardeniaList();
        $gar->addTableHead('checked','',[
            'checkbox' => true
        ])
            ->addTableHead('id','ID')
            ->addTableHead('name','名称')
            ->addTableHead('title','标题')
            ->addTableHead('createtime','时间')
            ->setTableOptions('url','/index/index/test2')
            ->setTableOptions('pagination',false)
            ->setTableOptions('height',500)
//            ->setTableOptions('checkboxHeader',true)
            ->display();
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
        $checkFileList = [
          'app/' => [
              'is_dir' => true,
              'must' => true,
          ],
          'public/' => [
              'is_dir' => true,
              'must' => false,
          ],//可选
          'info.ini' => [
              'is_dir' => false,
              'must' => true,
          ],
          'route.php' => [
              'is_dir' => false,
              'must' => false,
          ],//可选
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
        if (file_exists(base_path().$moduleName)) {
            //逻辑处理完成  删除对应的目录
            remove_dir($runTimePath);
            return '模块已存在';
        }
        //将模块内目录移动到相应目录
        rename($runTimePath.DIRECTORY_SEPARATOR.'info.ini',$runTimePath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$info['name'].DIRECTORY_SEPARATOR.'info.ini');

        $oldModulePath = $runTimePath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$moduleName;
        rename($oldModulePath,base_path().$moduleName);
        $publicPath = $runTimePath.DIRECTORY_SEPARATOR.'public';
        if (file_exists($publicPath)) {
            $newPath = public_path().DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.$moduleName;
            rename($publicPath,$newPath);
        }
        $routePath = $runTimePath.DIRECTORY_SEPARATOR.'route.php';
        if (file_exists($routePath)) {
            $newRouteDir = base_path().config('app.default_app').DIRECTORY_SEPARATOR.'route';
            if (!file_exists($newRouteDir)) {
                mkdir($newRouteDir,0755);
            }
            $newPath = $newRouteDir.DIRECTORY_SEPARATOR.'mod_'.$moduleName.'.php';
            rename($routePath,$newPath);
        }
        //逻辑处理完成  删除对应的目录
        $res = remove_dir($runTimePath);
        if (!$res) {
            return '删除临时存放目录失败';
        }
        return '安装'.$info['title'].' 模块成功';
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
        $routePath = base_path().config('app.default_app').DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR.'mod_'.$moduleName.'.php';
        if (file_exists($routePath)) {
            unlink($routePath);
        }

        if (!file_exists($moduleStaticPath) && !file_exists($moduleAppPath) && !file_exists($routePath)) {
            return '卸载模块：'.$moduleName.' 成功';
        } else {
            return '卸载'.$moduleName.' 失败';
        }
    }
}
