require.config({
    baseUrl: '/static/',
    paths: {
        'test': 'lib/test',
        'jquery': 'lib/jquery-3.6.0.min',
        'popper': 'js/popper-v1.16.0.min',
        'bootstrap': 'lib/bootstrap-4.3.1-dist/js/bootstrap.min',
        'bootstrap-table': 'lib/bootstrap-table-master/dist/bootstrap-table.min',
        'bootstrap-table-zh-CN': 'lib/bootstrap-table-master/dist/locale/bootstrap-table-zh-CN.min',
        'layui': 'lib/layui-v2.5.6/layui/layui',
        'form-validate': 'lib/form-validate/index',
        'sweetalert': 'lib/sweetalert/dist/sweetalert.min',
        'jquery-validator': 'lib/nice-validator/dist/jquery.validator.min.js?css',
        'bsTable': 'js/utils/bsTable',
        'garForm': 'js/utils/garForm',
        'helper': 'js/utils/helper',
        'validator': 'lib/nice-validator/dist/local/zh-CN',
        'viewer': 'lib/viewerjs/dist/viewer.min',
        'require-css': 'lib/require-css/css.min',
    },
    shim: {
        'bootstrap': {
            deps: [
                'jquery',
                'popper',
                'style!lib/bootstrap-4.3.1-dist/css/bootstrap.min',
                'style!css/layout',
            ],
        },
        'bootstrap-table-zh-CN': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.locales[\'zh-CN\']'
        },
        'bootstrap-table': {
            deps: ['bootstrap','style!lib/fontawesome-free-5.15.4-web/css/all.min','style!lib/bootstrap-table-master/dist/bootstrap-table.min'],
            exports: '$.fn.bootstrapTable'
        },
        'layui': {
            deps: ['style!lib/layui-v2.5.6/layui/css/layui'],
            exports: 'layui'
        },
        'jquery-validator': {
            deps: ['jquery']
        },
        'validator': {
            deps: ['jquery-validator']
        },
        'viewer':{
            deps: ['jquery','style!lib/viewerjs/dist/viewer.min']
        },
    },
    map: {
        '*': {
            'popper.js': 'popper',
            'style': 'require-css'
        }
    }
})
var garBackend = {}
var garLang;

// 完成一系列初始化的过程
require(['jquery','layui','helper'],function ($, layui,helper) {
    // 定义一些全局变量
    var ele = '#___main-backend___';
    garBackend = $(ele).data('runtimeInfo')
    garLang = function (name) {
        if (!name) {
            return ''
        }
        var langList = $(ele).data('langList')
        var nameList = name.split('.')
        const nameLen = nameList.length
        for (var i = 0; i < nameLen; i++) {
            langList = langList[nameList[i]]
        }
        return langList
    }

    // 渲染侧边栏
    layui.use(['element','layer'], function(){
        var element = layui.element;
        var _data = garBackend.asideMenuList;
        renderNavTree(_data,$('.layui-nav-tree'));
        var c = $('.layui-nav-itemed');
        var parentNode = c.parent('dl').parent('dd');
        if(parentNode.length){
            parentNode.addClass('layui-nav-itemed');
        }
        element.on('nav(user)',function (elem) {
            if ($(elem).attr('id') === 'logout') {
                layer.confirm('你确定要注销登录么？',null,function (index) {
                    layer.close(index);
                    location.href = "{:url('/Login/logout')}?t=" + (new Date()).getTime();
                });
            }
        })
        $(document).on('click','.sideNav',function (e) {
            e.stopPropagation();
            if (!$(e.target).children('.layui-nav-more').length){
                $(e.target).data('href') && (location.href = $(e.target).data('href'));
            }else {
                if ($(e.target).parent('dd.layui-nav-item').hasClass('layui-nav-itemed')){
                    $(e.target).parent('dd.layui-nav-item').removeClass('layui-nav-itemed');
                }else {
                    $(e.target).parent('dd.layui-nav-item').addClass('layui-nav-itemed');
                }
            }

        });


        function renderNavTree(data, parent) {
            var len = data.length
            for (let i = 0; i < len; i++) {
                var className = "layui-nav-item sideNav";
                var dd = $('<dd class="'+ className +'"></dd>');
                var menuUrl = data[i].name;
                if (data[i].active) {
                    dd.addClass('layui-nav-itemed');
                }

                //如果检测有子节点，则进行遍历
                if (data[i].children && data[i].children.length > 0) {

                    $(dd).append('<a href="javascript: void(0);" data-href="'+ menuUrl +'"><i class="fa fa-bars"></i>&nbsp;&nbsp;'+data[i].title+'<i class="layui-icon layui-icon-down layui-nav-more"></i></a>');
                    $(dd).append('<dl class="layui-nav-child sideNav"></dl>').appendTo(parent);

                    renderNavTree(data[i].children, $(dd).children().eq(1));

                } else {
                    $(dd).append('<a href="javascript: void(0);" data-href="'+ menuUrl +'"><i class="fa fa-bars"></i>&nbsp;&nbsp;'+data[i].title+'</a>').appendTo(parent);
                }
            }
        }

        $('#icon_stretch').on('click',function () {
            $('.layui-side.layui-bg-black').toggle('slow',function () {
                if ($('.layui-side.layui-bg-black').css('display') === 'none'){
                    $('.layui-body').css('left','0px');
                    $('.layui-layout-admin .layui-footer').css('left','0px');
                } else {
                    $('.layui-body').css('left','200px');
                    $('.layui-layout-admin .layui-footer').css('left','200px');
                }
            });

        });
        if (helper.isMobile()){
            $('.layui-side.layui-bg-black').hide('slow',function () {
                $('.layui-body').css('left','0px');
            });
        }

    });
})
