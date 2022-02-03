define(['jquery','flatpickr','bootstrap-select-locale-zh','zeroclipboard','ueditor-locale-zh'],function ($, flatpickr,zh,zeroclipboard,ueditorZh) {
    return {
        index: function () {
            $('.flatpickr-input').flatpickr()
            $('.selectpicker').selectpicker()
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