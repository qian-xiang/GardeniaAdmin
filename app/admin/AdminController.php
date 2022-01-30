<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
declare (strict_types = 1);

namespace app\admin;

use app\admin\model\MenuRule;
use app\common\core\lib\CurdHelper;
use constant\AppConstant;
use app\admin\model\AdminGroupAccess;
use think\App;
use think\facade\Lang;
use think\helper\Str;
use think\Template;
use gardenia_admin\src\core\core_class\GardeniaConstant;
use think\facade\Config;

class AdminController extends BaseController
{
    use CurdHelper;
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

    //不需要校验是否已登录的action
    protected $noNeedLogin = [];
    //不需要校验是否拥有访问权限的action
//    protected $noCheckAccess = [];
    protected $loadControllerLang = true;
    protected $langList = [];
    //bootstrap搜索字段
    protected $searchField = 'id';
    //查询数据库时对搜索字段使用的操作符 $operate = 'like'
    protected $operator = '=';

    // 初始化
    protected function initialize()
    {
        $this->checkLogin();
        $this->checkAccess();
        $this->loadLangFiles();

    }

    protected function getRenderMenuList() {
        $request = $this->request;
        if (!$request->admin_info) {
            return [];
        }
        $controller = $request->controller();
        $action = $request->action();
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

    protected function getIndexTreeMenu($ruleList,$pid,$currentMenuId,$currentLevel = 0,$maxLevel = 0) {
        $treeData = [];
        foreach ($ruleList as $key => $item){
            if ($maxLevel && $currentLevel === $maxLevel){
                return $treeData;
            }

            if ($item['pid'] === $pid) {
                $result = $this->getIndexTreeMenu($ruleList,$item['id'],$currentMenuId,$currentLevel,$maxLevel);
                // 以下就是处理子元素没有子节点后返回的逻辑
                if ($result){
                    $item['children'] = $result;
                }
//                $item['spread'] = $item['id'] === $pid;
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
                error(lang('login.not'),url('/admin/Login/index'));
            }
            $admin = AdminGroupAccess::with('admin_group')->hasWhere('admin',[
                'login_code' => $loginCode
            ])->find();
            if (!$admin) {
                error(lang('login.not'),url('/admin/Login/index')->build());
            }

            if ($admin->admin->login_code !== $loginCode) {
                error(lang('login.not'),url('/admin/Login/index')->build());
            }
            $this->request->admin_info = $admin;

        }
    }
    protected function checkAccess() {
        $request = $this->request;
        $this->noNeedLogin = array_map(function ($value) {
            return Str::snake($value);
        },$this->noNeedLogin);
        if (in_array($this->request->action(true),$this->noNeedLogin) !== false) {
            return ;
        }

        $appName = $this->app->http->getName();

        $rules = $request->admin_info->adminGroup->rules;
        if ($rules === '*') {
            $map = [];
        } else {
            $map[] = ['id','in',$rules];
        }

        $accessArr = MenuRule::where($map)
            ->where(['status'=> AppConstant::STATUS_FORMAL])
            ->withAttr('name',function ($value) use ($appName) {
                return '/'.$appName.'/'.$value;
            })->order('weigh','desc')->select();
        if (!$accessArr){
            error('您没有权限访问，因为尚未有任何权限');
        }
        $controller = $request->controller();
        $action = $request->action();
        //还需处理appName为空的问题
        $access = '/'.$appName.'/'.$controller.'/'.$action;
        $accessNameList = array_column($accessArr->toArray(),'name');

        //非插件请求时才鉴权
        if (!is_addon_request() && in_array($access,$accessNameList) === false) {;
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
            $this->app->LoadLangPack();

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
        $viewCofig = Config::get('view');
        // 如果是插件请求，更改视图默认的访问位置
        if (is_addon_request()) {
            $addonInfo = parse_addon_url();
            $controllerName = $addonInfo['controller'];
            $actionName = $addonInfo['action'];
            $viewCofig['view_path'] = get_addon_view_dir().DIRECTORY_SEPARATOR;
            $layoutPath = base_path().'common/core/tpl/layout';
        } else {
            $controllerName = $this->request->controller();
            $actionName = $this->request->action();
            $viewCofig['view_path'] = app_path().$viewCofig['view_dir_name'].DIRECTORY_SEPARATOR;
            $viewCofig['cache_path'] = runtime_path().'temp'.DIRECTORY_SEPARATOR;
            $layoutPath = '../../common/core/tpl/layout';
        }
        if (!$template) {
            $template = Str::snake($controllerName).DIRECTORY_SEPARATOR.Str::snake($actionName);
        }

        $viewInstance = new Template($viewCofig);

        if ($isUseLayout) {
            $viewInstance->layout($layoutPath);
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
        $viewInstance->assign([
            GardeniaConstant::GARDENIA_PREFIX.'Layout' => $gardeniaLayout
        ]);

        $arr = [
            'runtimeInfo' => [
                'page' => [
                    'app' => 'admin',
                    'controller' => $this->request->controller(),
                    'action' => $this->request->action(),
                    'url' => url()->build(),
                    'controllerJs' => '/static/js/backend/'.Str::snake(request()->controller()),
                    'controllerJsHump' => Str::studly(request()->controller()),
                ],
                'apiCode' => AppConstant::getApiCodeList(),
                'asideMenuList' => $this->getRenderMenuList(),
                'adminInfo' => $this->request->admin_info,
            ],
            'langList' => $this->langList,
        ];
        $var = array_merge($arr,$var);

        $viewInstance->fetch($template,$var);
    }
}