<?php
declare (strict_types = 1);

namespace app\admin\controller;

use constant\AppConstant;
use app\admin\AdminController;
use \app\admin\model\MenuRule as AuthRuleModel;
use think\Exception;
use think\facade\Db;
use think\Validate;
use think\validate\ValidateRule;

class MenuRule extends AdminController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = $this->request;
        if ($request->isAjax() && $request->isGet()) {
            $data = $request->get();
            $vali = $this->validate($data,[
                'offset|偏移量' => 'require|integer',
                'limit|记录数' => 'require|integer',
            ]);
            if ($vali !== true) {
                throw new Exception($vali);
            }
            $data['limit'] = (int)$data['limit'];
            $data['offset'] = (int)$data['offset'];
            $map = [];
            if (!empty($data['search'])) {
                $map[] = [
                    'title','like','%'.$data['search'].'%'
                ];
            }

            $list = AuthRuleModel::where($map)->limit($data['offset'],$data['limit'])
                ->order(['weigh' => 'desc','id' => 'desc'])->select();
            $total = AuthRuleModel::where($map)->count('id');

            return json([
                'rows' => $list,
                'total' => $total,
            ]);
        }

        $this->view();

    }

    public function add() {
        $request = $this->request;
        if ($request->isPost()) {
            $data = $request->post();
            $validate = new Validate();
            $validate->rule([
                'type' => ValidateRule::isRequire(null,'规则类型必选！')->isInteger(null,'规则类型格式必须是整数！'),
                'pid' => ValidateRule::isRequire(null,'父级必选！')->isInteger(null,'父级格式必须是整数！'),
                'title' => ValidateRule::isRequire(null,'标题必填！'),
                'icon' => ValidateRule::isRequire(null,'图标必填！'),
                'name' => ValidateRule::requireIf('type,'.AppConstant::RULE_TYPE_OTHER,'规则类型为其它时，规则必填！'),
                'weigh' => ValidateRule::isRequire(null,'权重必填！')->isInteger(null,'权重格式必须是整数！'),
                'status' => ValidateRule::isRequire(null,'状态必填！')->isInteger(null,'排序格式必须是整数！'),
            ]);

            if (!$validate->check($data)) {
                error($validate->getError());
            }

            $res = AuthRuleModel::where([
                'title' => $data['title'],
                'name' => $data['name'],
            ])->find();
            if ($res) {
                error('该规则名称和标题已存在！');
            }
            $insertData = [
                'type' => $data['type'],
                'pid' => $data['pid'],
                'title' => $data['title'],
                'icon' => $data['icon'],
                'name' => $data['name'],
                'weigh' => $data['weigh'],
                'status' => $data['status'],
            ];
            $AuthRuleModelModel = new AuthRuleModel();
            $AuthRuleModelModel->save($insertData);
            success('添加成功！');
        }
        $ruleTypeList = AppConstant::getRuleTypeList();

        $statusList = AppConstant::getStatusList();

        $parent = AuthRuleModel::where(['type' => AppConstant::RULE_TYPE_MENU])->column('title','id');
        $parent[0] = '无';
        $max = AuthRuleModel::max('weigh');
        $max++;
        return $this->view('',[
            'ruleTypeList' => $ruleTypeList,
            'statusList' => $statusList,
            'parentList' => $parent,
            'defaultWeigh' => $max,
            'ruleTypeVal' => AppConstant::RULE_TYPE_MENU,
            'parent' => 0,
            'defaultStatus' => AppConstant::LOGIN_STATUS_NORMAL,
        ]);

    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $request = $this->request;
        if ($request->isGet()){

            $currentMenu = AuthRuleModel::find($id);
            if (!$currentMenu){
                error('该条规则信息不存在！');
            }
            //获取菜单列表
            $menu = AuthRuleModel::whereOr([
                ['id','>',$id],
                ['id','<',$id],
            ])->column('title','id');

            $parentList = $menu;
            $parentList[0] = '无';

            $menu = null;
            $ruleTypeList = AppConstant::getRuleTypeList();
            $statusList = AppConstant::getStatusList();
            $this->view('',[
                'row' => $currentMenu,
                'ruleTypeList' => $ruleTypeList,
                'statusList' => $statusList,
                'parentList' => $parentList,
            ]);

        } elseif ($request->isPost()) {
            $data = $request->post();
            $validate = new Validate();
            $validate->rule([
                'id' => ValidateRule::isRequire(null,'规则ID必传！')->isInteger(null,'规则ID格式必须是整数！'),
                'type' => ValidateRule::isRequire(null,'规则类型必选！')->isInteger(null,'规则类型格式必须是整数！'),
                'pid' => ValidateRule::isRequire(null,'父级必选！')->isInteger(null,'父级格式必须是整数！'),
                'title' => ValidateRule::isRequire(null,'标题必填！'),
                'icon' => ValidateRule::isRequire(null,'图标必填！'),
                'name' => ValidateRule::requireIf('name,'.AppConstant::RULE_TYPE_OTHER,'规则类型为其它时，规则必填！'),
                'weigh' => ValidateRule::isRequire(null,'权重必填！')->isInteger(null,'权重格式必须是整数！'),
                'status' => ValidateRule::isRequire(null,'状态必填！')->isInteger(null,'状态格式必须是整数！'),
            ]);

            if (!$validate->check($data)) {
                error($validate->getError());
            }

            $updateData = [
                'id' => $data['id'],
                'type' => $data['type'],
                'pid' => $data['pid'],
                'title' => $data['title'],
                'icon' => $data['icon'],
                'name' => $data['name'],
                'weigh' => $data['weigh'],
                'status' => $data['status'],
            ];

            isset($data['rule_condition']) && $updateData['condition'] = $data['rule_condition'];
            AuthRuleModel::update($updateData);
            success('修改成功！',[],url('index')->build());
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
        $id = explode(',',$id);
        $model = new AuthRuleModel;

        $primaryKey = $model->getPk();
        Db::startTrans();
        AuthRuleModel::where([
            [$primaryKey,'in',$id],
        ])->whereOr(['pid' => $id])->delete();
        Db::commit();
        success('成功删除'.count($id).'条数据',[]);
    }
}
