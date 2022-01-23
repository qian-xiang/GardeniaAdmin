<?php
declare (strict_types = 1);

namespace app\validate\admin;

use constant\AppConstant;
use think\Validate;

class AdminValidate extends Validate
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

    public function setAddAdminRule() {
        $this->rule = [
            'username|用户名' => 'require|min:2|max:15',
            'password|密码' => 'require|min:6|max:32',
            'confirm_password|确认密码' => 'require|confirm:password',
            'group_id|管理员组' => 'require|integer|>:0',
            'status|状态' => 'require|in:'.join(',',array_keys(AppConstant::getStatusList())),
        ];
        return $this->rule;
    }
    public function setEditAdminRule() {
        $this->rule = [
            'username|用户名' => 'require|min:2|max:15',
            'password|密码' => 'min:6|max:32',
            'confirm_password|确认密码' => 'confirm:password',
            'group_id|管理员组' => 'require|integer|>:0',
            'status|状态' => 'require|in:'.join(',',array_keys(AppConstant::getStatusList())),
            'id' => 'require|integer|>:0'
        ];
        return $this->rule;
    }
}
