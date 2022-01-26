<?php


namespace app\garcms\controller;


use app\admin\model\MenuRule;
use app\common\core\exception\AppException;
use constant\AppConstant;

class Module
{
    /**
     * 不能拥有同名的name和title
     * @var array[]
     */
    protected $menuRule = [
        [
            'type' => AppConstant::RULE_TYPE_MENU,
            'title' => 'GarCms管理',
            'name' => '/garcms',
            'weigh' => -1,
            'children' => [
                [
                    'type' => AppConstant::RULE_TYPE_MENU,
                    'title' => '频道管理',
                    'name' => '/garcms/Channel',
                    'weigh' => -1,
                    'children' => [
                        [
                            'type' => AppConstant::RULE_TYPE_MENU,
                            'title' => '频道列表',
                            'name' => '/garcms/Channel/index',
                            'weigh' => -1,
                        ],
                        [
                            'type' => AppConstant::RULE_TYPE_OTHER,
                            'title' => '新增',
                            'name' => '/garcms/Channel/add',
                            'weigh' => -2,
                        ],
                        [
                            'type' => AppConstant::RULE_TYPE_OTHER,
                            'title' => '编辑',
                            'name' => '/garcms/Channel/edit',
                            'weigh' => -3,
                        ],
                        [
                            'type' => AppConstant::RULE_TYPE_OTHER,
                            'title' => '删除',
                            'name' => '/garcms/Channel/delete',
                            'weigh' => -4,
                        ],
                    ]
                ],
                [
                    'type' => AppConstant::RULE_TYPE_MENU,
                    'title' => '栏目管理',
                    'name' => '/garcms/ArticleColumn',
                    'weigh' => -1,
                    'children' => [
                        [
                            'type' => AppConstant::RULE_TYPE_MENU,
                            'title' => '栏目列表',
                            'name' => '/garcms/ArticleColumn/index',
                            'weigh' => -1,
                        ],
                        [
                            'type' => AppConstant::RULE_TYPE_OTHER,
                            'title' => '新增',
                            'name' => '/garcms/ArticleColumn/add',
                            'weigh' => -2,
                        ],
                        [
                            'type' => AppConstant::RULE_TYPE_OTHER,
                            'title' => '编辑',
                            'name' => '/garcms/ArticleColumn/edit',
                            'weigh' => -3,
                        ],
                        [
                            'type' => AppConstant::RULE_TYPE_OTHER,
                            'title' => '删除',
                            'name' => '/garcms/ArticleColumn/delete',
                            'weigh' => -4,
                        ],
                    ]
                ]
            ]
        ]
    ];
    public function install() {
        $menuRule = $this->menuRule;
        $insert = [];
        //将树形结构扁平化 查出要新增的所有菜单
        while (true) {
            $temp = [];
            foreach ($menuRule as $item) {
                $insert[] = $item;
                !empty($item['children']) && $temp = array_merge($temp,$item['children']);
            }
            $menuRule = $temp;
            if (!$temp) {
                break;
            }
        }
        if (!$insert) {
            return true;
        }
        try {
            $menuRuleModel = new MenuRule();
            $dataset = $menuRuleModel->saveAll($insert);
            $updateData = $this->treeToPidRows($dataset,$this->menuRule);
            $menuRuleModel->saveAll($updateData);
        } catch (\Exception $e) {
            error('安装模块失败，请稍候重试');
        }
    }
    public function uninstall() {
        $menuRule = $this->menuRule;
        $nameList = [];
        //将树形结构扁平化 查出要新增的所有菜单
        while (true) {
            $temp = [];
            foreach ($menuRule as $item) {
                $nameList[] = $item['name'];
                !empty($item['children']) && $temp = array_merge($temp,$item['children']);
            }
            $menuRule = $temp;
            if (!$temp) {
                break;
            }
        }
        if ($nameList) {
            $affectCount = MenuRule::where('name','in',$nameList)->delete();
            if ($affectCount !== count($nameList)) {
                throw new AppException('模块菜单规则删除不彻底');
            }
        }
        //删除模块目录
        return remove_dir(app_path());
    }
    protected function treeToPidRows($dataset = [], $tree = []) {
        $rows = [];
        foreach ($dataset as $model) {
            $parentRuleName = $this->getParentRuleNameByRuleName($model->name,'',$tree);
            if (!$parentRuleName) {
                $pid = 0;
            } else {
                $pid = 0;
                foreach ($dataset as $_model) {
                    if ($_model->name === $parentRuleName) {
                        $pid = $_model->id;
                        break;
                    }
                }
            }
            $rows[] = [
                'id' => $model->id,
                'pid' => $pid,
            ];
        }
        return $rows;
    }

    /**
     * 通过菜单规则的规则名称获取父规则名称（要求模块的菜单列表中不能拥有同名的规则名称 name）
     * @param string $ruleName
     * @param string $parentRuleName
     * @param array $tree
     * @return string
     */
    protected function getParentRuleNameByRuleName($ruleName = '', $parentRuleName = '', $tree = []) {
        foreach ($tree as $index => $item) {
            if ($item['name'] === $ruleName) {
                return $parentRuleName;
            }
            if (!empty($item['children'])) {
                $resPid = $this->getParentRuleNameByRuleName($ruleName,$item['name'],$item['children']);
                if ($resPid) {
                    return $resPid;
                }
            }
        }
        return '';
    }
}