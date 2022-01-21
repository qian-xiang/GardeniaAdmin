<?php /*a:5:{s:66:"/var/www/html/GitHub/GardeniaAdmin/app/admin/view/Index/index.html";i:1626865018;s:83:"/var/www/html/GitHub/GardeniaAdmin/app/admin/view/../../common/core/tpl/layout.html";i:1642316845;s:83:"/var/www/html/GitHub/GardeniaAdmin/app/admin/view/../../common/core/tpl/header.html";i:1642316891;s:84:"/var/www/html/GitHub/GardeniaAdmin/app/admin/view/../../common/core/tpl/sidebar.html";i:1636131893;s:83:"/var/www/html/GitHub/GardeniaAdmin/app/admin/view/../../common/core/tpl/footer.html";i:1636124599;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo config('app.app_name'); ?></title>
    <script src="/static/js/require-v2.3.6.js"></script>
    <script src="/static/js/main-backend.js" data-lang-list='<?php echo json_encode($langList,JSON_UNESCAPED_UNICODE); ?>' data-runtime-info='<?php echo json_encode($runtimeInfo,JSON_UNESCAPED_UNICODE); ?>' id="___main-backend___"></script>
    <style>
        .layui-body {
            overflow-x: scroll;
            padding: 1rem;
        }
        div.layui-layer-btn a.layui-layer-btn0 {
            border-color: #1E9FFF;
            background-color: #1E9FFF;
            color: #fff;
        }
        .btn.btn-primary.btn-operate-add,.btn.btn-danger.btn-operate-del {
            color: white;
        }
        .layui-nav .layui-nav-item a {
            padding: 0px 10px;
        }
        .layui-layout-admin .layui-footer {
            display: flex;
            justify-content: center;
            align-items: center;
            left: 0px;
            flex-wrap: nowrap;
            overflow: hidden;
            white-space: nowrap;
        }
        @media all and (max-width: 415px){
            .layui-layout-admin .layui-logo {
                width: 120px;
            }
            .layui-layout-left {
                left: 120px;
            }
            .layui-nav {
                padding: 0px 10px;
            }

        }

    </style>
</head>
<body>

<div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <div class="layui-logo"><?php echo config('app.app_name'); ?></div>
        <!-- 头部区域（可配合layui已有的水平导航） -->

    </div>

    <div class="layui-side layui-bg-black">
    <div class="layui-side-scroll">
        <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
        <dl class="layui-nav layui-nav-tree"  lay-filter="sideNavClick">

        </dl>

    </div>
</div>

    <div class="layui-body">
        <!-- 内容主体区域 -->
        栀子浅香
        <?php 
            $controllerJs = './static/js/backend/'.request()->controller(true).'.js';
         if(file_exists($controllerJs)): ?>
            <script src="/<?php echo htmlentities($controllerJs); ?>" data-action="<?php echo request()->action(true); ?>" id="___controller-js___"></script>
        <?php endif; ?>
        <!--        <div style="padding: 15px;">内容主体区域</div>-->
    </div>

    <div class="layui-footer">
    <!-- 底部固定区域 -->
    © <?php echo config('app.app_name'); ?>
    <!--        © layui.com-->
</div>
</div>

</body>
</html>