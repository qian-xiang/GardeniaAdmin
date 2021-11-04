<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
declare (strict_types = 1);

namespace app\admin;

use constant\AppConstant;
use app\admin\model\AuthGroupAccess;
use think\App;
use think\exception\ValidateException;
use think\facade\Lang;
use think\facade\View;
use think\Validate;
use think\facade\Db;
use gardenia_admin\src\core\core_class\GardeniaConstant;
use think\facade\Config;

abstract class AdminController
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

    //不需要校验是否已登录的action
    protected $noNeedLogin = [];
    //不需要校验是否拥有访问权限的action
    protected $noCheckAccess = [];
    //不需要校验防止重放攻击的接口token
//    protected $noCheckReqToken = [];
    protected $loadControllerLang = true;
    protected $langList = [];
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
//        $this->checkLogin();
//        $this->checkAccess();

        //加载控制器对应的多语言文件，请勿随意去除
        if ($this->loadControllerLang) {
            $lang = Lang::getLangSet();
            $controllerLangPath = app_path().'lang/'.$lang.'/'.$this->request->controller(true).'.php';
            $controllerLangList = [];
            if (file_exists($controllerLangPath)) {
                $zhCns = [$controllerLangPath];
                $controllerLangList = include $controllerLangPath;
            } else {
                $zhCns = [];
            }
            $config = config('lang.extend_list');
            $config[$lang] = $zhCns;
            Config::set(['extend_list' => $config],'lang');
            $this->app->LoadLangPack($lang);

            //读取指定多语言文件返回给前端
            $defaultLangSetPath = app_path().'lang/'.$lang.'.php';
            $langList = [];
            if (file_exists($defaultLangSetPath)) {
                $langList = include $defaultLangSetPath;
            }
            $langList = array_merge($langList,$controllerLangList);
            $this->langList = $langList;
        }
        $gardeniaLayout = [
            'left' => [
                'type' => GardeniaConstant::TEMPLATE_TYPE_CONTENT,
                'content' => null,
                'vars' => [],
            ],
            'right' => [
                'type' => GardeniaConstant::TEMPLATE_TYPE_CONTENT,
                'content' => null,
                'vars' => [],
            ],
        ];
        View::assign(GardeniaConstant::GARDENIA_PREFIX.'Layout',$gardeniaLayout);
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
        $request = $this->request;
        if (!$request->admin_info) {
            return [];
        }
        $controller = $request->controller();
        $action = $request->action();
        if ($controller === config('route.default_controller') &&
            $action === config('route.default_action')){

            $menuUrl = '/';
        } else {
            $menuUrl = '/'.$controller.'/'.$action;
        }
        if ($request->admin_info->authGroup->type === AppConstant::GROUP_TYPE_SUPER_ADMIN) {
            $ruleList = Db::name('auth_rule')
                ->where([
                    'status'=> AppConstant::STATUS_FORMAL,
                    'type' => AppConstant::RULE_TYPE_MENU,
                ])
                ->field('id,title,pid,name as field,root_id')->order('weigh','desc')->select()->toArray();
        } else {
//            $ruleList = Db::name('auth_group_access')->alias('a')->join('auth_group g','g.id = a.group_id')
//                ->where(['a.admin_id' => $this->request->admin_info->admin->id,'g.status'=> AppConstant::STATUS_FORMAL])
//                ->value('g.rules');
            $ruleList = Db::name('auth_rule')->where('id','in',$this->request->admin_info->authGroup->rules)
                ->where([
                    'status'=> AppConstant::STATUS_FORMAL,
                    'type' => AppConstant::RULE_TYPE_MENU,
                ])
                ->field('id,title,pid,name as field,root_id')->order('weigh','desc')->select()->toArray();
        }
        if (!$ruleList){
            error('您没有权限访问');
//            if (isset($this->request->accessWhiteList) && $this->request->accessWhiteList){
//                in_array($this->request->controller().'/'.$this->request->action(),$this->request->accessWhiteList) ? redirect($this->request->url()) : error('您没有权限访问');
//            }
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

        if (!$currentMenuId) {
            $currentMenuId = Db::name('auth_rule')->where([
                'name' => $menuUrl,
                'type' => AppConstant::RULE_TYPE_OTHER,
            ])->value('pid');

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
    protected function checkLogin() {
        $this->noNeedLogin = empty($this->noNeedLogin) ? [] : $this->noNeedLogin;

        if (in_array($this->request->action(true),$this->noNeedLogin) === false) {
            $loginCode = cookie('login_code');
            if (!$loginCode) {
                $this->error(lang('login.not'),url('/Login/index'));
            }
            $admin = AuthGroupAccess::hasWhere('admin',[
                'login_code' => $loginCode
            ])->cache(true)->find();
            if (!$admin) {
                $this->error(lang('login.not'),url('/Login/index'));
            }

            if ($admin->admin->login_code !== $loginCode) {
                $this->error(lang('login.not'),url('/Login/index'));
            }
            $this->request->admin_info = $admin;

        }
    }
    protected function checkAccess() {
        if (in_array($this->request->action(),$this->noNeedLogin) !== false) {
            return ;
        }
        $request = $this->request;
        $server = $request->server();
        $pathInfo = $server['PATH_INFO'];

        if (strpos($pathInfo,'.'.config('view.view_suffix')) !== false){
            $pathInfo = explode('.',$pathInfo);
            $pathInfo = $pathInfo[0];
        }
        $controller = '';
        $action = '';
        if (!$pathInfo) {
            $pathInfo = ['',config('route.default_controller'),config('route.default_action')];
            $controller = config('route.default_controller');
            $action = config('route.default_action');
        } else if ($pathInfo !== '/'){
            $pathInfo = explode('/',$pathInfo);
            $controller = isset($pathInfo[1]) ? $pathInfo[1] : config('route.default_controller');
            $action = isset($pathInfo[2]) ? $pathInfo[2] : config('route.default_action');
        } else {
            $pathInfo = ['',config('route.default_controller'),config('route.default_action')];
            $controller = config('route.default_controller');
            $action = config('route.default_action');
        }
        $map = [];
        if ($request->admin_info->authGroup->type === AppConstant::GROUP_TYPE_ADMIN){
            $query = $request->admin_info->authGroup->rules;
            $map = [
                ['id','in',$query]
            ];
        }
        $accessArr = Db::name('auth_rule')->where($map)
            ->where(['status'=> AppConstant::STATUS_FORMAL])->column('name');
        if (!$accessArr){
            error('您没有权限访问，因为尚未有任何权限');
        }
        foreach ($accessArr as &$item) {
            if ($item === '/'){
                $item = '/'.config('route.default_controller').'/'.config('route.default_action');
            }
        }
        $access = '/'.$controller.'/'.$action;

        if (!in_array($access,$accessArr) && $request->admin_info->authGroup->type !== AppConstant::GROUP_TYPE_SUPER_ADMIN) {;
            error('您没有权限访问');
        }
        $adminInfo = $request->admin_info;
        $adminInfo->access_list = $accessArr;
        $request->admin_info = $adminInfo;
    }
    protected function view($template = '',$var = [],$code = 200,$filter =null, $isUseLayout = true) {
        if ($isUseLayout) {
            View::engine()->layout('../../common/core/tpl/layout');
        }
        $arr = [
            'runtimeInfo' => [
                'page' => [
                    'app' => 'admin',
                    'controller' => $this->request->controller(true),
                    'action' => $this->request->action(true),
                    'url' => url()->build(),
                ],
                'apiCode' => AppConstant::getApiCodeList(),
            ],
            'langList' => $this->langList,
        ];
        $var = array_merge($arr,$var);
        return \view($template,$var,$code,$filter);
    }
}