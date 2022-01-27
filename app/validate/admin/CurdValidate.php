<?php


namespace app\validate\admin;


use think\Validate;

class CurdValidate extends Validate
{
    protected $rule = [];
    public function setCurdParamRule() {
        $this->rule = [
            'operate|操作名称' => 'require|in:c,d',
        ];
        return $this->rule;
    }
    public function setCurdOptionRule() {
        $this->rule = [
            'table|表名称' => 'require|chsDash',
            'controller|控制器名称' => 'require|alphaNum',
            'model|模型名称' => 'require|alphaNum',
            'field|显示字段' => 'require',
        ];
        return $this->rule;
    }
}