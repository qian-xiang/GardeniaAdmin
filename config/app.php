<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    //应用名称
    'app_name' => 'GardeniaAdmin',
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => env('APP_DEBUG',false),
    'app_trace' =>  true,
    //上传配置
    'upload' => [
        //使用的上传驱动
        'driver' => 'File',
        //单个文件大小限制
        'maxSize' => 10*1024*1024,
        //接受的文件格式
        'accept_ext' => 'jpg,jpeg,png,gif,bmp,mp4,mp3,zip,rar',
    ]
];
