define(['jquery'],function ($) {
    return {
        event: {
            bind: function () {

            },
            image: {
                bind: function (element) {
                    $(element).off('click',element).on('click',element,function () {
                        // var className = '.gar-preview-image'

                    })
                },
            }


        }
    }
})