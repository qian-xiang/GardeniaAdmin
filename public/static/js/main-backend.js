var garLang;
// 定义一些全局变量
const requireJsId = '___require-js___'
const garBackend = JSON.parse(document.getElementById(requireJsId).getAttribute('data-runtime-info'))
var paths = {
    'jquery': 'js/jquery-3.6.0.min',
    'popper': 'js/popper-v1.16.0.min',
    'bootstrap': 'lib/bootstrap-4.3.1-dist/js/bootstrap.min',
    'bootstrap-bundle': 'lib/bootstrap-4.3.1-dist/js/bootstrap.bundle.min',
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
    'vakata-jstree': 'lib/vakata-jstree/dist/jstree.min',
    'flatpickr': 'lib/flatpickr/dist/flatpickr.min',
    'flatpickr-zh': 'lib/flatpickr/dist/l10n/zh',
    'bootstrap-select': 'lib/bootstrap-select/dist/js/bootstrap-select.min',
    'bootstrap-select-locale-zh': 'lib/bootstrap-select/dist/js/i18n/defaults-zh_CN.min',
    'ueditor': 'lib/ueditor1.4.3.3/ueditor.all.min',
    'ueditor-locale-zh': 'lib/ueditor1.4.3.3/lang/zh-cn/zh-cn',
    'ueditor-config': 'lib/ueditor1.4.3.3/ueditor.config',
    'zeroclipboard': 'lib/ueditor1.4.3.3/third-party/zeroclipboard/zeroclipboard.min',
    'fileinput-locale-zh': 'lib/bootstrap-fileinput/js/locales/zh',
    'fileinput': 'lib/bootstrap-fileinput/js/fileinput.min',
    'piexif': 'lib/bootstrap-fileinput/js/plugins/piexif.min',
    'sortable': 'lib/bootstrap-fileinput/js/plugins/sortable.min',
    'fileinput-theme': 'lib/bootstrap-fileinput/themes/fa/theme.min',
    'sweetalert2': 'lib/sweetalert2/dist/sweetalert2.all.min',
}
var initLoad = [
    'jquery',
    'helper',
]
var initLoadParam = [
    '$',
    'helper',
]
if (garBackend.page.controllerJsExist) {
    paths[garBackend.page.controllerJsHump] = garBackend.page.controllerJs
    initLoad.push(garBackend.page.controllerJsHump)
    initLoadParam.push(garBackend.page.controllerJsHump)
}

require.config({
    baseUrl: '/static/',
    paths: paths,
    shim: {
        'bootstrap': {
            deps: [
                'jquery',
                'popper',
                // 'style!lib/bootstrap-4.3.1-dist/css/bootstrap.min',
                // 'style!css/layout',
            ],
        },
        'bootstrap-table-zh-CN': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.locales[\'zh-CN\']'
        },
        'bootstrap-table': {
            deps: ['bootstrap','style!lib/bootstrap-table-master/dist/bootstrap-table.min'],
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
        'vakata-jstree': {
            deps: ['jquery','style!lib/vakata-jstree/dist/themes/default/style.min'],
        },
        'garForm' : {
            deps: ['style!lib/../css/gar-form']
        },
        'flatpickr': {
            deps: ['flatpickr-zh','style!lib/flatpickr/dist/flatpickr.min']
        },
        'bootstrap-select': {
            deps: ['bootstrap','style!lib/bootstrap-select/dist/css/bootstrap-select.min']
        },
        'bootstrap-select-locale-zh': {
            deps: ['bootstrap-select']
        },
        'ueditor-locale-zh': {
            deps: ['ueditor-config','ueditor']
        },
        'fileinput-locale-zh': {
            deps: ['jquery','piexif','sortable','bootstrap-bundle','fileinput','fileinput-theme','style!lib/bootstrap-fileinput/css/fileinput.min']
        },
        'sweetalert2': {
            deps: ['style!lib/sweetalert2/dist/sweetalert2.min']
        }
    },
    map: {
        '*': {
            'popper.js': 'popper',
            'style': 'require-css'
        }
    }
})

require(initLoad,function (...initLoadParam) {
    const $ = arguments[0]
    const helper = arguments[1]
    const pageJs = arguments[2]

    garLang = function (name) {
        if (!name) {
            return ''
        }
        const ele = '#' + requireJsId;
        var langList = $(ele).data('langList')
        var nameList = name.split('.')
        const nameLen = nameList.length
        for (var i = 0; i < nameLen; i++) {
            langList = langList[nameList[i]]
        }
        return langList
    }

    // 渲染侧边栏
    var _data = garBackend.asideMenuList;

    renderNavTree(_data,$('dl.gardenia-layout-sidebar'));
    // var c = $('.layui-nav-itemed');
    // var parentNode = c.parent('dl').parent('dd');
    // if(parentNode.length){
    //     parentNode.addClass('layui-nav-itemed');
    // }
    // element.on('nav(user)',function (elem) {
    //     if ($(elem).attr('id') === 'logout') {
    //         layer.confirm('你确定要注销登录么？',null,function (index) {
    //             layer.close(index);
    //             location.href = "{:url('/Login/logout')}?t=" + (new Date()).getTime();
    //         });
    //     }
    // })
    $(document).on('click','a[data-href*="/"]',function (e) {
        e.stopPropagation();
        $(this).children('i.fas').each(function () {
            if ($(this).hasClass('fa-caret-up')) {
                $(this).removeClass('fa-caret-up')
                $(this).addClass('fa-caret-down')
            } else {
                $(this).removeClass('fa-caret-down')
                $(this).addClass('fa-caret-up')
            }
        })
        $(this).siblings('dl').each(function () {
            if ($(this).hasClass('d-none')) {
                $(this).removeClass('d-none')
            } else {
                $(this).addClass('d-none')
            }
        })

    });


    function renderNavTree(data, parent) {
        var len = data.length
        for (let i = 0; i < len; i++) {
            var dd = $('<dd></dd>');
            var menuUrl = data[i].name;

            //如果检测有子节点，则进行遍历
            if (data[i].children && data[i].children.length > 0) {

                $(dd).append('<a class="bleak" href="javascript: void(0);" data-href="'+ menuUrl +'"><i class="fa fa-bars"></i>&emsp;'+data[i].title+'&nbsp;<i class="fas fa-caret-down"></i></a>');
                $(dd).append('<dl class="gardenia-layout-sidebar-item-list d-none"></dl>').appendTo(parent);
                renderNavTree(data[i].children, $(dd).children().eq(1));

            } else {
                $(dd).append('<a class="bleak" href="'+ menuUrl +'"><i class="fa fa-bars"></i>&emsp;'+data[i].title+'</a>').appendTo(parent);
            }
            if (data[i].active) {
                dd.parents().removeClass('d-none');
                dd.parents('dl').siblings('a').removeClass('bleak');
                dd.parents('dl').siblings('a').addClass('high-light');
                dd.parents('dl').siblings('a').children('i.fa-caret-down').addClass('fa-caret-up');
                dd.parents('dl').siblings('a').children('i.fa-caret-down').removeClass('fa-caret-down');
                dd.children('a').removeClass('bleak');
                dd.children('a').addClass('high-light');
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
            $('.layui-body').css('left','0');
        });
    }
    if (garBackend.page.controllerJsExist) {
        //加载页面js
        pageJs[garBackend.page.action]()
    }
})
