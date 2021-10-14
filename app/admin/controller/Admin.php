<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\controller;


use constant\AppConstant;
use app\admin\AdminController;
use app\admin\model\AuthGroupAccess;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaHelper;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;
use think\Validate;
use think\validate\ValidateRule;
use app\admin\model\Admin as AdminModel;

class Admin extends AdminController
{
    public function index() {
        $request = request();
        $gardeniaList = new GardeniaList();
        $gardeniaList->setTableAttr('url',url('/'.$request->controller().'/getData')->build())
            ->addTableHead('choose','选择',['type' => 'checkbox'])
            ->addTableHead('username','用户名')
            ->addTableHead('login_status','状态')
            ->addTableHead('last_login_time','上次登录时间')
            ->addTableHead('login_time','登录时间')
            ->addTableHead('operate','操作',['type' => 'normal'])
            ->addTopOperateButton('gardenia','新增','create',['id'=> 'create',
                'onclick'=> 'location.href="'.url('/'.request()->controller().'/create')->build().'"'])
            ->addTopOperateButton('gardenia','删除','delete',['id'=> 'delete'])
            ->addColumnOperateButton('operate','查看','gardenia','read','/Admin/read',['name'=> "item_read",'lay-event' => 'read'])
            ->addColumnOperateButton('operate','编辑','gardenia','edit','/Admin/edit',['name'=> "item_edit",'lay-event' => 'edit'],[
                'redirect-url' => url('/'.request()->controller().'/edit')->build()])
            ->addColumnOperateButton('operate','删除','gardenia','delete','/Admin/delete',['name' => 'item_delete','lay-event' => 'delete'])
            ->display();
    }
    public function create() {
        $request = \request();
        if ($request->isGet()){
            $userGroup = Db::name('auth_group')
                ->where(['status'=> AppConstant::STATUS_FORMAL])->column('title','id');

            $statusList = AppConstant::getStatusList();

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm
                ->addFormItem('gardenia','text','username','用户名',null,null)
                ->addFormItem('gardenia','password','password','密码',null,null)
                ->addFormItem('gardenia','password','confirm','确认密码',null,null)
                ->addFormItem('gardenia','select','user_group_id','用户组',$userGroup,null)
                ->addFormItem('gardenia','select','status','状态',$statusList,['value' => AppConstant::STATUS_FORMAL])
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->setFormWholeStyle(['colon' => true])
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
            if ($request->admin_info->authGroup->type !== AppConstant::GROUP_TYPE_SUPER_ADMIN) {
                $this->error('只有超级用户才能创建用户');
            }
            $salt = create_salt();
            $encryptPwd = create_password($data['password'],$salt);
            $data['password'] = $encryptPwd;


            $userInfo = $request->admin_info->admin;

            $saveData = [
                'last_login_ip'=> get_client_ip(),
                'last_login_time'=> time(),
                'create_time'=> time(),
                'pid' => $userInfo['id'],
                'password' => $data['password'],
                'salt' => $salt,
                'username' => $data['username'],
                'status' => $data['status'],
            ];

            if ($userInfo['root_id'] === AppConstant::USER_NO_PID){
                $saveData['root_id'] = $userInfo['id'];
            } else {
                $saveData['root_id'] = $userInfo['root_id'];
            }

            $data = array_merge($data,$saveData);
            Db::startTrans();
            $insertID = AdminModel::insert($saveData,true);
            if (!$insertID){
                Db::rollback();
                $this->error('新增用户名失败，请稍候重试。');
            }

            //将该用户与对应的组加入group_access中
            $res = Db::name('auth_group_access')->save(['admin_id' => $insertID,'group_id' => $data['user_group_id']]);
            if (!$res){
                Db::rollback();
                $this->error('将权限关系加入用户组明细表中失败，请稍候重试。');
            }
            Db::commit();
            $this->success('新增用户名成功！');
        }
    }
    public function read($id) {
        !$id && $this->error('id必传');
        $request = \request();
        if ($request->isGet()){
            $user = Db::name('admin')->alias('u')
                ->leftJoin('auth_group_access a','u.id = a.admin_id')
                ->where([
                    'u.id' => $id,
                    'u.is_delete' => AppConstant::USER_NO_DELETE,
                ])->field('u.id,u.username,u.p_id,u.login_status,a.group_id')->select()->toArray();
            if (!$user){
                $this->error('该用户不存在或尚未为其分配权限！');
            }
            $user = $user[0];

            if ($request->admin_info->authGroup->type === AppConstant::GROUP_TYPE_ADMIN){
                if ($user['p_id'] !== $request->admin_info->id && $user['create_user_id'] !== AppConstant::USER_NO_PID){
                    $this->error('该用户不是您创建的，因此您没有操作该用户的权限！');
                }
            }

            $userGroup = Db::name('auth_group')
                ->where(['status'=> AppConstant::STATUS_FORMAL])->column('title','id');

            $statusList = AppConstant::getStatusList();

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm
                ->addFormItem('gardenia','hidden','id','ID',null,['value' => $id])
                ->addFormItem('gardenia','text','username','用户名',null,[
                        'value' => $user['username'],
                        'readonly' => true
                    ])
                ->addFormItem('gardenia','password','password','密码',null,['readonly' => true])
                ->addFormItem('gardenia','password','confirm','确认密码',null,['readonly' => true])
                ->addFormItem('gardenia','select','user_group_id','用户组',$userGroup,[
                    'disabled' => true,
                    'value' => $user['group_id']
                    ])
                ->addFormItem('gardenia','select','status','状态',$statusList,[
                    'disabled' => true,
                    'value' => $user['login_status']
                    ])
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->display();
        }
    }
    public function edit($id) {
        !$id && $this->error('id必传');
        $request = \request();
//        $adminModel = new AdminModel();
        if ($request->isGet()){
            $user = Db::name('admin')->alias('u')
                ->leftJoin('auth_group_access a','u.id = a.admin_id')
                ->where([
                    'u.id' => $id,
                    'u.is_delete' => AppConstant::USER_NO_DELETE,
                ])->field('u.id,u.username,u.pid,u.login_status,a.group_id')->select()->toArray();
            if (!$user){
                $this->error('该用户不存在或尚未为其分配权限！');
            }
            $user = $user[0];

            if ($request->admin_info->authGroup->type === AppConstant::GROUP_TYPE_ADMIN){
                if ($user['p_id'] !== $request->admin_info['id'] && $user['create_user_id'] !== AppConstant::USER_NO_PID){
                    $this->error('该用户不是您创建的，因此您没有操作该用户的权限！');
                }
            }

            $userGroup = Db::name('auth_group')
                ->where(['status'=> AppConstant::STATUS_FORMAL])->column('title','id');

            $statusList = AppConstant::getStatusList();

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm
                ->addFormItem('gardenia','hidden','id','ID',null,['value' => $id])
                ->addFormItem('gardenia','text','username','用户名',null,[
                    'value' => $user['username'],
                ])
                ->addFormItem('gardenia','password','password','密码',null)
                ->addFormItem('gardenia','password','confirm','确认密码',null)
                ->addFormItem('gardenia','select','user_group_id','用户组',$userGroup,[
                    'value' => $user['group_id']
                ])
                ->addFormItem('gardenia','select','login_status','状态',$statusList,[
                    'value' => $user['login_status']
                ])
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->display();
        }elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new Validate();
            $validate->rule([
                'id|用户ID' => ValidateRule::isRequire()->isInteger(),
                'username|用户名' => ValidateRule::isRequire(),
                'user_group_id|用户组' => ValidateRule::isRequire()->isInteger(null,'请选择用户组'),
                'login_status|状态' => ValidateRule::isRequire()->isInteger(),
            ]);
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }
            $updateData = [
                'username' => $data['username'],
                'login_status' => $data['login_status'],
            ];
            if (!empty($data['password']) && !empty($data['confirm'])) {
                if ($data['password'] !== $data['confirm']) {
                    $this->error('密码和确认密码不一致');
                }
                $salt = create_salt();
                $password = create_password($data['password'],$salt);
                $updateData['password'] = $password;
                $updateData['salt'] = $salt;
            }

