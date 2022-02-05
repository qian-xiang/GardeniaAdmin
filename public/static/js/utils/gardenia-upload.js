define(['jquery','sweetalert2','bootstrap-table-zh-CN'], function ($,sweetalert,bootstrapTable) {
    // gar-upload-file
    var template = `<div class="input-group mb-3">
                <input type="text" class="form-control" name="[field]" placeholder="请选择或上传文件">
                <div class="input-group-append">
                    <div class="upload-btn-parent">
                        <button class="btn btn-primary" type="button">上传<input type="file" multiple id="[input-file-id]"></button>
                    </div>
                    <button class="btn btn-dark gar-upload-btn-choose" type="button">选择</button>
                </div>
            </div>
            <div class="gar-upload-preview-image">

            </div>`
    return {
        init: function (selector = '') {
            selector = selector || '.gardenia-upload'
            var field
            var _template
            $(selector).each(function () {
                field = $(this).data('field') ? $(this).data('field') : ''
                if (!field) {
                    throw new Error(`选择器：${selector}的元素未填写data-field属性`)
                }
                _template = template.replace('[field]',field)
                _template = _template.replace('[input-file-id]',`gardenia-upload-file-${field}`)
                //将没初始化的元素初始化
                if (!$(this).children('.gar-upload-preview-image').length) {
                    $(this).append(_template)
                }
            })

        }
    }
})