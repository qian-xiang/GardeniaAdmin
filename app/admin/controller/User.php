<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\extend\diy\extra_class\AppConstant;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;
use think\Validate;
use think\validate\ValidateRule;

class User extends BaseController
{
    public function index() {
        $request = request();
        $gardeniaList = new GardeniaList();
        $gardeniaList->setTableAttr('url',url('/'.$request->controller().'/getData')->build())
            ->addListHead('username','用户名')
            ->addListHead('login_status','状态')
            ->addListHead('last_login_time','最近登录时间')
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
    public function create() {
        $request = \request();
        if ($request->isGet()){
            $userGroup = Db::name('auth_group')
                ->where(['status'=> AppConstant::STATUS_FORMAL])->field('id as value,title as label')->select()->toArray();

            $statusList = [
                ['label'=> '禁用', 'value' => 0],
                ['label'=> '正常', 'value' => 1,'selected' => 'selected'],
            ];

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm
                ->addFormItem('gardenia','text','username','用户名',null,null,true)
                ->addFormItem('gardenia','password','password','密码',null,null,true)
                ->addFormItem('gardenia','password','confirm','确认密码',null,null,true)
                ->addFormItem('gardenia','select','user_group_id','用户组',$userGroup,null,true)
                ->addFormItem('gardenia','select','status','状态',$statusList,null,true)
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->display();
        }elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new Validate();
            $validate->rule([
                'username|用户名' => ValidateRule::isRequire(),
                'password|密码' => ValidateRule::isRequire(),
                'confirm|确认密码' => ValidateRule::isRequire()->confirm('password','确认密码和密码不一致'),
                'user_group_id|用户组' => ValidateRule::isRequire()->isInteger(null,'请选择用户组'),
                'status|状态' => ValidateRule::isRequire()->isInteger(),
            ]);
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }
            $data['password'] = password_encrypt($data['password']);

            $saveData = [
                'last_login_ip'=> get_client_ip(),
                'last_login_time'=> time(),
                'create_time'=> time(),
            ];
            $data = array_merge($data,$saveData);
            Db::startTrans();
            $insertID = Db::name('user')->strict(false)->insert($data,true);
            if (!$insertID){
                Db::rollback();
                $this->error('新增用户名失败，请稍候重试。');
            }
            //新增用户成功，更新login_code
            //生成登录token存入数据库
            $token = login_token_generate($insertID);
            $res = Db::name('user')->strict(false)->save(['id'=> $insertID,'login_code' => $token]);
            if (!$res){
                Db::rollback();
                $this->error('更新login_code失败，请稍候重试。');
            }
            //将该用户与对应的组加入group_access中
            $res = Db::name('auth_group_access')->save(['uid' => $insertID,'group_id' => $data['user_group_id']]);
            if (!$res){
                Db::rollback();
                $this->error('将权限关系加入用户组明细表中失败，请稍候重试。');
            }
            Db::commit();
            $this->success('新增用户名成功！');
        }
    }
    public function edit($id) {
        !$id && $this->error('id必传');
        $request = \request();
        if ($request->isGet()){
            $user = Db::name('user')->alias('u')
                ->join('auth_group_access a','u.id = a.uid')
                ->where(['u.id' => $id])->field('u.id,u.username,u.login_status,a.group_id')->select()->toArray();
            if (!$user){
                $this->error('该用户不存在！');
            }
            $user = $user[0];
            $userGroup = Db::name('auth_group')
                ->where(['status'=> AppConstant::STATUS_FORMAL])->field('id as value,title as label')->select()->toArray();
            if ($userGroup) {
                foreach ($userGroup as &$item) {
                    if ($item['value'] === $user['group_id']){
                        $item['selected'] = true;
                    }
                }
            }

            $statusList = [
                ['label'=> '禁用', 'value' => 0,'selected' => $user['login_status'] === 0],
                ['label'=> '正常', 'value' => 1,'selected' => $user['login_status'] === 1],
            ];

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm
                ->addFormItem('gardenia','hidden','id','ID',null,['value' => $id],true)
                ->addFormItem('gardenia','text','username','用户名',null,['value' => $user['username']],true)
                ->addFormItem('gardenia','password','password','密码',null,null,true)
                ->addFormItem('gardenia','password','confirm','确认密码',null,null,true)
                ->addFormItem('gardenia','select','user_group_id','用户组',$userGroup,null,true)
                ->addFormItem('gardenia','select','status','状态',$statusList,null,true)
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->display();
        }elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new Validate();
            $validate->rule([
                'id|用户ID' => ValidateRule::isRequire()->isInteger(),
                'username|用户名' => ValidateRule::isRequire(),
                'password|密码' => ValidateRule::isRequire(),
                'confirm|确认密码' => ValidateRule::isRequire()->confirm('password','确认密码和密码不一致'),
                'user_group_id|用户组' => ValidateRule::isRequire()->isInteger(null,'请选择用户组'),
                'status|状态' => ValidateRule::isRequire()->isInteger(),
            ]);
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }
            $data['password'] = password_encrypt($data['password']);


            Db::startTrans();
            $res = Db::name('user')->strict(false)->save($data);
            if (!$res){
                Db::rollback();
                $this->error('更新用户信息失败，请稍候重试。');
            }
            //查询该用户的所在的用户组
            $group_id = Db::name('auth_group_access')->where(['uid' => $data['id']])->value('group_id');
            if (!$group_id){
                Db::rollback();
                $this->error('查询不到该用户所在的用户组信息');
            }
            if ($group_id !== (int)$data['user_group_id']){
                $res = Db::name('auth_group_access')->where(['uid' => $id])->update(['group_id' => $data['user_group_id']]);
                if (!$res){
                    Db::rollback();
                    $this->error('将权限关系加入用户组明细表中失败，请稍候重试。');
                }
            }

            Db::commit();
            $this->success('更新用户信息成功！');
        }
    }
    public function getData() {
        $list = Db::name('user')
            ->withAttr('login_status',function ($value) {
                return AppConstant::getStatusAttr($value);
            })
            ->withAttr('last_login_time',function ($value) {
                return AppConstant::timestampToMinute($value);
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