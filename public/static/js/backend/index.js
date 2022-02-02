define(['jquery','flatpickr','bootstrap-select-locale-zh','zeroclipboard','ueditor-locale-zh'],function ($, flatpickr,zh,zeroclipboard,ueditor) {
    return {
        index: function () {
            $('.flatpickr-input').flatpickr()
            $('.selectpicker').selectpicker()
            window.ZeroClipboard = zeroclipboard
            UE.getEditor('rich-text')
        }
    }
})