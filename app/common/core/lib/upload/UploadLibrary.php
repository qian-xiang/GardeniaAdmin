<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the Apache2.0 license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\common\core\lib\upload;

use app\common\core\exception\AppException;
use think\facade\Config;
use think\facade\Filesystem;

class UploadLibrary
{
    public function upload($files = []) {
        $tag = input('get.tag','default');
        // 获取表单上传文件
        $savename = [];
        foreach($files as $file){
            //验证放到控制器
//            $extList = Config::get('app.upload.accept_ext','');
//            if ($extList && $extList = explode(',',$extList) && in_array($file->extension(),$extList)) {
//                throw new AppException('不支持上传该类型的文件');
//            }
            $savename[] = Filesystem::disk('public')->putFile($tag, $file);
        }
        return $savename;
    }
}