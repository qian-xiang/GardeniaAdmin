define(['jquery','sweetalert2','bootstrap-table-zh-CN'], function ($,sweetalert,bootstrapTable) {
    var template = `<div class="input-group mb-3">
                        <input type="text" class="form-control" name="[field]" placeholder="请选择或上传文件">
                        <div class="input-group-append">
                            <div class="upload-btn-parent">
                                <button class="btn btn-primary" type="button">上传<input type="file" multiple id="[input-file-id]"></button>
                            </div>
                            <button class="btn btn-dark gardenia-upload-btn-choose" type="button">选择</button>
                        </div>
                    </div>
                    <div class="gardenia-upload-preview-image">
        
                    </div>`

    return {
        init: function (selector = '') {
            selector = selector || '.gardenia-upload'
            const previewContainerSelector = '.gardenia-upload-preview-image'
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

                var tag = $(containerContext).data('tag') ? $(containerContext).data('tag') : 'default'
                var uploadUrl = $(containerContext).data('upload-url') ? $(containerContext).data('upload-url') : '/common/Common/upload?tag=' + tag
                var uploadListUrl = $(containerContext).data('upload-list-url') ? $(containerContext).data('upload-list-url') : '/common/Common/uploadFileList'

                //将没初始化的元素初始化
                if (!$(this).children('.gardenia-upload-preview-image').length) {
                    $(this).append(_template)
                    $(document).off('input propertychange',targetSelector).on('input propertychange',targetSelector,function () {
                        const targetValue = $(this).val()
                        var urlList = targetValue.split(',')
                        const urlListLen = urlList.length
                        var imageCardText = ''
                        for (var i = 0; i < urlListLen; i++) {
                            imageCardText += context.buildImageCard(urlList[i])
                        }
                        $(this).parent().siblings(previewContainerSelector).empty().append(imageCardText)

                    })
                    //当用户上传文件时
                    $(document).off('change','#'+inputFileId).on('change','#'+inputFileId,function () {
                        var files = $(this)[0].files
                        var formData = new FormData()
                        const fileLen = files.length
                        for (var i = 0; i < fileLen; i++) {
                            formData.append('file['+ i +']',files[i])
                        }
                        var that = this
                        $.ajax({
                            url: uploadUrl,
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
                                    filePreviewChildren += context.buildImageCard(res.data[i].url)
                                    urlStr = urlStr ? (urlStr + ',' + res.data[i].url) : res.data[i].url
                                }
                                const tempArr = $(that).attr('id').split('-')
                                const field = tempArr[tempArr.length - 1]
                                var targetSelector = `input[name="${field}"]`

                                $(that).val('')
                                $(targetSelector).val(urlStr)
                                $(targetSelector).trigger('input')
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


                    //当用户点击选择按钮时
                    $(containerContext).find('.input-group.mb-3 > .input-group-append > .gardenia-upload-btn-choose').off('click').on('click',function () {
                        field = $(this).parents('.gardenia-upload').first().data('field')
                        var table = `#${field}-choose-table`
                        sweetalert.fire({
                            title: '文件列表',
                            // showConfirmButton: false,
                            confirmButtonText: '确定',
                            showCloseButton: true,
                            width: '95%',
                            html: `<table id="${field}-choose-table" class="gardenia-upload-choose-table"></table>`,
                        }).then(function (result) {
                            const tableSelector = $(sweetalert.getHtmlContainer()).find('.fixed-table-body > table.gardenia-upload-choose-table').attr('id')
                            const field = tableSelector.split('-')[0]
                            targetSelector = `input[name="${field}"]`
                            if (result.isConfirmed) {
                                const data = $(`#${tableSelector}`).bootstrapTable('getSelections')
                                const checkedLen = data.length
                                var imageCardText = ''
                                var urls = []
                                for (var i = 0; i < checkedLen; i++) {
                                    imageCardText += context.buildImageCard(data[i].url)
                                    urls.push(data[i].url)
                                }
                                //更新表单字段里的文件url
                                $(targetSelector).val(urls.join(','))
                                $(targetSelector).trigger('input')

                            }
                        })

                        var tableOptions = {
                            url: uploadListUrl,
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
                                        // var inputSelector = $(this).data('input-selector')
                                        // var containerSelector = $(this).data('container-selector')
                                        return `<button type="button" class="btn btn-dark upload-choose" data-index="${index}"
                                                data-input-selector='${targetSelector}'
                                                >选择</button>`
                                    },
                                }
                            ]
                        };
                        obj = Object.assign(obj, tableOptions)
                        $(table).bootstrapTable('destroy').bootstrapTable(obj)
                    })
                }
            })
            const rowChooseBtnSelector = 'button.upload-choose'
            //当用户点击表格上的选择按钮时
            $(document).off('click',rowChooseBtnSelector).on('click',rowChooseBtnSelector,function () {
                var data = $(this).parents('table.gardenia-upload-choose-table').first().bootstrapTable('getData',{
                    useCurrentPage: false
                })
                var index = $(this).data('index')
                var inputSelector = $(this).data('input-selector')
                var url = data[index].url
                $(inputSelector).val(url)
                $(inputSelector).trigger('input')
                sweetalert.close()
            })
            //删除文件
            var delEleSeletor = `${selector} .image-card-bottom`
            $(document).off('click',delEleSeletor).on('click',delEleSeletor,function () {
                const field = $(this).parents('.gardenia-upload').first().data('field')
                var targetSelector = `input[name="${field}"]`
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
                $(this).parents('.image-card').first().remove()
            })
        },
        buildImageCard: function (url = '') {
            var arr = url.split('/')
            const defaultValue = 'File'
            var end = arr[arr.length - 1]
            var suffix
            if (!end) {
                suffix = defaultValue
            } else if (end.indexOf('.') > -1) {
                var temp = end.split('.')
                suffix = temp[temp.length - 1] ? temp[temp.length - 1] : defaultValue
            } else {
                suffix = end
            }
            return `<div class="image-card">
                    <div class="gardenia-upload-title-above">
                        <img src="${url}" alt="${url}" data-suffix="${suffix}" onerror="this.src = '/common/Common/createPreviewImage?content='+ this.getAttribute('data-suffix')">
                    </div>
                    <div class="image-card-bottom" data-url="${url}">
                        <i class="far fa-trash-alt gardenia-upload-preview-image-delete"></i>
                    </div>
                </div>`
        }
    }
})