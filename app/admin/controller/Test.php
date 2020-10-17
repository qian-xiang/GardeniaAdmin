<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\GardeniaController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\Request;

class Test extends GardeniaController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = request();
        $gardeniaList = new GardeniaList();
        $gardeniaList->view('Index/index');
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        $form = new GardeniaForm();
        $form->addFormItem('gardenia','text','title','标题',null,[
            'lay-verify' => 'title'
        ])
            ->setInnerJs('text',"form.verify({title: function(value, item){if(!value) {alert('\{\$alert\}');return false;}}})",['alert'=> '你猜'])
            ->addBottomButton('gardenia','submit','submit','提交')
            ->setFormStatus(false)
            ->display();
    }

    public function getData() {
        return response(['code' => 0, 'data' => []],200,[],'json');
    }
}
