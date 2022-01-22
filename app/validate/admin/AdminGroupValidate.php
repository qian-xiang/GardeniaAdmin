<?php
declare (strict_types = 1);

namespace app\validate\admin;

use constant\AppConstant;
use think\Validate;

class AdminGroupValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];
    public function setAddAdminGroupRule() {
        $this->rule = [
            'type|管理员类型' => 'require|in:'.join(',',array_keys(AppConstant::getAdminTypeList())),
            'status|状态' => 'require|in:'.join(',',array_keys(AppConstant::getStatusList())),
            'title|分组标题' => 'require|max:255',
            'rules|规则' => 'require|array',
        ];
        return $this->rule;
    }
}
