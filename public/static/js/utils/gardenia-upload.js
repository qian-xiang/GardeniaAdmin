define(['jquery','sweetalert2','bootstrap-table-zh-CN'], function ($,sweetalert,bootstrapTable) {
    // gar-upload-file
    var template = `<div class="input-group mb-3">
                        <input type="text" class="form-control" name="[field]" placeholder="请选择或上传文件">
                        <div class="input-group-append">
                            <div class="upload-btn-parent">
                                <button class="btn btn-primary" type="button">上传<input type="file" multiple id="[input-file-id]"></button>
                            </div>
                            <button class="btn btn-dark gardenia-upload-btn-choose" type="button">选择</button>
                        </div>
                    </div>
                    <div class="gar-upload-preview-image">
        
                    </div>`

    return {
        init: function (selector = '') {
            selector = selector || '.gardenia-upload'
            var field
            var _template
            var inputFileId
            var context = this
            $(selector).each(function () {
                field = $(this).data('field') ? $(this).data('field') : ''
                if (!field) {
                    throw new Error(`选择器：${selector}的元素未填写data-field属性`)
                }
                var containerContext = this
                var targetSelector = `input[name="${field}"]`
                inputFileId = `gardenia-upload-file-${field}`
                _template = template.replace('[field]',field)
                _template = _template.replace('[input-file-id]',inputFileId)
                //将没初始化的元素初始化
                if (!$(this).children('.gar-upload-preview-image').length) {
                    $(this).append(_template)
                    //当用户上传文件时
                    $(document).off('change',inputFileId).on('change',inputFileId,function () {
                        var files = $(this)[0].files
                        var tag = $(containerContext).data('tag') ? $(containerContext).data('tag') : 'default'
                        var url = $(containerContext).data('url') ? $(containerContext).data('url') : '/common/Common/upload?tag=' + tag
                        var formData = new FormData()
                        const fileLen = files.length
                        for (var i = 0; i < fileLen; i++) {
                            formData.append('file['+ i +']',files[i])
                        }
                        var that = this
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
                                    filePreviewChildren += context.buildImageCard(res.data[i].url,res.data[i].name,res.data[i].mime)
                                    urlStr = urlStr ? (urlStr + ',' + res.data[i].url) : res.data[i].url
                                }
                                $(that).parents('.input-group').siblings('.gar-upload-preview-image').empty().append(filePreviewChildren)
                                $(targetSelector).val(urlStr)
                                $(that).val('')
                            },
                            error: function (e) {
                                console.log('e',e)
                                sweetalert.fire({
                                    icon: 'error',
                                    title: '出错啦~',
                                    text: '上传时发生错误，请稍候重试',
                                })
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

                    //删除文件
                    var delEleSeletor = `${selector} .gar-upload-preview-image-delete`
                    $(document).off('click',delEleSeletor).on('click',delEleSeletor,function () {
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
                    var btnOutChooseSelector = `${selector} .gardenia-upload-btn-choose`
                    $(document).off('click',btnOutChooseSelector).on('click',btnOutChooseSelector,function () {
                        sweetalert.fire({
                            title: '文件列表',
                            // showConfirmButton: false,
                            confirmButtonText: '确定',
                            showCloseButton: true,
                            width: '95%',
                            html: `<table id="${field}-choose-table" style="width: 100%; max-height: 300px; height: 50vh;"></table>`,
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                const data = $(table).bootstrapTable('getSelections')
                                const checkedLen = data.length
                                var imageCardText = ''
                                var urls = []
                                for (var i = 0; i < checkedLen; i++) {
                                    imageCardText += page.buildImageCard(data[i].url,data[i].name,data[i].mime)
                                    urls.push(data[i].url)
                                }
                                //更新表单字段里的文件url
                                $(targetSelector).val(urls.join(','))
                                //更新预览图
                                $(targetSelector).children('.gar-upload-preview-image').empty().append(imageCardText)

                            }
                        })
                        const table = `#${field}-choose-table`

                        var tableOptions = {
                            url: '/common/Common/uploadFileList',
                            pagination: true,
                            sidePagination: "server",
                            pageList: [15, 30, 'all'],
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
                            const data = $(table).bootstrapTable('getData',{
                                useCurrentPage: false
                            })
                            const index = $(this).data('index')
                            const url = data[index].url
                            $(targetSelector).val(url)
                            $(targetSelector).parent().siblings('.gar-upload-preview-image').empty().append(page.buildImageCard(data[index].url,data[index].name,data[index].mime))
                            sweetalert.close()
                        })
                    })
                }
            })

        },
        buildImageCard: function (url = '', name = '', mime = 'image/png') {
            if (mime.indexOf('image') > -1) {
                return `<div class="image-card">
                    <div class="gar-upload-title-above">
                        <img title="${name}" src="${url}" alt="${name}">
                    </div>
                    <p>${name}</p>
                    <div class="image-card-bottom">
                        <i class="far fa-trash-alt gar-upload-preview-image-delete"></i>
                    </div>
                </div>`
            }
            return `<div class="image-card">
                    <div class="gar-upload-title-above">
                        <i class="far fa-file-alt"></i>
                    </div>
                    <p>${name}</p>
                    <div class="image-card-bottom">
                        <i class="far fa-trash-alt"></i>
                    </div>
                </div>`
        }
    }
})