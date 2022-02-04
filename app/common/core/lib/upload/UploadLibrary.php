<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the Apache2.0 license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\common\core\lib\upload;

use think\facade\Filesystem;

class UploadLibrary
{
    public function upload($data = []) {
        $tag = empty($data['tag']) ? 'default' : $data['tag'];
        // 获取表单上传文件
        $savename = [];
        foreach($data['file'] as $file){
            $filePath = Filesystem::disk('public')->putFile($tag, $file);
            $filePath = Filesystem::disk('public')->url($filePath);
            $savename[] = [
                'url' => $filePath,
                'filename' => $file->getOriginalName(),
            ];
        }
        return $savename;
    }
}