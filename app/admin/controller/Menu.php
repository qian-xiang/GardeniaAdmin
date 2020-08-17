<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\BaseController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;
use think\Validate;
use think\validate\ValidateRule;

class Menu extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {

//            ,height: 312
//            ,url: '/admin.php/index/getData' //数据接口
        $gardeniaList = new GardeniaList('id');
        $gardeniaList
//            ->setHeadToolbox('#toolbarDemo','path','./static/js/gardenia/text_template.js')
            ->setTableAttr('url','/admin.php/Menu/getData')
            ->setTableAttr('page',true)
            ->addExtraLayuiJS('path','./static/js/gardenia/list_extra_layui.js')
            ->addListHead('choose','选择','checkbox')
//            ->addListHead('id','ID')
            ->addListHead('title','标题')
            ->addListHead('type','规则类型')
            ->addListHead('icon','图标')
            ->addListHead('name','规则')
            ->addListHead('sort','排序')
            ->addListHead('status','状态')
//            ->addListHead('operate','操作','normal','#rightToolbox',$template)
            ->addListHead('operate','操作','normal')
            ->addTopOperateButton('gardenia','新增','create',['id'=> 'create',
                'onclick'=> 'location.href="'.url('/'.request()->controller().'/create').'"'])
            ->addTopOperateButton('gardenia','删除','delete',['id'=> 'delete'])
            ->addColumnOperateButton('operate','查看','gardenia','read',['name'=> "item_read",'lay-event' => 'read'],['rule-name' => 'item_read'])
            ->addColumnOperateButton('operate','编辑','gardenia','edit',['name'=> "item_edit",'lay-event' => 'edit'],[
                'rule-name' => 'item_edit','redirect-url' => url('/'.request()->controller().'/edit')])
            ->addColumnOperateButton('operate','删除','gardenia','delete',['name' => 'item_delete','lay-event' => 'delete'],['rule-name' => 'item_delete'])
//            ->setDeleteTip('删除有风险，你确定删除么？')
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
                ['label'=> '菜单', 'value' => 0],
                ['label'=> '其它', 'value' => 1],
            ];
            $parent = [
                ['label'=> '无', 'value' => 0],
            ];
            $statusList = [
                ['label'=> '禁用', 'value' => 0],
                ['label'=> '正常', 'value' => 1],
            ];
            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','select','rule_type','规则类型',$ruleTypeList)
                ->addFormItem('gardenia','select','pid','父级',$parent)
                ->addFormItem('gardenia','text','title','标题')
                ->addFormItem('gardenia','text','icon','图标',null,['value' => 'icon-menu'])
                ->addFormItem('gardenia','text','rule','规则',null,['placeholder' => '根目录/控制器名/行为名称，如/Menu/index，其中/不可省略'])
                ->addFormItem('gardenia','text','rule_condition','规则条件')
                ->addFormItem('gardenia','number','sort','排序',null,['value' => 0])
                ->addFormItem('gardenia','select','status','状态',$statusList)
                ->addBottomButton('gardenia','submit','submit','提交')
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
                'sort' => ValidateRule::isRequire(null,'排序必填！')->isInteger(null,'排序格式必须是整数！'),
                'status' => ValidateRule::isRequire(null,'状态必填！')->isInteger(null,'排序格式必须是整数！'),
            ]);

            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
//
//            if ($data['rule_type'] === AppConstant::RULE_TYPE_OTHER){
//                !isset($data['rule']) && $this->error('规则类型为其它时，规则必填！');
//            }

            $insertData = [
                'type' => $data['rule_type'],
                'pid' => $data['pid'],
                'title' => $data['title'],
                'icon' => $data['icon'],
                'name' => $data['rule'],
                'sort' => $data['sort'],
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
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
        return 0;
//        $gardeniaForm = new GardeniaForm();
//        $gardeniaForm->addFormItem('username','用户名','gardenia','text',null,['style'=> 'width: fit-content;'])
//            ->addFormItem('password','密码','normal','input',null,['placeholder' => '请输入密码'])
//            ->display();
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
        !isset($id) && $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'id必传');
        $res = Db::name('auth_rule')->where([
            ['id','=',$id],
        ])->whereOr(['root_id' => $id])->whereOr(['pid' => $id])->delete();
        if (!$res){
            return $this->layuiAjaxReturn(AppConstant::CODE_ERROR,'删除失败');
        }
        return $this->layuiAjaxReturn(AppConstant::CODE_SUCCESS,'删除成功');
    }
    public function getData() {
        $list = Db::name('auth_rule')
            ->withAttr('status',function ($value) {
                return AppConstant::getStatusAttr($value);
            })
            ->withAttr('type',function ($value) {
                return AppConstant::getRuleTypeAttr($value);
            })->order(['sort' => 'asc','id' => 'desc'])->select()->toArray();
        $data = [
            'code' => AppConstant::CODE_SUCCESS,
            'msg' => '获取成功！',
            'count' => count($list),
            'data' => $list
        ];

        return response($data,200,[],'json');
    }

}
