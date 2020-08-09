<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\extend\diy\extra_class\AppConstant;
use app\admin\BaseController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;


class Menu extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $template = "<script type=\"text/html\" id=\"rightToolbox\">
  <button class=\"layui-btn layui-bg-blue layui-btn-sm\">查看</button>
  <button class=\"layui-btn layui-bg-green layui-btn-sm\">编辑</button>
  <button class=\"layui-btn layui-bg-red layui-btn-sm\">删除</button>
</script>";

//        $headTemplate = "<script type=\"text/html\" id=\"toolbarDemo\">
//  <button class=\"layui-btn layui-bg-blue layui-btn-sm\" lay-event=\"add\">新增</button>
//</script>";

//            ,height: 312
//            ,url: '/admin.php/index/getData' //数据接口
        $gardeniaList = new GardeniaList();
        $gardeniaList
//            ->setHeadToolbox('#toolbarDemo','path','./static/js/gardenia/text_template.js')
            ->setTableAttr('url','/admin.php/index/getData')
            ->setTableAttr('page',true)
            ->addExtraLayuiJS('path','./static/js/gardenia/list_extra_layui.js')
            ->addListHead('choose','选择','checkbox')
            ->addListHead('id','ID')
            ->addListHead('username','用户名')
            ->addListHead('login_code','登录标识')
            ->addListHead('login_status','状态')
//            ->addListHead('operate','操作','normal','#rightToolbox',$template)
            ->addListHead('operate','操作','normal')
            ->addTopOperateButton('gardenia','新增','create',['id'=> 'create',
                'onclick'=> 'location.href="'.url('/'.request()->controller().'/create').'"'])
            ->addTopOperateButton('gardenia','删除','delete',['id'=> 'delete'])
            ->addColumnOperateButton('operate','编辑','gardenia','edit',['onclick'=> "location.href= '/admin.php/menu/edit/id/2'"])
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
                ['label'=> '菜单', 'value' => 'menu'],
                ['label'=> '其它', 'value' => 'other'],
            ];
            $parentCategory = [
                ['label'=> '无', 'value' => '/'],
            ];
            $statusList = [
                ['label'=> '禁用', 'value' => 0],
                ['label'=> '正常', 'value' => 1],
            ];
            $gardeniaForm = new GardeniaForm();
            $gardeniaForm->addFormItem('gardenia','select','rule_type','规则类型',$ruleTypeList)
                ->addFormItem('gardenia','select','category','父级分类',$parentCategory)
                ->addFormItem('gardenia','text','title','标题')
                ->addFormItem('gardenia','text','icon','图标')
                ->addFormItem('gardenia','text','rule','规则',null,['placeholder' => '根目录/控制器名/行为名称，如/Menu/index，其中/不可省略'])
                ->addFormItem('gardenia','text','rule_condition','规则条件')
                ->addFormItem('gardenia','number','sort','排序',null,['value' => 0])
                ->addFormItem('gardenia','select','status','状态',$statusList)
                ->addBottomButton('gardenia','submit','submit','提交')
                ->display();
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
        $gardeniaForm = new GardeniaForm();
        $gardeniaForm->addFormItem('username','用户名','gardenia','text',null,['style'=> 'width: fit-content;'])
            ->addFormItem('password','密码','normal','input',null,['placeholder' => '请输入密码'])
            ->display();
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
        //
        return $this->layuiAjaxReturn(1,'删除成功');
    }
    public function getData() {
        $list = Db::name(AppConstant::TABLE_USER)
            ->withAttr('login_status',function ($value){
                return AppConstant::getStatusAttr($value);
            })->select()->toArray();

        $data = [
            'code' => AppConstant::CODE_SUCCESS,
            'msg' => '获取成功！',
            'count' => count($list),
            'data' => $list
        ];

        return response($data,200,[],'json');
    }

}
