<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AdminGroupAccess extends Model
{
    //
    public function admin() {
        return $this->hasOne(Admin::class,'id','admin_id');
    }
    public function adminGroup() {
        return $this->hasOne(AdminGroup::class,'id','group_id');
    }
}
