define(['jquery','flatpickr','bootstrap-select-locale-zh','zeroclipboard','ueditor-locale-zh','fileinput-locale-zh',
    'sweetalert2','bootstrap-table-zh-CN','gardenia-upload'],function ($, flatpickr,zh,zeroclipboard,ueditorZh,fileinputLocaleZh,sweetalert,bootstrapTable,gardeniaUpload) {
    return {
        index: function () {
            const page = this
            // $('.flatpickr-input').flatpickr()
            // $('.selectpicker').selectpicker()
            // $('#upload').fileinput()
            gardeniaUpload.init()
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


            // try {
            //     console.log('ue',UE)
            //     UE.getEditor('rich-text').ready(function () {
            //         window.ZeroClipboard = zeroclipboard
            //     })
            // } catch (e) {
            //     location.reload()
            // }
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