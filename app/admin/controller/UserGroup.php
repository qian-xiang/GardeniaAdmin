<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\AuthGroup;
use constant\AppConstant;
use app\admin\AdminController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaHelper;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;
use think\Request;
use think\Validate;
use think\validate\ValidateRule;

class UserGroup extends AdminController
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
            ->addTableHead('choose','选择',['type' => 'checkbox'])
            ->addTableHead('id','ID')
            ->addTableHead('title','用户组')
            ->addTableHead('type','类型')
            ->addTableHead('status','状态')
            ->addTableHead('operate','操作',['type' => 'normal'])
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
            if ($request->user['admin_type'] !== AppConstant::GROUP_TYPE_SUPER_ADMIN){
                $this->error('不是超级管理员不能创建用户组');
            }
            $ruleList = Db::name('auth_rule')->field('id,title,pid,name as field')->select()->toArray();
            $nodeList = [];
            if ($ruleList){
                $nodeList = $this->buildTreeData($ruleList,0);
            }
            $statusList = AppConstant::getStatusList();

            $js = "./static/js/gardenia/UserGroupTreeExtraJs.js";

            $typeList = AppConstant::getRuleTypeList();

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','text','title','用户组名',null,null)
                ->addFormItem('gardenia','select','type','类型',$typeList,['value' => AppConstant::RULE_TYPE_MENU])
                ->addFormItem('gardenia','select','status','状态',$statusList,['value' => AppConstant::STATUS_FORMAL])
                ->addFormItem('gardenia','tree','rules','规则',$nodeList,null)
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
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $request = \request();
        if ($request->isGet()){
            $userGroup = Db::name('auth_group')->field('id,title,status,rules')->find($id);
            $ruleList = Db::name('auth_rule')->field('id,title,pid,name as field')->select()->toArray();
            $userGroup['rules'] = explode(',',$userGroup['rules']);
            if ($ruleList){
                $nodeList = $this->buildTreeData($ruleList,0,$userGroup['rules']);
            }
            $statusList = AppConstant::getStatusList();


            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','hidden','id','ID',null,['value'=> $userGroup['id']])
                ->addFormItem('gardenia','text','title','用户组名',null,['value'=> $userGroup['title'], 'readonly' => true])
                ->addFormItem('gardenia','select','status','状态',$statusList,[
                        'disabled' => true,
                        'value' => $userGroup['status']
                    ])
                ->addFormItem('gardenia','tree','rules','规则',$nodeList,['disabled' => true])
                ->addBottomButton('gardenia','cancel','cancel','返回')
                ->setFormStatus(true)
                ->display();
        }
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
            if ($request->admin_info->authGroup->type !== AppConstant::GROUP_TYPE_SUPER_ADMIN){
                $this->error('非超级管理员不能编辑用户组');
            }
            $userGroup = Db::name('auth_group')->field('id,title,status,rules')->find($id);
            $ruleList = Db::name('auth_rule')->field('id,title,pid,name as field')->select()->toArray();
            $userGroup['rules'] = explode(',',$userGroup['rules']);
            $nodeList = [];
            if ($ruleList){
                $nodeList = $this->buildTreeData($ruleList,0,$userGroup['rules']);
            }
            $statusList = AppConstant::getStatusList();

//            $js = "./static/js/gardenia/userGroup_edit.js";

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','hidden','id','ID',null,['value'=> $userGroup['id']])
                ->addFormItem('gardenia','text','title','用户组名',null,['value'=> $userGroup['title']])
                ->addFormItem('gardenia','select','status','状态',$statusList,['value' => $userGroup['status']])
                ->addFormItem('gardenia','tree','rules','规则',$nodeList,null)
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
//                ->addTreeItemJs('rules','path',$js)
                ->setFormStatus(true)
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
                $this->error($validate->getError());
            }
            $data['rules'] = json_decode($data['rules'],true);
            $rules = $this->getMenuIds($data['rules']);

            $rules = $rules ? implode(',',$rules) : '';
            $updateData = [
                'title' => $data['title'],
                'status' => $data['status'],
                'rules' => $rules,
            ];
            $res = AuthGroup::update($updateData,[
                'id' => $data['id'],
            ]);
            if (!$res){
                $this->error('更新用户组名失败，请稍候重试。');
            }
            $this->success('更新用户组成功！',url('/'.$request->controller())->build());
        }
    }

    /**
     * 删除指定资源
     *
     * @return \think\Response
     */
    public function delete()
    {
        $request = $this->request;
        $id = $request->post('id',0);
        !isset($id) && $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'id必传');
        if ($request->user['admin_type'] !== AppConstant::GROUP_TYPE_SUPER_ADMIN){
            $this->error('超级管理员不能删除用户组');
        }
        $res = Db::name('auth_group')->where([
            ['id','in',$id]
        ])->delete();
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
            ->withAttr('type',function ($value) {
                return AppConstant::getAdminTypeAttr($value);
            })
            ->order(['id' => 'desc'])->select()->toArray();
        $recordCount = count($list);
        $list = GardeniaHelper::layPaginate($list);
        $data = [
            'code' => AppConstant::CODE_SUCCESS,
            'msg' => '获取成功！',
            'count' => $recordCount,
            'data' => $list
        ];

        return response($data,200,[],'json');
    }
    protected function getMenuIds($data = []) {
        $list = [];
        $_list = [];
        foreach ($data as $item) {
            if (!empty($item['children'])) {
                $_list = $this->getMenuIds($item['children']);
            }
            $item['checked'] && $list[] = $item['id'];
        }
        unset($item);
        $list = array_merge_recursive($list,$_list);

        return $list;
    }

}
