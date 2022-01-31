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
            'app|应用名称' => 'alphaNum',
            'table|表名称' => 'require|chsDash',
            'controller|控制器名称' => 'alphaNum',
            'model|模型名称' => 'alphaNum',
            'field|显示字段' => 'min:1',
        ];
        return $this->rule;
    }
}