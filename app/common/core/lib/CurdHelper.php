<?php


namespace app\common\core\lib;

use app\admin\model\MenuRule as AuthRuleModel;
use constant\AppConstant;
use think\Validate;
use think\validate\ValidateRule;

trait CurdHelper
{
    protected $model = null;

    /**
     * 根据搜索字段的操作符获取用于查询条件的搜索文本
     * @param string $operator
     * @param string $value
     * @return string
     */
    private function getTextByOperator($operator = '', $value = '') {
        return $operator === 'like' ? '%'.$value.'%' : $value;
    }
    public function index() {
        $request = $this->request;
        if ($request->isAjax() && $request->isGet()) {
            $data = $request->get();
            $vali = $this->validate($data,[
                'offset|偏移量' => 'require|integer',
                'limit|记录数' => 'require|integer',
            ]);
            if ($vali !== true) {
                throw new \Exception($vali);
            }
            $data['limit'] = (int)$data['limit'];
            $data['offset'] = (int)$data['offset'];
            $map = [];
            if (!empty($data['search'])) {
                $map[] = [
                    $this->searchField,$this->operator,$this->getTextByOperator($this->operator,$data['search'])
                ];
            }
            $primaryKey = $this->model->getPk();
            $sort = empty($data['sort']) ? $primaryKey : $data['sort'];
            $order = empty($data['order']) ? 'desc' : $data['order'];
            $list = $this->model->where($map)->limit($data['offset'],$data['limit'])
                ->order($sort,$order)->select();
            $total = $this->model->where($map)->count($primaryKey);

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
}