<?php


namespace app\garcms\validate\admin;


use think\Validate;

class ChannelValidate extends Validate
{
    protected $rule = [

    ];
    public function setAddChannelRule() {
        $this->rule = [
            'title|标题' => 'require|max:255',
            'name|名称' => 'require|max:255',
        ];
        return $this->rule;
    }
}