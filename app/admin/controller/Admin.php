<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\controller;

use app\admin\model\Admin as AdminModel;
use app\validate\admin\AdminValidate;
use constant\AppConstant;
use app\admin\AdminController;
use app\admin\model\AdminGroupAccess;
use think\exception\ValidateException;
use think\facade\Db;
use app\admin\model\AdminGroup;

class Admin extends AdminController
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
                    'a.title','like','%'.$data['search'].'%'
                ];
            }
            $field = [
              'a.id',
              'a.username',
              'a.status',
              'p.username' => 'parent_username',
              'a.create_time',
              'a.update_time',
            ];
            $list = AdminModel::alias('a')->leftJoin('admin p','p.id = a.pid')->where($map)->limit($data['offset'],$data['limit'])
                ->field($field)->order(['a.create_time' => 'desc'])->select();
            $total = AdminModel::alias('a')->leftJoin('admin p','p.id = a.pid')->where($map)->count('a.id');
            return json([
                'rows' => $list,
                'total' => $total,
            ]);
        }

        $this->view();
    }
    public function add() {
        $request = \request();
        if ($request->isGet()){
            $adminGroup = AdminGroup::where(['status'=> AppConstant::STATUS_FORMAL])->field('title,id')->select()->toArray();
            $statusList = AppConstant::getStatusList();

            $this->view('',[
                'groupList' => $adminGroup,
                'statusList' => $statusList,
                'defaultGroup' => $adminGroup[0]['id'],
                'defaultStatus' => AppConstant::STATUS_FORMAL,
            ]);
        }elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new AdminValidate();
            $rule = $validate->setAddAdminRule();
            if (!$validate->check($data)){
                error($validate->getError());
            }
            $res = AdminGroup::find($data['group_id']);
            if (!$res) {
                error('该管理员组不存在');
            }
            $data = make_validate_rule_data($rule,$data);
            $salt = create_salt();
            $encryptPwd = create_password($data['password'],$salt);
            $data['password'] = $encryptPwd;
            $data['salt'] = $salt;
            $data['pid'] = $request->admin_info->admin_id;
            Db::startTrans();
            try {
                $adminModel = new AdminModel();
                $adminModel->save($data);
                $adminGroupAccessModel = new AdminGroupAccess();
                //将该用户与对应的组加入group_access中
                $adminGroupAccessModel->save(['admin_id' => $adminModel->id,'group_id' => $data['group_id']]);
                Db::commit();

            } catch (\Exception $e) {
                Db::rollback();
                error('创建管理员失败，请稍候重试');
            }
            success('新增用户名成功！');
        }
    }

    public function edit($id) {
        $action = '更新管理员信息';
        $request = $this->request;
        if ($request->isGet()){
            try {
                $this->validate(['id' => $id],['id' => 'require|integer|>:0']);
            } catch (ValidateException $e) {
                error($e->getMessage());
            }
            $field = [
                'a.id',
                'a.username',
                'a.status',
                'ac.group_id',
            ];
            $row = AdminModel::alias('a')->join('admin_group_access ac','ac.admin_id = a.id')->field($field)
                ->where(['a.id' => $id])->find();
            if (!$row) {
                error('该管理员不存在');
            }
            $adminGroup = AdminGroup::where(['status' => AppConstant::STATUS_FORMAL])->field('title,id')->select();
            $statusList = AppConstant::getStatusList();
            $this->view('',[
                'row' => $row,
                'statusList' => $statusList,
                'groupList' => $adminGroup,
            ]);
        }elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new AdminValidate();
            $validate->setEditAdminRule();
            if (!$validate->check($data)){
                error($validate->getError());
            }
            $res = AdminGroup::find($data['group_id']);
            if (!$res) {
                error('该管理员组不存在');
            }
            $_data['status'] = $data['status'] ?? AppConstant::STATUS_FORMAL;
            $_data['group_id'] = $data['group_id'];
            $_data['username'] = $data['username'];
            $_data['id'] = $data['id'];
            $_data['password'] = empty($data['password']) ? '' : $data['password'];

            $data = $_data;
            if ($data['password']) {
                $salt = create_salt();
                $encryptPwd = create_password($data['password'],$salt);
                $data['password'] = $encryptPwd;
                $data['salt'] = $salt;
            }
            $adminGroupAccess = AdminGroupAccess::where([
                'admin_id' => $data['id']
            ])->find();
            if (!$adminGroupAccess) {
                error('该用户的管理员组信息不存在');
            }
            Db::startTrans();
            try {
                AdminModel::update($data,[
                    'id' => $data['id']
                ]);
                $adminGroupAccess->group_id = $data['group_id'];
                $adminGroupAccess->save();
                Db::commit();
            }catch (\Exception $e) {
                Db::rollback();
                error($action.'失败，请稍候重试');
            }
            success($action.'成功！');
        }
    }
    public function delete() {
        $data = $this->request->post();
        try {
            $this->validate($data,['id' => 'require']);
        } catch (ValidateException $e) {
            error($e->getMessage());
        }
        $data['id'] = explode(',',$data['id']);
        $admin = AdminModel::select($data['id']);
        if (!$admin) {
            error('所选的管理员信息不存在!');
        }
        AdminModel::destroy($data['id'],true);
        success('删除成功！');
    }

}