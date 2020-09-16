<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\GardeniaController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;
use think\Request;
use think\Validate;
use think\validate\ValidateRule;

class UserGroup extends GardeniaController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = \request();
        $gardeniaList = new GardeniaList();
        $gardeniaList->setTableAttr('url',url('/'.$request->controller().'/getData')->build())
            ->addListHead('title','用户组')
            ->addListHead('status','状态')
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
        $request = \request();
        if ($request->isGet()){
            $ruleList = Db::name('auth_rule')->field('id,title,pid,name as field')->select()->toArray();
            $nodeList = [];
            if ($ruleList){
                $nodeList = $this->buildTreeData($ruleList,0);
            }
            $statusList = [
                ['label'=> '禁用', 'value' => 0],
                ['label'=> '正常', 'value' => 1,'selected' => 'selected'],
            ];

            $js = "./static/js/gardenia/UserGroupTreeExtraJs.js";

            $typeList = [
                ['label'=> '超级管理员', 'value' => 0],
                ['label'=> '管理员', 'value' => 1,'selected' => 'selected'],
            ];

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','text','title','用户组名',null,null,true)
                ->addFormItem('gardenia','select','type','类型',$typeList,null,true)
                ->addFormItem('gardenia','select','status','状态',$statusList,null,true)
                ->addFormItem('gardenia','tree','rules','规则',$nodeList,null,true)
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->addTreeItemJs('rules','path',$js)
                ->setFormStatus(false)
                ->display();
        }elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new Validate();
            $validate->rule([
                'title|用户组名' => ValidateRule::isRequire(),
                'status|状态' => ValidateRule::isRequire()->isInteger(),
                'type|类型' => ValidateRule::isRequire()->isInteger(),
                'rules|规则' => ValidateRule::requireIf('type,'.AppConstant::GROUP_TYPE_ADMIN),
            ]);
            if (!$validate->check($data)){
                $this->layuiAjaxReturn(AppConstant::CODE_ERROR,$validate->getError());
            }
            $res = Db::name('auth_group')->strict(false)->save($data);
            if (!$res){
                $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'新增用户组名失败，请稍候重试。');
            }
            $this->layuiAjaxReturn(AppConstant::CODE_SUCCESS,'新增用户组成功！','',url('/'.$request->controller())->build());
        }
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
        $request = \request();
        if ($request->isGet()){
            $userGroup = Db::name('auth_group')->field('id,title,status,rules')->find($id);
            $ruleList = Db::name('auth_rule')->field('id,title,pid,name as field')->select()->toArray();
            $userGroup['rules'] = explode(',',$userGroup['rules']);
            if ($ruleList){
                $nodeList = $this->buildTreeData($ruleList,0,$userGroup['rules']);
            }
            $statusList = [
                ['label'=> '禁用', 'value' => 0,'selected' => $userGroup['status'] === 0],
                ['label'=> '正常', 'value' => 1,'selected' => $userGroup['status'] === 1],
            ];

            $js = "./static/js/gardenia/userGroup_edit.js";

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','hidden','id','ID',null,['value'=> $userGroup['id']])
                ->addFormItem('gardenia','text','title','用户组名',null,['value'=> $userGroup['title']],true)
                ->addFormItem('gardenia','select','status','状态',$statusList,null,true)
                ->addFormItem('gardenia','tree','rules','规则',$nodeList,null,true)
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->addTreeItemJs('rules','path',$js)
                ->setFormStatus(false)
                ->display();
        }elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new Validate();
            $validate->rule([
                'title|用户组名' => ValidateRule::isRequire(),
                'status|状态' => ValidateRule::isRequire()->isInteger(),
                'rules|规则' => ValidateRule::isRequire(),
                'id|ID' => ValidateRule::isRequire(),
            ]);
            if (!$validate->check($data)){
                $this->layuiAjaxReturn(AppConstant::CODE_ERROR,$validate->getError());
            }
            $res = Db::name('auth_group')->strict(false)->save($data);
            if (!$res){
                $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'更新用户组名失败，请稍候重试。');
            }
            $this->layuiAjaxReturn(AppConstant::CODE_SUCCESS,'更新用户组成功！','',url('/'.$request->controller())->build());
        }
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
        $request = request();
        !isset($id) && $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'id必传');
        $res = Db::name('auth_group')->where(['id' => $id])->delete();
        if (!$res){
            return $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'删除失败');
        }
        return $this->layuiAjaxReturn(AppConstant::CODE_SUCCESS,'删除成功','',url('/'.$request->controller())->build());
    }
    public function getData() {
        $list = Db::name('auth_group')
            ->withAttr('status',function ($value) {
                return AppConstant::getStatusAttr($value);
            })
            ->order(['id' => 'desc'])->select()->toArray();
        $data = [
            'code' => AppConstant::CODE_SUCCESS,
            'msg' => '获取成功！',
            'count' => count($list),
            'data' => $list
        ];

        return response($data,200,[],'json');
    }
}
