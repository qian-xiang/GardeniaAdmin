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
        'bsTable': 'js/utils/bsTable',
    },
    shim: {
        'bootstrap': {
            deps: ['jquery','popper'],
        },
        'bootstrap-table-zh-CN': {
            deps: ['bootstrap-table'],
            exports: '$.fn.bootstrapTable.locales[\'zh-CN\']'
        },
        'bootstrap-table': {
            deps: ['bootstrap'],
            exports: '$.fn.bootstrapTable'
        },
        'layui': {
            exports: 'layui'
        },
        // 'validator': {
        //     exports: 'validator'
        // }
    },
    map: {
        '*': {
            'popper.js': 'popper',
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
