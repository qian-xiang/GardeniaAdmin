<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\controller;


use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\GardeniaController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaHelper;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;
use think\Validate;
use think\validate\ValidateRule;

class User extends GardeniaController
{
    public function index() {
        $request = request();
        $gardeniaList = new GardeniaList();
        $gardeniaList->setTableAttr('url',url('/'.$request->controller().'/getData')->build())
            ->addTableHead('username','用户名')
            ->addTableHead('login_status','状态')
            ->addTableHead('last_login_time','最近登录时间')
            ->addTableHead('operate','操作',['type' => 'normal'])
            ->addTopOperateButton('gardenia','新增','create',['id'=> 'create',
                'onclick'=> 'location.href="'.url('/'.request()->controller().'/create')->build().'"'])
            ->addTopOperateButton('gardenia','删除','delete',['id'=> 'delete'])
            ->addColumnOperateButton('operate','查看','gardenia','read',['name'=> "item_read",'lay-event' => 'read'],['rule-name' => 'item_read'],'/User/read')
            ->addColumnOperateButton('operate','编辑','gardenia','edit',['name'=> "item_edit",'lay-event' => 'edit'],[
                'rule-name' => 'item_edit','redirect-url' => url('/'.request()->controller().'/edit')->build()],'/User/edit')
            ->addColumnOperateButton('operate','删除','gardenia','delete',['name' => 'item_delete','lay-event' => 'delete'],['rule-name' => 'item_delete'],'/User/delete')
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
                ->addFormItem('gardenia','text','username','用户名',null,null)
                ->addFormItem('gardenia','password','password','密码',null,null)
                ->addFormItem('gardenia','password','confirm','确认密码',null,null)
                ->addFormItem('gardenia','select','user_group_id','用户组',$userGroup,null)
                ->addFormItem('gardenia','select','status','状态',$statusList,null)
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
            $data['password'] = password_encrypt($data['password']);

            $userInfo = $request->user;

            $saveData = [
                'last_login_ip'=> get_client_ip(),
                'last_login_time'=> time(),
                'create_time'=> time(),
                'pid' => $userInfo['id'],
            ];

            if ($userInfo['root_id'] === AppConstant::USER_NO_PID){
                $saveData['root_id'] = $userInfo['id'];
            } else {
                $saveData['root_id'] = $userInfo['root_id'];
            }

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
                ->leftJoin('auth_group_access a','u.id = a.uid')
                ->where([
                    'u.id' => $id,
                    'u.is_delete' => AppConstant::USER_NO_DELETE,
                ])->field('u.id,u.username,u.p_id,u.login_status,a.group_id')->select()->toArray();
            if (!$user){
                $this->error('该用户不存在或尚未为其分配权限！');
            }
            $user = $user[0];
            if ($request->user['admin_type'] === AppConstant::GROUP_TYPE_ADMIN){
                if ($user['p_id'] !== $request->user['id'] && $user['create_user_id'] !== AppConstant::USER_NO_PID){
                    $this->error('该用户不是您创建的，因此您没有操作该用户的权限！');
                }
            }

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
                ->addFormItem('gardenia','hidden','id','ID',null,['value' => $id])
                ->addFormItem('gardenia','text','username','用户名',null,['value' => $user['username']])
                ->addFormItem('gardenia','password','password','密码',null,null)
                ->addFormItem('gardenia','password','confirm','确认密码',null,null)
                ->addFormItem('gardenia','select','user_group_id','用户组',$userGroup,null)
                ->addFormItem('gardenia','select','status','状态',$statusList,null)
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
                'status|状态' => ValidateRule::isRequire()->isInteger(),
            ]);
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }
            if (isset($data['password']) || isset($data['confirm'])) {
                $validate = new Validate();
                $validate->rule([
                    'password|密码' => ValidateRule::isRequire(),
                    'confirm|确认密码' => ValidateRule::isRequire()->confirm('password','确认密码和密码不一致'),
                ]);
                if (!$validate->check($data)){
                    $this->error($validate->getError());
                }
                $data['password'] = password_encrypt($data['password']);
            }


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
    public function delete($id) {
        $request = request();
        if (!$request->isPost()){
            $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'请求方式必须是POST');
        }
        $user = Db::name('user')->find($id);
        if (!$user) {
            $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'该用户信息不存在!');
        }
       $res = Db::name('user')->where(['id'=> $id, 'root_id' => $id])->delete();
       if (!$res) {
           $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'删除该用户失败，请稍候重试!');
       }
        $this->layuiAjaxReturn(AppConstant::CODE_SUCCESS,'删除成功！');
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