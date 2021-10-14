<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AuthGroupAccess extends Model
{
    //
    public function admin() {
        return $this->hasOne(Admin::class,'id','admin_id');
    }
    public function authGroup() {
        return $this->hasOne(AuthGroup::class,'id','group_id');
    }
}
