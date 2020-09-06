<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\middleware;


use app\admin\extend\diy\extra_class\AppConstant;
use think\facade\Db;
use think\facade\View;

class CheckAccess
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);
        return $response;
    }
    private function buildTreeData($ruleList,$pid,$checkData = [],$currentLevel = 0,$maxLevel = 0) {
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
//                $item['spread'] = true;
//                $item['active'] = in_array($item['id'],$checkData);
                $treeData[] = $item;
            }
        }
        return $treeData;
    }

}