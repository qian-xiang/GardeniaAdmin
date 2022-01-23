<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\AdminGroup as AdminGroupModel;
use app\validate\admin\AdminGroupValidate;
use constant\AppConstant;
use app\admin\AdminController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaHelper;
use think\exception\ValidateException;
use think\facade\Db;
use app\admin\model\MenuRule;

class AdminGroup extends AdminController
{
    public function index()
    {
        $request = $this->request;
        if ($request->isAjax() && $request->isGet()) {
            $data = $request->get();
            try {
                $this->validate($data,[
                    'offset|偏移量' => 'require|integer',
                    'limit|记录数' => 'require|integer',
                ]);
            } catch (ValidateException $e) {
                error($e->getMessage());
            }
            $data['limit'] = (int)$data['limit'];
            $data['offset'] = (int)$data['offset'];
            $map = [];
            if (!empty($data['search'])) {
                $map[] = [
                    'title','like','%'.$data['search'].'%'
                ];
            }

            $list = AdminGroupModel::where($map)->limit($data['offset'],$data['limit'])
                ->order(['create_time' => 'desc'])->select()->toArray();
            $total = AdminGroupModel::where($map)->count('id');
            return json([
                'rows' => $list,
                'total' => $total,
            ]);
        }

        $this->view();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function add()
    {
        $request = $this->request;
        if ($request->isGet()){
            $ruleList = MenuRule::field('id,title as text,pid as parent,name')->select()->toArray();
            foreach ($ruleList as &$item) {
                $item['parent'] = $item['parent'] ?: '#';
                $item['state'] = [
                    'opened' => true,
                ];
            }
            unset($item);
            $adminGroupList = AdminGroupModel::field('id,title')->select();
            $this->view('',[
                'ruleList' => $ruleList,
                'pidList' => $adminGroupList,
                'pidVal' => $adminGroupList[0]['id'],
                'statusList' => AppConstant::getStatusList(),
                'defaultStatus' => AppConstant::STATUS_FORMAL,
            ]);
        }elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new AdminGroupValidate();
            $rule = $validate->setAddAdminGroupRule();
            if (!$validate->check($data)){
                error($validate->getError());
            }
            $keys = array_keys($rule);
            $_data = [];
            foreach ($keys as $key) {
                $temp = explode('|',$key);
                !empty($data[$temp[0]]) && $_data[$temp[0]] = $data[$temp[0]];
            }
            $_data['rules'] = $_data['rules'] ? join(',',$_data['rules']) : '';
            $model = new AdminGroupModel();
            $model->save($_data);
            success('新增用户组成功！');
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
            $adminGroup = AdminGroupModel::find($id);
            if (!$adminGroup) {
                error('该分组已不存在');
            }
            $adminGroup['rules'] = $adminGroup['rules'] ? explode(',',$adminGroup['rules']) : [];
            $ruleList = MenuRule::field('id,title as text,pid as parent,name')->select()->toArray();
            foreach ($ruleList as &$item) {
                $item['parent'] = $item['parent'] ?: '#';
                $item['state'] = [
                    'opened' => true,
                    'selected' => in_array($item['id'],$adminGroup['rules'])
                ];
            }
            unset($item);
            $adminGroupList = AdminGroupModel::field('id,title')->select();
            $this->view('',[
                'ruleList' => $ruleList,
                'pidList' => $adminGroupList,
                'statusList' => AppConstant::getStatusList(),
                'row' => $adminGroup,
            ]);
        }elseif ($request->isPost()) {
            $data = $request->post();
            $adminGroup = AdminGroupModel::find($data['id']);
            if (!$adminGroup) {
                error('该分组已不存在');
            }
            $validate = new AdminGroupValidate();
            $rule = $validate->setEditAdminGroupRule();
            if (!$validate->check($data)){
                error($validate->getError());
            }
            $rule_key = array_keys($rule);
            $_data = [];
            foreach ($rule_key as $key) {
                $temp = explode('|',$key);
                !empty($data[$temp[0]]) && $_data[$temp[0]] = $data[$temp[0]];
            }
            //找出该分组的所有子节点
            $rows = AdminGroupModel::field('id,pid')->select();
            $children = [];
            $tempList = [
                ['id' => $data['id']]
            ];

            while (true) {
                $temp = [];
                foreach ($tempList as $temp_item) {
                    foreach ($rows as $item) {
                        if ($temp_item['id'] === $item['pid']) {
                            $children[] = $item['id'];
                            $temp[] = $item;
                        }
                    }
                }
                if (!$temp) {
                    break;
                }
                $tempList = $temp;
            }

            $row = AdminGroupModel::where([
                ['id','in',$children]
            ])->field('id,rules')->select();
            //连带更新子节点的规则
            foreach ($row as &$item) {
                $item['rules'] = $item['rules'] ? explode(',',$item['rules']) : [];
                $item['rules'] = join(',',array_intersect($data['rules'],$item['rules']));
            }
            unset($item);
            $_data['rules'] = join(',',$_data['rules']);
            $row[] = $_data;
            $model = new AdminGroupModel();
            $model->saveAll($row);
            success('更新用户组成功！',[],url('/'.$request->controller())->build());
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
        !isset($id) && error('id必传');
        if ($request->user['admin_type'] !== AppConstant::GROUP_TYPE_SUPER_ADMIN){
            error('超级管理员不能删除用户组');
        }
        $res = Db::name('auth_group')->where([
            ['id','in',$id]
        ])->delete();
        if (!$res){
            error('删除失败');
        }
        error('删除成功',[],url('/'.$request->controller())->build());
    }


}
