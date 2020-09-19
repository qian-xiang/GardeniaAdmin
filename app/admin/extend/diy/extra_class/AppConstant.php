<?php
/**

 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin

 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.

 */
namespace app\admin\extend\diy\extra_class;


class AppConstant
{
    const TABLE_USER = 'user';
    //状态：正常（启用）
    const STATUS_FORMAL = 1;
    //状态：禁用
    const STATUS_FORBID = 0;

    const CODE_SUCCESS = 0;
    const CODE_ERROR = 1;

    //规则类型：菜单
    const RULE_TYPE_MENU = 0;
    //规则类型：其它
    const RULE_TYPE_OTHER = 1;

    //超级管理
    const GROUP_TYPE_SUPER_ADMIN = 0;
    //一般管理
    const GROUP_TYPE_ADMIN = 1;

    //用户状态：存在,未删除
    const USER_NO_DELETE = 0;
    //用户状态：已被删除
    const USER_DELETED = 1;

    //登录状态：正常
    const LOGIN_STATUS_NORMAL = 1;
    //登录状态：禁用
    const LOGIN_STATUS_FORBID = 0;

    //user表中的pid的值为0，即表示该用户是自己
    const USER_NO_PID = 0;

    //user表中的root_id的值为0，即表示该用户的根ID是自己
    const USER_NO_ROOT_ID = 0;

    public static function getStatusList() {
        return [self::STATUS_FORBID=> '禁用', self::STATUS_FORMAL=> '正常'];
    }
    public static function getStatusAttr($value) {
        $list = self::getStatusList();
        return $list[$value];
    }
    public static function getRuleTypeList() {
        return [self::RULE_TYPE_MENU=> '菜单', self::RULE_TYPE_OTHER=> '其它'];
    }
    public static function getRuleTypeAttr($value) {
        $list = self::getRuleTypeList();
        return $list[$value];
    }
    public static function timestampToMinute($value) {
        return date('Y-m-d H:i',$value);
    }
//    public function getGroupAdminTypeList() {
//
//    }
}