            $info = AuthGroupAccess::hasWhere('admin',[
                'id' => $data['id'],
            ])->find();
            if (!$info->id) {
                $this->error('该用户不存在');
            }
            if ($request->admin_info->authGroup->type !== AppConstant::GROUP_TYPE_SUPER_ADMIN && $info->authGroup->type === AppConstant::GROUP_TYPE_SUPER_ADMIN) {
                $this->error('你不是超级管理员，无法修改超级管理员的信息');
            } elseif ($request->admin_info->authGroup->type !== AppConstant::GROUP_TYPE_SUPER_ADMIN &&
                $info->authGroup->type === AppConstant::GROUP_TYPE_ADMIN &&
                in_array($request->admin_info->admin->id,[$info->admin->parent_id,$info->admin->root_id]) === false) {
                $this->error('你不是TA的上级，无法修改信息');
            }
            if (!$info->group_id){
                $this->error('查询不到该用户所在的用户组信息');
            }
            Db::startTrans();
            $resAdmin = $info->admin()->save($updateData);

            $res = true;
            if ($info->group_id !== (int)$data['user_group_id']){
                $res = $info->update(['group_id' => $data['user_group_id']]);
                if (!$res && !$resAdmin){
                    Db::rollback();
                    $this->error('将权限关系更新入用户组明细表中失败，请稍候重试。');
                }
            }
            if ($res && $resAdmin) {
                Db::commit();
                $this->success('更新用户信息成功！');
            }
            Db::rollback();
            $this->success('更新用户信息失败，请重试！');
        }
    }
    public function delete() {
        $request = $this->request;
        $id = $request->post('id');
        if (!$id) {
            $this->error('未传递记录ID');
        }
        if ($request->admin_info->authGroup->type !== AppConstant::GROUP_TYPE_SUPER_ADMIN) {
            $this->error('只有超级用户才能删除管理员');
        }
        $user = Db::name('admin')->find($id);
        if (!$user) {
            $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'该用户信息不存在!');
        }
       $res = Db::name('admin')->where('id','in',$id)->whereOr(['root_id' => $id])->delete();
       if (!$res) {
           $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'删除该用户失败，请稍候重试!');
       }
        $this->layuiAjaxReturn(AppConstant::CODE_SUCCESS,'删除成功！');
    }
    public function getData() {
        $list = AdminModel::
            withAttr('login_status',function ($value) {
                return AppConstant::getStatusAttr($value);
            })
            ->withAttr('last_login_time',function ($value) {
                return $value ? AppConstant::timestampToMinute($value) : '无';
            })
            ->withAttr('login_time',function ($value) {
                return $value ? AppConstant::timestampToMinute($value) : '无';
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
}