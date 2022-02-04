<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the Apache2.0 license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\common\controller;


use app\BaseController;
use app\common\core\lib\upload\UploadLibrary;
use app\validate\common\UploadValidate;
use think\facade\Config;
use think\File;

/**
 * 后面再考虑登录鉴权
 * Class Common
 * @package app\common\controller
 */
class Common extends BaseController
{
    public function upload() {
        $data = $this->request->get();
        $data['file'] = $this->request->file('file');
        $validate = new UploadValidate();
        if (!$validate->check($data)) {
            error($validate->getError());
        }
        if ($data['file'] instanceof File) {
            $data['file'] = [$data['file']];
        }
        foreach ($data['file'] as $file) {
            $extList = Config::get('app.upload.accept_ext','');
            if ($extList && ($extList = explode(',',$extList)) && !in_array($file->extension(),$extList)) {
                error('不支持上传该类型的文件');
            }
        }

        $uploadLibrary = new UploadLibrary();
        success('上传成功！',$uploadLibrary->upload($data));
    }
}