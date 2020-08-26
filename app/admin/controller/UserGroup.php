<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\extend\diy\extra_class\AppConstant;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\facade\Db;
use think\Request;

class UserGroup extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $gardeniaList = new GardeniaList();
        $gardeniaList->setTableAttr('url',url('/UserGroup/getData')->build())
            ->addListHead('group_name','用户组')
            ->addListHead('status','状态')
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
        $nodeList = [
            [
                'id' => 1,
                'field' => 'category',
                'title' => '分类',
                'spread' => true,
                'children' => [
                    [
                        'id' => 1,
                        'field' => 'category',
                        'title' => '新闻',
                        'spread' => true,
                    ],
                    [
                        'id' => 2,
                        'field' => 'category',
                        'title' => '媒体',
                        'spread' => true,
                    ]
                ]
            ],
            [
                'id' => 2,
                'field' => 'category',
                'title' => '管理',
                'spread' => true,
                'children' => [
                    [
                        'id' => 1,
                        'field' => 'category',
                        'title' => '新闻管理',
                        'spread' => true,
                    ],
                    [
                        'id' => 3,
                        'field' => 'category',
                        'title' => '内容管理',
                        'spread' => true,
                    ],
                ]
            ]
        ];

        $statusList = [
            ['label'=> '禁用', 'value' => 0],
            ['label'=> '正常', 'value' => 1,'selected' => 'selected'],
        ];

        $gardeniaForm = new GardeniaForm();
        $gardeniaForm->addFormItem('gardenia','text','title','用户名',null,null,true)
            ->addFormItem('gardenia','select','status','状态',$statusList,null,true)
            ->addFormItem('gardenia','tree','rule','规则',$nodeList,null,true)
            ->addBottomButton('gardenia','submit','submit','提交')
            ->addBottomButton('gardenia','cancel','cancel','取消')
            ->display();
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
    }
    public function getData() {
        $list = Db::name('auth_group')
            ->withAttr('status',function ($value) {
                return AppConstant::getStatusAttr($value);
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
