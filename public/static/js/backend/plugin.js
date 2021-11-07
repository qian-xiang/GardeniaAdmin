require(['jquery','bootstrap-table-zh-CN','sweetalert','bsTable','validator','garForm','layui'],
    function ($,bootstrapTable,sweetalert,BsTable,Validator,GarForm,layui) {
        var page = {
            index: function () {
                $(document).ready(function () {
                    const table = '#table';
                    var tableOptions = {
                        url: 'index',
                        // pagination: true,
                        // sidePagination: "server",
                        // pageList: [10,20,'all'],
                        // pageSize: 10,
                        // showPaginationswitch: true,
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
                        // idField: 'id',
                    };
                    var obj = {
                        columns: [
                            {
                                field: 'title',
                                title: '标题',
                            },
                            {
                                field: 'name',
                                title: '名称',
                            },
                            {
                                field: 'versionCode',
                                title: '版本号',
                            },
                            {
                                field: 'intro',
                                title: '介绍',
                            },

                            {
                                field: 'author',
                                title: '作者',
                            },
                            {
                                field: 'email',
                                title: '邮箱',
                            },
                            {
                                field: 'website',
                                title: '官网',
                                formatter: function (value) {
                                    return '<a href="'+ value +'">官网</a>';
                                },
                            },
                            {
                                field: 'operate',
                                title: '操作',
                                formatter: function (value, row, index) {
                                    row.status = Number(row.status);
                                    var statusText = row.status ? '卸载' : '安装';
                                    return '<a href="javascript: void(0)" class="btn btn-sm '+ (row.status ? 'btn-danger' : 'btn-success') + ' plugin-operate" data-row=\''+ JSON.stringify(row) +'\'>'+ statusText +'</a>'
                                },
                            }
                        ]
                    };
                    obj = Object.assign(obj,tableOptions)

                    $(table).bootstrapTable('destroy').bootstrapTable(obj)
                    $('#plugin-online').click(function () {
                        swal({
                            // title: '提示',
                            text: '该功能尚未开放，敬请期待',
                            timer: 2000,
                            buttons: false,
                            icon: 'info',
                        })
                    })
                    $(document).on('click','a.plugin-operate',function () {
                        var url
                        var typeText = $(this).text()
                        var row = $(this).data('row')
                        if ($(this).attr('class').indexOf('btn-danger') > -1) {
                            //点击卸载的时候
                            url = 'uninstall'
                        } else {
                            //点击安装的时候
                            url = 'install'
                        }
                        var that = this

                        swal({
                            title: '提示',
                            text: '您确定要'+ typeText +'么？',
                            buttons: [
                                '确认',
                                '取消'
                            ],
                            // timer: 3000,
                            icon: url === 'uninstall' ? 'warning' : 'info',
                        }).then(function (val) {
                            if (!val) {
                                swal({
                                    title: '提示',
                                    text: '正在执行...',
                                    buttons: false,
                                })
                                $.ajax({
                                    url,
                                    method: 'POST',
                                    data: {
                                        name: row.name,
                                    },
                                    dataType: 'json',
                                    success: function (res) {
                                        if (res.msg) {
                                            swal({
                                                title: '提示',
                                                text: res.msg,
                                                timer: 2000,
                                                buttons: false,
                                                icon: res.code === garBackend.apiCode.success ? 'success' : 'error'
                                            })
                                        }
                                        if (res.code === garBackend.apiCode.success) {
                                            if (url === 'uninstall') {
                                                $(that).text( '安装')
                                                $(that).removeClass( 'btn-danger')
                                                $(that).addClass( 'btn-success')
                                            } else {
                                                $(that).text( '卸载')
                                                $(that).removeClass( 'btn-success')
                                                $(that).addClass( 'btn-danger')
                                            }
                                        }
                                    },
                                    error: function (e) {
                                        console.log('出错了',e)
                                        swal({
                                            title: '错误提示',
                                            text: '发生了错误，请稍候重试',
                                            timer: 2000,
                                        })
                                    }
                                })
                            }
                        })
                    })
                    const eleDel = '.btn-operate-del'
                    $(document).on('click',eleDel,function () {
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
                    BsTable.event.image.bind()
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
                            $.ajax({
                                url: '',
                                method: 'POST',
                                data: formData,
                                dataType: 'json',
                                success: function (res) {
                                    if (res.msg) {
                                        swal({
                                            title: '提示',
                                            text: res.msg,
                                            timer: 2000,
                                        })
                                        console.log(res.redirectUrl)
                                        if (res.code === garBackend.apiCode.success) {
                                            setTimeout(function () {
                                                !res.redirectUrl && history.back()
                                                location.href =  res.redirectUrl
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
                                },
                                complete: function () {
                                    $(ele).find('button[type="submit"]').attr('disabled',true);
                                }
                            })
                        }
                    });

                }
            }
        }
        var action = $('#___controller-js___').data('action');
        page[action]();
    })