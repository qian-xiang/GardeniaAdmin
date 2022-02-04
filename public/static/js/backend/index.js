define(['jquery','flatpickr','bootstrap-select-locale-zh','zeroclipboard','ueditor-locale-zh','fileinput-locale-zh',
    'sweetalert2'],function ($, flatpickr,zh,zeroclipboard,ueditorZh,fileinputLocaleZh,sweetalert) {
    return {
        index: function () {
            sweetalert.fire('Any fool can use a computer')
            $('.flatpickr-input').flatpickr()
            $('.selectpicker').selectpicker()
            $('#upload').fileinput()
            //删除文件
            $(document).off('click','.gar-upload-preview-image-delete').on('click','.gar-upload-preview-image-delete',function () {
                const replacedStr = $(this).data('url') + ','
                const targetSelector = 'input[name="idcard"]'
                const targetValue = $().val()
                $(targetSelector).val(targetValue.indexOf(replacedStr) > -1 ? targetValue.replace(replacedStr,'') : $(this).data('url'))
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
                            return swal({
                                title: '提示',
                                text: res.msg,
                                icon: 'error',
                                buttons: false,
                                timer: 2000,
                            })
                        }
                        const fileLen = res.data.length
                        var filePreviewChildren = ''
                        for (var i = 0; i < fileLen; i++) {
                            filePreviewChildren += `<div class="image-card"><img src="${res.data[i].url}" alt="${res.data[i].filename}">
                                    <i class="far fa-trash-alt gar-upload-preview-image-delete" data-url="${res.data[i].url}"></i>
                                </div>`
                        }
                        $(that).parents('.input-group').siblings('.gar-upload-preview-image').empty().append(filePreviewChildren)
                    },
                    error: function (e) {
                        console.log('e',e)
                    },
                    compete: function (res) {
                        console.log('res',res)
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
        }
    }
})