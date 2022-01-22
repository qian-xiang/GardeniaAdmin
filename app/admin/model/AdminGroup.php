<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AdminGroup extends Model
{
    public function admin() {
        return $this->hasOne(Admin::class,'admin_id','id');
    }

}
