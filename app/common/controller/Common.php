<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the Apache2.0 license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\common\controller;


use app\admin\model\Upload;
use app\BaseController;
use app\common\core\lib\upload\UploadLibrary;
use app\validate\common\UploadValidate;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Filesystem;
use think\File;

/**
 * 后面再考虑登录鉴权
 * Class Common
 * @package app\common\controller
 */
class Common extends BaseController
{
    /**
     * 上传文件
     */
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

    /**
     * 获取上传文件列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function uploadFileList() {
        $data = $this->request->get();
        try {
            $this->validate($data,[
                'offset|偏移量' => 'require|integer',
                'limit|记录数' => 'require|integer',
            ]);
        } catch (ValidateException $e) {
            error($e->getMessage());
        }

        $data['limit'] = (int)$data['limit'];
        $data['offset'] = (int)$data['offset'];
        $map = [];
        if (!empty($data['search'])) {
            $map[] = [
                'name','like','%'.$data['search'].'%'
            ];
        }

        $list = Upload::where($map)->limit($data['offset'],$data['limit'])
            ->order(['id' => 'desc'])->select();
        $total = Upload::where($map)->count('id');

        return json([
            'rows' => $list,
            'total' => $total,
        ]);
    }
}