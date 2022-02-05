<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the Apache2.0 license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\common\core\lib\upload;

use app\admin\model\Upload;
use think\facade\Filesystem;

class UploadLibrary
{
    public function upload($data = []) {
        $tag = empty($data['tag']) ? 'default' : $data['tag'];
        // 获取表单上传文件
        $savename = [];
        $insert = [];
        foreach($data['file'] as $file){
            $savePath = Filesystem::disk('public')->putFile($tag, $file);
            $filePath = Filesystem::disk('public')->url($savePath);
            $mime = $file->getMime();
            $savename[] = [
                'url' => $filePath,
                'name' => $file->getOriginalName(),
                'mime' => $mime,
            ];
            $insert[] = [
                'name' => $file->getOriginalName(),
                'url' => $savePath,
                'size' => $file->getSize(),
                'ext' => $file->getExtension(),
                'mime' => $mime,
                //暂时固定7
                'admin_id' => 7,
            ];
        }
        $uploadModel = new Upload();
        $uploadModel->saveAll($insert);
        return $savename;
    }
}