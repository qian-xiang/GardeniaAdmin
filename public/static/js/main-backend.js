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
require(['jquery'],function ($) {
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
})
