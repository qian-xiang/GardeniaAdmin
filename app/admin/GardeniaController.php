<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
declare (strict_types = 1);

namespace app\admin;

use app\admin\extend\diy\extra_class\AppConstant;
use think\App;
use think\exception\ValidateException;
use think\facade\View;
use think\Validate;
use think\facade\Db;

abstract class GardeniaController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        //
        View::assign('asideMenuList',$this->getRenderMenuList());
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }
    /**
     * 以layui请求数据返回的格式来返回数据
     * @param $code
     * @param $msg
     * @param string $data
     * @param string $redirectUrl
     * @return \think\Response
     */
    protected function layuiAjaxReturn($code,$msg,$data = '',$redirectUrl = '') {
        response([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'redirectUrl' => $redirectUrl,
        ],200,[],'json')->send();
    }
    protected function success($content = '',$redirectUrl = null,$second = 3) {
        $redirectUrl = $redirectUrl === null ? url('/'.request()->controller()) : $redirectUrl;
        view('common/success',[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ])->send();
    }
    protected function error($content = '',$redirectUrl = null,$second = 3) {
        $redirectUrl = $redirectUrl === null ? $this->request->header('referer') : $redirectUrl;
        view('common/error',[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ])->send();
    }
    protected function getRenderMenuList() {
        $request = request();
        $controller = $request->controller();
        $action = $request->action();
        if ($controller === config('route.default_controller') &&
            $action === config('route.default_action')){

            $menuUrl = '/';
        } else {
            $menuUrl = '/'.$controller.'/'.$action;
        }
        $ruleList = Db::name('auth_group_access')->alias('a')->join('auth_group g','g.id = a.group_id')
            ->where(['a.uid' => $this->request->user['id'],'g.status'=> AppConstant::STATUS_FORMAL])
            ->value('g.rules');
        $ruleList = Db::name('auth_rule')->where('id','in',$ruleList)
            ->where([
                'status'=> AppConstant::STATUS_FORMAL,
                'type' => AppConstant::RULE_TYPE_MENU,
            ])
            ->field('id,title,pid,name as field,root_id')->order('id','desc')->select()->toArray();
        if (!$ruleList){
            error('您没有权限访问');
        }
        $currentMenuId = 0;
        $rootId = 0;
        foreach ($ruleList as $item) {
            if ($item['field'] === $menuUrl){
                $currentMenuId = $item['id'];
                $rootId = $item['root_id'] === 0 ? $item['id'] : $item['root_id'];
                break;
            }
        }

        $nodeList = [];
        if ($ruleList){
            $nodeList = $this->getIndexTreeMenu($ruleList,0,$currentMenuId,$rootId);
        }
        return $nodeList;
    }
    protected function buildTreeData($ruleList,$pid,$checkData = [],$currentLevel = 0,$maxLevel = 0) {
        $treeData = [];
        foreach ($ruleList as $key => $item){
            if ($maxLevel && $currentLevel === $maxLevel){
                return $treeData;
            }
            if ($item['pid'] === $pid) {
                unset($ruleList[$key]);
                $result = $this->buildTreeData($ruleList,$item['id'],$checkData,$currentLevel,$maxLevel);
                if ($result){
                    $item['children'] = $result;
                }
                $item['spread'] = true;
                $item['checked'] = in_array($item['id'],$checkData);
                $treeData[] = $item;
            }
        }
        return $treeData;
    }
    protected function getIndexTreeMenu($ruleList,$pid,$currentMenuId,$rootId,$currentLevel = 0,$maxLevel = 0) {
        $treeData = [];
        foreach ($ruleList as $key => $item){
            if ($maxLevel && $currentLevel === $maxLevel){
                return $treeData;
            }

            if ($item['pid'] === $pid) {
                unset($ruleList[$key]);
                $result = $this->getIndexTreeMenu($ruleList,$item['id'],$currentMenuId,$rootId,$currentLevel,$maxLevel);
                if ($result){
                    $item['children'] = $result;
                }
                $item['spread'] = $rootId === $currentMenuId ? false : ($item['root_id'] === $rootId || $item['id'] === $rootId);
                $item['active'] = $item['id'] === $currentMenuId;
                $treeData[] = $item;
            }
        }
        return $treeData;
    }

}