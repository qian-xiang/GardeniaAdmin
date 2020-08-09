<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
//auth类的设置
return [
    'AUTH_CONFIG' => array(
        'AUTH_ON' => true, //认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
        'AUTH_GROUP' => env('database.prefix').'auth_group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => env('database.prefix').'auth_group_access', //用户组明细表
        'AUTH_RULE' => env('database.prefix').'auth_rule', //权限规则表
        'AUTH_USER' => env('database.prefix').'user'//用户信息表
    ),
    //校验访问权限白名单设置
    'white_list' => [
        //登录
      'login/login'
    ],
];