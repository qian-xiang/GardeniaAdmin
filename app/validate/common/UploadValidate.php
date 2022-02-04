<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the Apache2.0 license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\validate\common;


use think\File;
use think\Validate;

class UploadValidate extends Validate
{
    protected $rule = [
        'tag|文件标签' => 'alphaDash|max:255',
        'file|文件' => 'require|validateFile',
    ];
    protected $message = [

    ];
    public function __construct()
    {
        $this->extend('validateFile',\Closure::fromCallable([$this,'validateFile']),'文件格式不符');
        parent::__construct();
    }

    protected function validateFile($value, $data) {
        return $value instanceof File || is_array($value);
    }
}