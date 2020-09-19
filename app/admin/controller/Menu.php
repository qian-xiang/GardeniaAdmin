<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\GardeniaController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;
use think\Validate;
use think\validate\ValidateRule;

class Menu extends GardeniaController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = request();
//            ,height: 312
//            ,url: '/admin.php/index/getData' //数据接口
        $gardeniaList = new GardeniaList('id');
        $gardeniaList
//            ->setHeadToolbox('#toolbarDemo','path','./static/js/gardenia/text_template.js')
            ->setTableAttr('url',url('/'.$request->controller().'/getData')->build())
            ->setTableAttr('page',true)
            ->addExtraLayuiJS('path','./static/js/gardenia/list_extra_layui.js')
            ->addListHead('choose','选择','checkbox')
//            ->addListHead('id','ID')
            ->addListHead('title','标题')
            ->addListHead('type','规则类型')
            ->addListHead('icon','图标')
            ->addListHead('name','规则')
            ->addListHead('weigh','权重')
            ->addListHead('status','状态')
//            ->addListHead('operate','操作','normal','#rightToolbox',$template)
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
        $request = request();
        if ($request->isGet()){
            $ruleTypeList = [
                ['label'=> '菜单', 'value' => 0, 'selected'=> true],
                ['label'=> '其它', 'value' => 1],
            ];
            $initParent = [
                ['label'=> '无', 'value' => 0],
            ];
            $statusList = [
                ['label'=> '禁用', 'value' => 0],
                ['label'=> '正常', 'value' => 1, 'selected'=> true],
            ];

            $parent = Db::name('auth_rule')->where(['type' => AppConstant::RULE_TYPE_MENU])->field('id as value,title as label')->select()->toArray();
            $parent = array_merge($initParent,$parent);

            $max = Db::name('auth_rule')->max('weigh');
            $max++;

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','select','rule_type','规则类型',$ruleTypeList)
                ->addFormItem('gardenia','select','pid','父级',$parent)
                ->addFormItem('gardenia','text','title','标题')
                ->addFormItem('gardenia','text','icon','图标',null,['value' => 'icon-menu'])
                ->addFormItem('gardenia','text','rule','规则',null,['placeholder' => '根目录/控制器名/行为名称，如/Menu/index，其中/不可省略'])
                ->addFormItem('gardenia','text','rule_condition','规则条件')
                ->addFormItem('gardenia','number','weigh','权重',null,['value' => $max])
                ->addFormItem('gardenia','select','status','状态',$statusList)
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->display();
        } elseif ($request->isPost()) {
            $data = $_POST;
            $validate = new Validate();
            $validate->rule([
                'rule_type' => ValidateRule::isRequire(null,'规则类型必选！')->isInteger(null,'规则类型格式必须是整数！'),
                'pid' => ValidateRule::isRequire(null,'父级必选！')->isInteger(null,'父级格式必须是整数！'),
                'title' => ValidateRule::isRequire(null,'标题必填！'),
                'icon' => ValidateRule::isRequire(null,'图标必填！'),
                'rule' => ValidateRule::requireIf('rule_type,'.AppConstant::RULE_TYPE_OTHER,'规则类型为其它时，规则必填！'),
                'weigh' => ValidateRule::isRequire(null,'权重必填！')->isInteger(null,'权重格式必须是整数！'),
                'status' => ValidateRule::isRequire(null,'状态必填！')->isInteger(null,'排序格式必须是整数！'),
            ]);

            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
//
//            if ($data['rule_type'] === AppConstant::RULE_TYPE_OTHER){
//                !isset($data['rule']) && $this->error('规则类型为其它时，规则必填！');
//            }

            $res = Db::name('auth_rule')->where([
                'title' => $data['title'],
                'name' => $data['rule'],
                ])->find();
            if ($res) {
                $this->error('该规则名称和标题已存在！');
            }
            $insertData = [
                'type' => $data['rule_type'],
                'pid' => $data['pid'],
                'title' => $data['title'],
                'icon' => $data['icon'],
                'name' => $data['rule'],
                'weigh' => $data['weigh'],
                'status' => $data['status'],
            ];

            isset($data['rule_condition']) && $insertData['condition'] = $data['rule_condition'];
            if ((int)$data['pid'] === 0){
                $insertData['root_id'] = 0;
            } else {
                $res = Db::name('auth_rule')->where(['id' => $data['pid']])->field('root_id')->find();
                if (!$res){
                    $this->error('该父级规则不存在，或已被删除！');
                }
                $insertData['root_id'] = $res['root_id'] === 0 ? $data['pid'] : $res['root_id'];
            }

            $res = Db::name('auth_rule')->save($insertData);
            if (!$res){
                $this->error('添加规则失败，请稍候重试。');
            }

            $this->success('添加成功！',url('/'.$request->controller()));

        } else {
            $this->error('访问方式非法！');
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
        $request = request();
        if ($request->isGet()){

            $parent = [
                ['label'=> '无', 'value' => 0],
            ];

            $currentMenu = Db::name('auth_rule')->where(['id'=> $id])->find();
            if (!$currentMenu){
                $this->error('该条规则信息不存在！');
            }
            //获取菜单列表
            $menu = Db::name('auth_rule')->whereOr([
                ['id','>',$id],
                ['id','<',$id],
            ])->select()->toArray();
            if (!$menu) {
                //所有规则信息均不存在时，则有默认数组成员  无
                $menu =  [];
            }
            $authList = [];

            if ($menu) {
                foreach ($menu as $item) {
                    $temp = [
                        'label' => $item['title'],
                        'value' => $item['id']
                    ];

                    $temp['selected'] = $currentMenu['pid'] === $item['id'];
                    $authList[] = $temp;
                }
            }

            $parent[0]['selected'] = $currentMenu['pid'] === 0;

            $menu = null;
            $ruleTypeList = [
                ['label'=> '菜单', 'value' => AppConstant::RULE_TYPE_MENU, 'selected' => $currentMenu['type'] === AppConstant::RULE_TYPE_MENU],
                ['label'=> '其它', 'value' => AppConstant::RULE_TYPE_OTHER, 'selected' => $currentMenu['type'] === AppConstant::RULE_TYPE_OTHER],
            ];
            $statusList = [
                ['label'=> '禁用', 'value' => AppConstant::STATUS_FORBID, 'selected' => $currentMenu['status'] === AppConstant::STATUS_FORBID],
                ['label'=> '正常', 'value' => AppConstant::STATUS_FORMAL, 'selected' => $currentMenu['status'] === AppConstant::STATUS_FORMAL],
            ];
            $parent = array_merge($parent,$authList);

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','select','rule_type','规则类型',$ruleTypeList,['disabled' => 'disabled'])
                ->addFormItem('gardenia','hidden','id','规则ID',null,['value' => $id])
                ->addFormItem('gardenia','select','pid','父级',$parent,['disabled' => 'disabled'])
                ->addFormItem('gardenia','text','title','标题',null,['value'=> $currentMenu['title'], 'readonly' => 'readonly'])
                ->addFormItem('gardenia','text','icon','图标',null,['value' => $currentMenu['icon'], 'readonly' => 'readonly'])
                ->addFormItem('gardenia','text','rule','规则',null,['value' => $currentMenu['name'], 'readonly' => 'readonly'])
                ->addFormItem('gardenia','text','rule_condition','规则条件',null,['readonly' => 'readonly','value' => $currentMenu['condition']])
                ->addFormItem('gardenia','number','sort','排序',null,['value' => $currentMenu['sort'], 'readonly' => 'readonly'])
                ->addFormItem('gardenia','select','status','状态',$statusList,['disabled' => 'disabled'])
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
        $request = request();
        if ($request->isGet()){

            $parent = [
                ['label'=> '无', 'value' => 0],
            ];

            $currentMenu = Db::name('auth_rule')->where(['id'=> $id])->find();
            if (!$currentMenu){
                $this->error('该条规则信息不存在！');
            }
            //获取菜单列表
            $menu = Db::name('auth_rule')->whereOr([
                ['id','>',$id],
                ['id','<',$id],
            ])->select()->toArray();
            if (!$menu) {
                //所有规则信息均不存在时，则有默认数组成员  无
                $menu =  [];
            }
            $authList = [];

            if ($menu) {
                foreach ($menu as $item) {
                    $temp = [
                        'label' => $item['title'],
                        'value' => $item['id']
                    ];

                    $temp['selected'] = $currentMenu['pid'] === $item['id'];
                    $authList[] = $temp;
                }
            }

            $parent[0]['selected'] = $currentMenu['pid'] === 0;

            $menu = null;
            $ruleTypeList = [
                ['label'=> '菜单', 'value' => AppConstant::RULE_TYPE_MENU, 'selected' => $currentMenu['type'] === AppConstant::RULE_TYPE_MENU],
                ['label'=> '其它', 'value' => AppConstant::RULE_TYPE_OTHER, 'selected' => $currentMenu['type'] === AppConstant::RULE_TYPE_OTHER],
            ];
            $statusList = [
                ['label'=> '禁用', 'value' => AppConstant::STATUS_FORBID, 'selected' => $currentMenu['status'] === AppConstant::STATUS_FORBID],
                ['label'=> '正常', 'value' => AppConstant::STATUS_FORMAL, 'selected' => $currentMenu['status'] === AppConstant::STATUS_FORMAL],
            ];
            $parent = array_merge($parent,$authList);

            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','select','rule_type','规则类型',$ruleTypeList)
                ->addFormItem('gardenia','hidden','id','规则ID',null,['value' => $id])
                ->addFormItem('gardenia','select','pid','父级',$parent)
                ->addFormItem('gardenia','text','title','标题',null,['value'=> $currentMenu['title']])
                ->addFormItem('gardenia','text','icon','图标',null,['value' => $currentMenu['icon']])
                ->addFormItem('gardenia','text','rule','规则',null,['value' => $currentMenu['name']])
                ->addFormItem('gardenia','text','rule_condition','规则条件',null,['value' => $currentMenu['condition']])
                ->addFormItem('gardenia','number','sort','排序',null,['value' => $currentMenu['sort']])
                ->addFormItem('gardenia','select','status','状态',$statusList)
                ->addBottomButton('gardenia','submit','submit','提交')
                ->addBottomButton('gardenia','cancel','cancel','取消')
                ->display();
        } elseif ($request->isPost()) {
            $data = $_POST;
            $validate = new Validate();
            $validate->rule([
                'id' => ValidateRule::isRequire(null,'规则ID必传！')->isInteger(null,'规则ID格式必须是整数！'),
                'rule_type' => ValidateRule::isRequire(null,'规则类型必选！')->isInteger(null,'规则类型格式必须是整数！'),
                'pid' => ValidateRule::isRequire(null,'父级必选！')->isInteger(null,'父级格式必须是整数！'),
                'title' => ValidateRule::isRequire(null,'标题必填！'),
                'icon' => ValidateRule::isRequire(null,'图标必填！'),
                'rule' => ValidateRule::requireIf('rule_type,'.AppConstant::RULE_TYPE_OTHER,'规则类型为其它时，规则必填！'),
                'sort' => ValidateRule::isRequire(null,'排序必填！')->isInteger(null,'排序格式必须是整数！'),
                'status' => ValidateRule::isRequire(null,'状态必填！')->isInteger(null,'排序格式必须是整数！'),
            ]);

            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $updateData = [
                'id' => $data['id'],
                'type' => $data['rule_type'],
                'pid' => $data['pid'],
                'title' => $data['title'],
                'icon' => $data['icon'],
                'name' => $data['rule'],
                'sort' => $data['sort'],
                'status' => $data['status'],
            ];

            isset($data['rule_condition']) && $updateData['condition'] = $data['rule_condition'];
            if ((int)$data['pid'] === 0){
                $updateData['root_id'] = 0;
            } else {
                $res = Db::name('auth_rule')->where(['id' => $data['pid']])->field('root_id')->find();
                if (!$res){
                    $this->error('该父级规则不存在，或已被删除！');
                }
                $updateData['root_id'] = $res['root_id'] === 0 ? $data['pid'] : $res['root_id'];
            }

            $res = Db::name('auth_rule')->save($updateData);
            if (!$res){
                $this->error('修改规则失败，请稍候重试。');
            }

            $this->success('修改成功！',url('/'.$request->controller()));

        } else {
            $this->error('访问方式非法！');
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
        $res = Db::name('auth_rule')->where([
            ['id','=',$id],
        ])->whereOr(['root_id' => $id])->whereOr(['pid' => $id])->delete();
        if (!$res){
            return $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'删除失败');
        }
        return $this->layuiAjaxReturn(AppConstant::CODE_SUCCESS,'删除成功','',url('/'.$request->controller())->build());
    }
    public function getData() {
        $list = Db::name('auth_rule')
            ->withAttr('status',function ($value) {
                return AppConstant::getStatusAttr($value);
            })
            ->withAttr('type',function ($value) {
                return AppConstant::getRuleTypeAttr($value);
            })->order(['weigh' => 'desc','id' => 'desc'])->select()->toArray();
        $data = [
            'code' => AppConstant::CODE_SUCCESS,
            'msg' => '获取成功！',
            'count' => count($list),
            'data' => $list
        ];

        return response($data,200,[],'json');
    }

}
