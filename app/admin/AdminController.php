<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */
declare (strict_types = 1);

namespace app\admin;

use app\admin\model\MenuRule;
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
    protected $noCheckAccess = [];
    protected $loadControllerLang = true;
    protected $langList = [];

    // 初始化
    protected function initialize()
    {
        $this->checkLogin();
        $this->checkAccess();
        $this->loadLangFiles();

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
        $suffix = \config('view.view_suffix');
        $suffix = $suffix ? '.'.$suffix : '';
        view(app_path().'view/common/success'.$suffix,[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ])->send();
    }
    protected function error($content = '',$redirectUrl = null,$second = 3) {
//        $suffix = \config('view.view_suffix');
//        $suffix = $suffix ? '.'.$suffix : '';
        $path = 'view/common/error';

        $path = is_addon_request() ? app_path().ADDON_APP.'/'.$path.'.html' : $path;
        $redirectUrl = $redirectUrl === null ? $this->request->header('referer') : $redirectUrl;
        view($path,[
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

        $map = [];
        $appName = $this->app->http->getName();

        if ($request->admin_info->adminGroup->type === AppConstant::GROUP_TYPE_ADMIN){
            $rules = $request->admin_info->adminGroup->rules;
            $map = function ($query) use ($rules,$appName) {
                $query->whereRaw('concat("/'.$appName.'/",`name`) in :rules',['rules' => $rules]);
            };
        }
        $accessArr = MenuRule::where($map)
            ->where(['status'=> AppConstant::STATUS_FORMAL])
            ->withAttr('name',function ($value) use ($appName) {
                return strtolower('/'.$appName.'/'.$value);
            })->order('weigh','desc')->select();
        if (!$accessArr && $request->admin_info->auth_group->type !== AppConstant::GROUP_TYPE_SUPER_ADMIN){
            error('您没有权限访问，因为尚未有任何权限');
        }

        $controller = $request->controller(true);
        $action = $request->action(true);
        //还需处理appName为空的问题
        $access = '/'.$appName.'/'.$controller.'/'.$action;
        $accessNameList = array_column($accessArr->toArray(),'name');
        //非插件请求时才鉴权
        if (!is_addon_request() && in_array($access,$accessNameList) === false && $request->admin_info->admin_group->type !== AppConstant::GROUP_TYPE_SUPER_ADMIN) {;
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
            $viewCofig['cache_path'] = runtime_path().'temp';
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

        $viewInstance->fetch($template,$var);
    }
}