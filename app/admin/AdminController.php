<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
declare (strict_types = 1);

namespace app\admin;

use app\admin\model\AuthRule;
use constant\AppConstant;
use app\admin\model\AuthGroupAccess;
use think\App;
use think\exception\ValidateException;
use think\facade\Lang;
use think\facade\View;
use think\Validate;
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
        $this->checkLogin();
        $this->checkAccess();
        $this->loadLangFiles();

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
        $controller = $request->controller(true);
        $action = $request->action(true);
        $appName = $this->app->http->getName();
        if ($controller === config('route.default_controller') &&
            $action === config('route.default_action')){
            $menuUrl = '/'.$appName;
        } else {
            $menuUrl = '/'.$appName.'/'.$controller.'/'.$action;
        }

        $ruleList = $request->admin_info->access_list->toArray();
        $currentMenuId = 0;
        $rootId = 0;
        $pid = 0;
        $ruleType = AppConstant::RULE_TYPE_MENU;
        //通过权限校验才会开始获取用于渲染的菜单列表 因此此处不用校验 当前规则ID的合法性
        foreach ($ruleList as $key => $item) {
            if ($item['name'] === $menuUrl) {
                $currentMenuId = $item['id'];
                $rootId = $item['root_id'] === 0 ? $item['id'] : $item['root_id'];
                $ruleType = $item['type'];
                $pid = $item['pid'];
            }
            // 删除非菜单规则 供前端渲染使用
            if ($item['type'] !== AppConstant::RULE_TYPE_MENU) {
                unset($ruleList[$key]);
            }
        }

        if ($ruleType === AppConstant::RULE_TYPE_OTHER) {
            $currentMenuId = $pid;
        }

        return $this->getIndexTreeMenu($ruleList,0,$currentMenuId,$rootId);
    }

    protected function getIndexTreeMenu($ruleList,$pid,$currentMenuId,$rootId,$currentLevel = 0,$maxLevel = 0) {
        $treeData = [];
        foreach ($ruleList as $key => $item){
            if ($maxLevel && $currentLevel === $maxLevel){
                return $treeData;
            }

            if ($item['pid'] === $pid) {
                $result = $this->getIndexTreeMenu($ruleList,$item['id'],$currentMenuId,$rootId,$currentLevel,$maxLevel);
                // 以下就是处理子元素没有子节点后返回的逻辑
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
                $this->error(lang('login.not'),url('Login/index'));
            }
            $admin = AuthGroupAccess::hasWhere('admin',[
                'login_code' => $loginCode
            ])->find();
            if (!$admin) {
                $this->error(lang('login.not'),url('Login/index'));
            }

            if ($admin->admin->login_code !== $loginCode) {
                $this->error(lang('login.not'),url('Login/index'));
            }
            $this->request->admin_info = $admin;

        }
    }
    protected function checkAccess() {
        $request = $this->request;

        if (in_array($this->request->action(true),$this->noNeedLogin) !== false) {
            return ;
        }

        $map = [];
        $appName = $this->app->http->getName();

        if ($request->admin_info->authGroup->type === AppConstant::GROUP_TYPE_ADMIN){
            $rules = $request->admin_info->authGroup->rules;
            $map = function ($query) use ($rules,$appName) {
                $query->whereRaw('concat("/'.$appName.'/",`name`) in :rules',['rules' => $rules]);
            };
        }

        $accessArr = AuthRule::where($map)
            ->where(['status'=> AppConstant::STATUS_FORMAL])
            ->withAttr('name',function ($value) use ($appName) {
                return strtolower('/'.$appName.'/'.$value);
            })->order('weigh','desc')->select();
        if (!$accessArr){
            error('您没有权限访问，因为尚未有任何权限');
        }
        $controller = $request->controller(true);
        $action = $request->action(true);
        $access = '/'.$appName.'/'.$controller.'/'.$action;

        $accessNameList = array_column($accessArr->toArray(),'name');

        if (in_array($access,$accessNameList) === false) {;
            error('根据您已有的权限，您没有权限访问');
        }
        $adminInfo = $request->admin_info;
        $adminInfo->access_list = $accessArr;
        $this->request->admin_info = $adminInfo;
    }
    protected function loadLangFiles() {
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
    }

    protected function view($template = '',$var = [],$code = 200,$filter =null, $isUseLayout = true) {
        if ($isUseLayout) {
            View::engine('Think')->layout(base_path().'common/core/tpl/layout');
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

        $arr = [
            'runtimeInfo' => [
                'page' => [
                    'app' => 'admin',
                    'controller' => $this->request->controller(true),
                    'action' => $this->request->action(true),
                    'url' => url()->build(),
                ],
                'apiCode' => AppConstant::getApiCodeList(),
                'asideMenuList' => $this->getRenderMenuList(),
            ],
            'langList' => $this->langList,
        ];
        $var = array_merge($arr,$var);
        return \view($template,$var,$code,$filter);
    }
}