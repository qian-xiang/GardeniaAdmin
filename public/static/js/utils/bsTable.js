define(function () {
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
            }
        }
    }
})