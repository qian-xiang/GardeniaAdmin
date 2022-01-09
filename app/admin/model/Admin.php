<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class Admin extends Model
{
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
