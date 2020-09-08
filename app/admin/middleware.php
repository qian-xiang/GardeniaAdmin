<?php
// 后台中间件定义文件
return [
    // Session初始化
     \think\middleware\SessionInit::class,
    //校验权限中间件
    \app\admin\middleware\CheckLogin::class,
    \app\admin\middleware\CheckAccess::class,
];
