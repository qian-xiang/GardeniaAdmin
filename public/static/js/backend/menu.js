require(['jquery','bootstrap-table-zh-CN','sweetalert','bsTable'],function ($,bootstrapTable,sweetalert,BsTable) {
var page = {
    index: function () {
        console.log(garLang('btn-operate-add'))
        $(document).ready(function () {
            const table = '#table';
            var tableOptions = {
                url: '/admin/menu/index',
                pagination: true,
                sidePagination: "server",
                pageList: [10,20,'all'],
                pageSize: 10,
                showPaginationswitch: true,
                height: 500,
                toolbar: '#toolbar',
                search: true,
                showRefresh: true,
                showToggle: true,
                showFullscreen: true,
                showColumns: true,
                showColumnsToggleAll: true,
                showExport: true,
                clickToSelect: true,
                minimumCountColumns: true,
                // showFooter: true,
                idField: 'id',
            };
            var obj = {
                columns: [
                    {
                        field: 'checked',
                        checkbox: true,
                    },
                    {
                        field: 'id',
                        title: 'ID',
                    },
                    {
                        field: 'type',
                        title: '类型',
                    },
                    {
                        field: 'title',
                        title: '标题',
                    },
                    {
                        field: 'name',
                        title: '规则',
                    },
                    {
                        field: 'level',
                        title: '等级',
                    },
                    {
                        field: 'weigh',
                        title: '权重',
                    },
                    {
                        field: 'status',
                        title: '状态',
                        formatter: BsTable.formatter.status.simple
                    },
                    {
                        field: 'create_time',
                        title: '时间',
                    },
                    {
                        field: 'operate',
                        title: '操作',

                    }
                ]
            };
            obj = Object.assign(obj,tableOptions)

            $(table).bootstrapTable('destroy').bootstrapTable(obj)

            $('.btn-operate-del').click(function () {
                swal({
                    title: '提示',
                    text: '您确定要删除么？',
                    buttons: [
                        '确认',
                        '取消'
                    ],
                    // timer: 3000,
                    icon: 'warning',
                }).then(function (val) {
                    // 如果是点击确认按钮
                    if (!val) {
                        var rows = $(table).bootstrapTable('getSelections')
                        if (!rows.length) {
                            return swal({
                                title: '',
                                text: '请先选中表格数据',
                                button: false,
                                timer: 2000,
                                icon: 'warning',
                            })
                        }
                        rows = rows.map(function (currentValue) {
                            return currentValue.id;
                        })
                        rows = rows.join(',');

                        $.ajax({
                            url: '/admin/menu/del',
                            method: 'POST',
                            data: {
                                id: rows,
                            },
                            dataType: 'json',
                            success: function (res) {
                                console.log(res)
                                swal({
                                    title: '提示',
                                    text: res.msg,
                                    button: false,
                                    timer: 2000,
                                    icon: res.code === garBackend.apiCode.success ? 'success' : 'error',
                                })
                                if (res.redirectUrl) {
                                    return setTimeout(function () {
                                        location.href = res.redirectUrl
                                    },2000)
                                }
                                $(table).bootstrapTable('refresh')
                            },
                            error: function (e) {
                                console.log(e)
                            }
                        })
                    }
                })

            })
        })
    },
    add: function () {
        $('#form-add').submit(function () {
            var arr = $(this).serializeArray()
            var formData = {}
            $.each(arr,function () {
                formData[this.name] = this.value
            })
           $.ajax({
               url: garBackend.url,
               method: 'POST',
               data: formData,
               dataType: 'json',
               success: function (res) {
                   console.log(res)
                   if (res.msg) {
                       console.log(res.msg)
                       if (res.code === garBackend.apiCode.success) {
                           setTimeout(function () {
                               !res.data.redirectUrl && history.back()
                               location.href =  res.data.redirectUrl
                           },2000)
                       } else if (res.data.redirectUrl) {
                           setTimeout(function () {
                               location.href = res.data.redirectUrl
                           },2000)
                       }

                   }
               },
               error: function (e) {
                   console.log('出错啦',e)
               }
           })
        })
    },
    edit: function () {

    }
}
var action = $('#___controller-js___').data('action');
    page[action]();
})