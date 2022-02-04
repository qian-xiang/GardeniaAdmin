define(['jquery','flatpickr','bootstrap-select-locale-zh','zeroclipboard','ueditor-locale-zh','fileinput-locale-zh',
    'sweetalert2','bootstrap-table-zh-CN','bsTable'],function ($, flatpickr,zh,zeroclipboard,ueditorZh,fileinputLocaleZh,sweetalert,bootstrapTable,bsTable) {
    return {
        index: function () {
            const page = this
            $('.flatpickr-input').flatpickr()
            $('.selectpicker').selectpicker()
            $('#upload').fileinput()

            const targetSelector = 'input[name="idcard"]'
            //删除文件
            $(document).off('click','.gar-upload-preview-image-delete').on('click','.gar-upload-preview-image-delete',function () {
                const replacedStr = $(this).data('url')
                const targetValue = $(targetSelector).val()
                var urlList = targetValue.split(',')
                const urlListLen = urlList.length
                for (var i = 0; i < urlListLen; i++) {
                    if (urlList[i] === replacedStr) {
                        urlList.splice(i,1)
                    }
                }
                $(targetSelector).val(urlList.join(','))
                $(this).parent().remove()
            })
            //当用户点击选择按钮时
            $(document).off('click','.gar-upload-btn-choose').on('click','.gar-upload-btn-choose',function () {
                const field = 'idcard'
                sweetalert.fire({
                    title: '文件列表',
                    // showConfirmButton: false,
                    confirmButtonText: '确定',
                    showCloseButton: true,
                    width: '95%',
                    html: `<table id="${field}-choose-table" style="width: 100%; max-height: 300px; height: 50vh;"></table>`,
                })
                const table = `#${field}-choose-table`

                var tableOptions = {
                    url: '/common/Common/uploadFileList',
                    pagination: true,
                    sidePagination: "server",
                    pageList: [10, 20, 'all'],
                    pageSize: 10,
                    showPaginationswitch: true,
                    height: 500,
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
                    sortName: 'id',
                    sortOrder: 'desc',
                }
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
                            field: 'name',
                            title: '文件名',
                        },
                        {
                            field: 'url',
                            title: '文件地址',
                        },
                        {
                            field: 'create_time',
                            title: '时间',
                            formatter: bsTable.formatter.dateTime,
                        },
                        {
                            field: 'operate',
                            title: '操作',
                            formatter: function (value, row, index) {
                                return `<button type="button" class="btn btn-dark upload-choose" data-index="${index}">选择</button>`
                            },
                        }
                    ]
                };
                obj = Object.assign(obj, tableOptions)
                $(table).bootstrapTable('destroy').bootstrapTable(obj)

                $(document).off('click','button.upload-choose').on('click','button.upload-choose',function () {
                    const data = $(table).bootstrapTable('getData')
                    const index = $(this).data('index')
                    const url = data[index].url
                    $(targetSelector).val(url)
                    $(targetSelector).parent().siblings('.gar-upload-preview-image').empty().append(page.buildImageCard(data[index].url,data[index].name))
                    sweetalert.close()
                })
            })
            //当用户上传文件时
            $(document).off('change','#gar-upload-file').on('change','#gar-upload-file',function () {
                var files = $(this)[0].files
                var url = '/common/Common/upload?tag=default'
                var formData = new FormData()
                const fileLen = files.length
                for (var i = 0; i < fileLen; i++) {
                    formData.append('file['+ i +']',files[i])
                }
                const that = this
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (res) {
                        console.log('res',res)
                        if (res.code) {
                            return sweetalert.fire({
                                title: '提示',
                                text: res.msg,
                                icon: 'error',
                                timer: 2000,
                            })
                        }
                        const fileLen = res.data.length
                        var filePreviewChildren = ''
                        var urlStr = ''
                        for (var i = 0; i < fileLen; i++) {
                            filePreviewChildren += page.buildImageCard(res.data[i].url,res.data[i].name)
                            urlStr = urlStr ? (urlStr + ',' + res.data[i].url) : res.data[i].url
                        }
                        $(that).parents('.input-group').siblings('.gar-upload-preview-image').empty().append(filePreviewChildren)
                        $(targetSelector).val(urlStr)
                        $(that).val('')
                    },
                    error: function (e) {
                        console.log('e',e)
                    },
                    complete: function (res) {
                        sweetalert.close()
                    },
                    beforeSend: function () {
                        sweetalert.fire({
                            title: '正在上传...',
                            showConfirmButton: false,
                        })
                    }
                })
            })
            try {
                console.log('ue',UE)
                UE.getEditor('rich-text').ready(function () {
                    window.ZeroClipboard = zeroclipboard
                })
            } catch (e) {
                location.reload()
            }
        },
        buildImageCard: function (url,name) {
            return `<div class="image-card"><img src="${url}" alt="${name}">
                                    <i class="far fa-trash-alt gar-upload-preview-image-delete" data-url="${url}"></i>
                                </div>`
        }
    }
})