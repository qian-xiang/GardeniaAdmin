<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Filesystem;
use think\Model;

/**
 * @mixin \think\Model
 */
class Upload extends Model
{
    public function getUrlAttr($value) {
        return Filesystem::disk('public')->url($value);
    }
}
