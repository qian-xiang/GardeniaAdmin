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

    /**
     * 生成预览图片内容
     * @return \think\Response
     */
    public function createPreviewImage() {
        $content = $this->request->get('content','File');
        $content = mb_substr($content,0,4);
        $template = <<<'EOT'
<svg version="1.1"
         baseProfile="full"
         width="100" height="80"
         xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="#16c2c2" />
        <text x="46" y="42" font-size="30" text-anchor="middle" fill="white">[content]</text>
    </svg>
EOT;
        $template = str_replace('[content]',$content,$template);
        return response($template,200,[
            'Content-Type' => 'image/svg+xml',
            'Vary' => 'Accept-Encoding',
        ]);
    }
}