define(['jquery','flatpickr','bootstrap-select-locale-zh'],function ($, flatpickr,zh) {
    return {
        index: function () {
            $('.flatpickr-input').flatpickr()
            $('.selectpicker').selectpicker()
        }
    }
})