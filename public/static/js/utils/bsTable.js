define(['helper'],function (Helper) {
    return {
        formatter: {
            status: {
                simple: function (value, row, index) {
                    const list = [
                        'dark',
                        'success',
                    ];
                    const listText = {
                        'dark': '禁用',
                        'success': '正常',
                    }
                    if (!list[value]) {
                        throw 'bsTable.formatter.status方法仅支持以下类型type：' + list.join(',')
                    }
                    return '<a href="#" class="badge badge-'+ list[value] +'">'+ listText[list[value]] +'</a>'
                },
                normal: function (value, title) {
                    const list = [
                        'primary',
                        'secondary',
                        'success',
                        'danger',
                        'warning',
                        'info',
                        'light',
                        'dark',
                    ];
                    if (list.indexOf(value) === -1) {
                        throw 'bsTable.formatter.status方法仅支持以下类型type：' + list.join(',')
                    }
                    return '<a href="#" class="badge badge-'+ type +'">'+ title +'</a>'
                },
            },
            image: function (value, row, index, className, callback) {
                return '<div class="gar-preview-image-td-parent"><img class="gar-preview-image-td" src="'+value+'" alt=""></div>';
            },
            garOperate: function (btns,extra) {
                if (!btns) {
                    return '';
                }
                var btnList = [
                    'del',
                    'edit',
                ];
                var arr = btns.split(',')
                var len = arr.length
                // 按钮类型合法 开始构建html模板
                var html = '';
                var tmp;
                for (var i = 0; i < len; i++) {
                    if (btnList.indexOf(arr[i]) === -1) {
                        throw 'garOperate方法仅支持：' + btnList.join(',')
                    }
                    tmp = '';
                    switch (arr[i]) {
                        case 'add':
                            tmp = '<a class="btn btn-sm btn-primary btn-operate-'+arr[i]+'" href="/'+ garBackend.page.app +'/'+ garBackend.page.controller +'/add">'+
                        '<i class="fa fa-plus-square"></i> '+garLang('btn-operate-'+arr[i])+'</a> '
                            break;
                        case 'edit':
                            tmp = '<a class="btn btn-sm btn-success btn-operate-'+arr[i]+'" href="/'+ garBackend.page.app +'/'+ garBackend.page.controller +'/edit/id/'+ extra.id +'">'+
                                '<i class="fa fa-edit"></i> '+garLang('btn-operate-'+arr[i])+'</a> '
                            break;
                        case 'del':
                            tmp = '<a class="btn btn-sm btn-danger btn-operate-'+arr[i]+'" href="javascript: void(0)">'+
                        '<i class="fa fa-trash"></i> '+garLang('btn-operate-'+arr[i])+' </a> '
                            break;
                        default:
                            break;
                    }
                    html += tmp;
                }

                return html;
            },
            dateTime: function (value,row,index) {
                if (!value) {
                    return '';
                }
                var d = new Date(value)
                var month = Helper.numberToAddZero(d.getMonth() + 1)
                var date = Helper.numberToAddZero(d.getDate())
                var seconds = Helper.numberToAddZero(d.getSeconds())
                var minutes = Helper.numberToAddZero(d.getMinutes())
                var hours = Helper.numberToAddZero(d.getHours())
                return d.getFullYear() + '-' + month + '-' + date + ' '+ hours + ':' + minutes + ':' + seconds
            }
        },
        event: {
            bind: function (obj) {
                ($.type(obj.image)  === 'array' && obj.image.length) && this.image.bind(obj.image)
            },
            image: {
                bind: function (element, className, callback) {
                    element = element || '.gar-preview-image-td'
                    $(document).off('click',element).on('click',element,function () {
                        className = className || 'gar-preview-image-modal'
                        var _callback = callback || function () {
                            var targetDom = '.'+ className +' > .swal-icon'
                            $(targetDom).css('margin',0)
                            $('.swal-modal.'+className).css('width','fit-content')
                        }
                        if (!$(this).data('status')) {
                            swal({
                                icon: $(element).attr('src'),
                                closeOnClickOutside: true,
                                buttons: false,
                                className: className
                            })
                            $(this).data('status',1)
                            _callback()
                        } else {
                            swal.close()
                            $(this).data('status',0)
                        }
                    })
                }
            },

        },
    }
})