define(['jquery','flatpickr','bootstrap-select-locale-zh','zeroclipboard','ueditor-locale-zh','fileinput-locale-zh'],function ($, flatpickr,zh,zeroclipboard,ueditorZh,fileinputLocaleZh) {
    return {
        index: function () {
            $('.flatpickr-input').flatpickr()
            $('.selectpicker').selectpicker()
            $('#upload').fileinput()
            $(document).off('change','#gar-upload-file').on('change','#gar-upload-file',function () {
                console.log('files',$(this)[0].files)
                console.log('sfsfsf',$(this).val())
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