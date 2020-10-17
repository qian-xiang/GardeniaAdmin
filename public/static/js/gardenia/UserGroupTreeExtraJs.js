form.on('submit(gardeniaForm)',function(data){
    function getIdArray(data) {
        let len = data.length;
        for (let i = 0; i < len; i++) {
            if (data[i].id){
                idArr.push(data[i].id);
            }
            if (data[i].children && data[i].children.length){
                getIdArray(data[i].children);
            }
        }
    }
    let formData = data.field;

    let treeData = tree_rules.getChecked('rules');
    let idArr = [];
    getIdArray(treeData);
    idArr = idArr.join(',');

    let newFormData = {
        rules: idArr,
        title: formData['title'],
        type: formData['type'],
        status: formData['status']
    };
    formData = null;
    $.ajax({
        url: '{:url("/".request()->controller()."/".request()->action())}',
        method: 'POST',
        async: true,
        data: newFormData,
        dataType: 'json',
        success: function (res) {
            console.log(res);
            if (res.code !== 0) {
                return layer.msg(res.msg);
            }
            layer.msg(res.msg);
            if (res.redirectUrl){
                location.href = res.redirectUrl;
            }
        },
        error: function (e) {
            console.log(e);
            return layer.msg(e.msg);
        }
    });
})

