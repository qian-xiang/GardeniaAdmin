define(['jquery','bootstrap-table-zh-CN','sweetalert','bsTable','validator','garForm'],
    function ($,bootstrapTable,sweetalert,BsTable,Validator,GarForm) {
var page = {
    index: function () {
        $(document).ready(function () {
            const table = '#table';
            var tableOptions = {
                url: 'index',
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
                sortName: 'weigh',
                sortOrder: 'desc',
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
                        formatter: function (value) {
                            return value ? '其它' : '菜单';
                        }
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
                        title: '层级',
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
                        formatter: BsTable.formatter.dateTime,
                    },
                    {
                        field: 'operate',
                        title: '操作',
                        formatter: function (value, row, index) {
                            return BsTable.formatter.garOperate('del,edit',{id: row.id})
                        },
                    }
                ]
            };
            obj = Object.assign(obj,tableOptions)

            $(table).bootstrapTable('destroy').bootstrapTable(obj)
            const eleDel = '.btn-operate-del'
            $(document).off('click',eleDel).on('click',eleDel,function () {
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
                            url: 'delete',
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
            // BsTable.event.image.bind()
        })
    },
    add: function () {
        this.api.addEdit('#form-add')
    },
    edit: function () {
        this.api.addEdit('#form-edit')
    },
    api: {
        addEdit: function (ele) {
            ele = ele || '#form-add'
            $(ele).validator({
                // 验证通过
                valid: function(form) {
                    var arr = $(form).serializeArray()
                    var formData = {}
                    $.each(arr,function () {
                        formData[this.name] = this.value
                    })
                    $(ele).find('button[type="submit"]').attr('disabled',true);
                    swal({
                        text: '正在执行中...',
                        buttons: false,
                    })
                    $.ajax({
                        url: '',
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (res) {
                            $(ele).find('button[type="submit"]').attr('disabled',false);
                            if (res.msg) {
                                swal({
                                    text: res.msg,
                                    buttons: false,
                                    timer: 2000,
                                    icon: res.code === garBackend.apiCode.success ? 'success' : 'error'
                                })
                                console.log(res.redirectUrl)
                                if (res.code === garBackend.apiCode.success) {
                                    setTimeout(function () {
                                        if (res.redirectUrl) {
                                            location.href =  res.redirectUrl
                                        }
                                        history.back()
                                    },2000)
                                } else if (res.redirectUrl) {
                                    setTimeout(function () {
                                        location.href = res.redirectUrl
                                    },2000)
                                }

                            }
                        },
                        error: function (e) {
                            console.log('出错啦',e)
                            $(ele).find('button[type="submit"]').attr('disabled',false);
                            swal({
                                title: '提示',
                                text: '出错啦，请稍候重试',
                                icon: 'error',
                                buttons: false,
                                timer: 2000,
                            })
                        },
                    })
                }
            });

        }
    }
}
return page
})