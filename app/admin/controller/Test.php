<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\GardeniaController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\Request;
use think\View;

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
//            ,height: 312
//            ,url: '/admin.php/index/getData' //数据接口
        $gardeniaList = new GardeniaList('id');
        $gardeniaList
//            ->setHeadToolbox('#toolbarDemo','path','./static/js/gardenia/text_template.js')
            ->setTableAttr('url',url('/'.$request->controller().'/getData')->build())
            ->setTableAttr('page',true)
            ->addExtraLayuiJS('path','./static/js/gardenia/list_extra_layui.js')
            ->addListHead('choose','选择','checkbox')
//            ->addListHead('id','ID')
            ->addListHead('title','标题')
            ->addListHead('type','规则类型')
            ->addListHead('icon','图标')
            ->addListHead('name','规则')
            ->addListHead('weigh','权重')
            ->addListHead('status','状态')
//            ->addListHead('operate','操作','normal','#rightToolbox',$template)
            ->addListHead('operate','操作','normal')
            ->addTopOperateButton('gardenia','新增','create',['id'=> 'create',
                'onclick'=> 'location.href="'.url('/'.request()->controller().'/create')->build().'"'])
            ->addTopOperateButton('gardenia','删除','delete',['id'=> 'delete'])
            ->addColumnOperateButton('operate','查看','gardenia','read',['name'=> "item_read",'lay-event' => 'read'],['rule-name' => 'item_read'])
            ->addColumnOperateButton('operate','编辑','gardenia','edit',['name'=> "item_edit",'lay-event' => 'edit'],[
                'rule-name' => 'item_edit','redirect-url' => url('/'.request()->controller().'/edit')->build()])
            ->addColumnOperateButton('operate','删除','gardenia','delete',['name' => 'item_delete','lay-event' => 'delete'],['rule-name' => 'item_delete'])
            ->display();
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

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
    public function getData() {
        $menu = new Menu();
        $menu->getData();
    }
}
