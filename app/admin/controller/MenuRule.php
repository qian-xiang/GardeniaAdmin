<?php
declare (strict_types = 1);

namespace app\admin\controller;

use constant\AppConstant;
use app\admin\AdminController;
use gardenia_admin\src\core\core_class\GardeniaForm;
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
                ->order(['weigh' => 'desc','id' => 'desc'])->select()->toArray();
            $total = AuthRuleModel::where($map)->count('id');
            foreach ($list as &$item) {
                $item['create_time'] = 'https://interactive-examples.mdn.mozilla.net/media/examples/plumeria.jpg';
            }
            return json([
                'rows' => $list,
                'total' => $total,
            ]);
        }

        return $this->view();

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
                error_json($validate->getError());
            }

            $res = AuthRuleModel::where([
                'title' => $data['title'],
                'name' => $data['name'],
            ])->find();
            if ($res) {
                error_json('该规则名称和标题已存在！');
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

            if ((int)$data['pid'] === 0){
                $insertData['root_id'] = 0;
            } else {
                $res = AuthRuleModel::where(['id' => $data['pid']])->field('root_id')->find();
                if (!$res){
                    error_json('该父级规则不存在，或已被删除！');
                }
                $insertData['root_id'] = $res['root_id'] === 0 ? $data['pid'] : $res['root_id'];
            }
            $AuthRuleModelModel = new AuthRuleModel();
            $res = $AuthRuleModelModel->save($insertData);
            if (!$res){
                error_json('添加规则失败，请稍候重试。');
            }

            success_json('添加成功！');
        }
        $ruleTypeList = AppConstant::getRuleTypeList();

        $statusList = AppConstant::getStatusList();

        $parent = Db::name('auth_rule')->where(['type' => AppConstant::RULE_TYPE_MENU])->column('title','id');
        $parent[0] = '无';
        $max = Db::name('auth_rule')->max('weigh');
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
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $request = request();
        if ($request->isGet()){

            $currentMenu = Db::name('auth_rule')->where(['id'=> $id])->find();
            if (!$currentMenu){
                $this->error('该条规则信息不存在！');
            }
            //获取菜单列表
            $menu = Db::name('auth_rule')->whereOr([
                ['id','>',$id],
                ['id','<',$id],
            ])->column('title','id');

            $parent = $menu;

            $parent[0] = '无';

            $menu = null;
            $ruleTypeList = AppConstant::getRuleTypeList();
            $statusList = AppConstant::getStatusList();

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','select','rule_type','规则类型',$ruleTypeList,[
                'disabled' => 'disabled',
                'value' => $currentMenu['type']
            ])
                ->addFormItem('gardenia','hidden','id','规则ID',null,['value' => $id])
                ->addFormItem('gardenia','select','pid','父级',$parent,[
                    'disabled' => 'disabled',
                    'value' => $currentMenu['pid']
                ])
                ->addFormItem('gardenia','text','title','标题',null,['value'=> $currentMenu['title'], 'readonly' => 'readonly'])
                ->addFormItem('gardenia','text','icon','图标',null,['value' => $currentMenu['icon'], 'readonly' => 'readonly'])
                ->addFormItem('gardenia','text','rule','规则',null,['value' => $currentMenu['name'], 'readonly' => 'readonly'])
                ->addFormItem('gardenia','text','rule_condition','规则条件',null,['readonly' => 'readonly','value' => $currentMenu['condition']])
                ->addFormItem('gardenia','number','weigh','权重',null,['value' => $currentMenu['weigh'], 'readonly' => 'readonly'])
                ->addFormItem('gardenia','select','status','状态',$statusList,[
                    'disabled' => 'disabled',
                    'value' => $currentMenu['status']
                ])
                ->addBottomButton('gardenia','cancel','cancel','取消')
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
        $request = $this->request;
        if ($request->isGet()){

            $currentMenu = AuthRuleModel::find($id);
            if (!$currentMenu){
                error_json('该条规则信息不存在！');
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
            return $this->view('',[
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
                error_json($validate->getError());
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
            if ((int)$data['pid'] === 0){
                $updateData['root_id'] = 0;
            } else {
                $res = AuthRuleModel::where(['id' => $data['pid']])->find();
                if (!$res){
                    error_json('该父级规则不存在，或已被删除！');
                }
                $updateData['root_id'] = $res['root_id'] === 0 ? $data['pid'] : $res['root_id'];
            }
            Db::startTrans();
            $res = AuthRuleModel::update($updateData);
            if ($res->getNumRows() !== 1){
                Db::rollback();
                error_json('修改规则失败，请稍候重试。');
            }
            Db::commit();
            success_json('修改成功！',[],url('index')->build());

        } else {
            error_json('访问方式非法！');
        }
    }

    /**
     * 删除指定资源
     *
     * @return \think\Response
     */
    public function del()
    {
        $request = $this->request;
        $id = $request->post('id',0);
        !isset($id) && error_json('id必传');
        $id = explode(',',$id);
        $primaryKey = Db::name('auth_rule')->getPk();
        Db::startTrans();
        $res = AuthRuleModel::where([
            [$primaryKey,'in',$id],
        ])->whereOr(['root_id' => $id])->whereOr(['pid' => $id])->delete();
        if (!$res){
            Db::rollback();
            error_json('删除失败');
        }
        Db::commit();
        success_json('成功删除'.count($id).'条数据',[]);
    }


}
