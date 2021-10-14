<?php
declare (strict_types = 1);

namespace app\admin\controller;

use constant\AppConstant;
use app\admin\AdminController;
use gardenia_admin\src\core\core_class\GardeniaForm;
use gardenia_admin\src\core\core_class\GardeniaHelper;
use gardenia_admin\src\core\core_class\GardeniaList;
use think\Request;

class Log extends AdminController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = request();
        $gardeniaList = new GardeniaList();
        $gardeniaList
            ->setTableAttr('url',url('/'.$request->controller().'/getData')->build())
            ->setTableAttr('page',true)
            ->addTableHead('date_time','请求时间')
            ->addTableHead('type','类型')
            ->addTableHead('content','内容')
            ->addTableHead('spend_time','耗时')
            ->display();
    }

    public function getData() {

        $logDir = config('channels.file.path') ? config('channels.file.path') : app()->getRuntimePath().'log';
        $content = $this->getLogContent($logDir);

        $pattern = "/\[(\d+\-\d{1,2}\-\d{1,2}T\d{1,2}\:\d{1,2}\:\d{1,2}\+\d{1,2}\:\d{1,2})\]\[([\s\S]*?)\]\s([\s\S]*?)(\[\s{1}RunTime:(\d+\.\d+\S)\s\])?\n/";
        $res = preg_match_all($pattern,$content,$arr);
        if (!$res) {
            $this->error('获取日志内容时，执行正则表达式失败。');
        }
        unset($arr[0]);
        $arr = array_values($arr);
        $array = [];
        if ($arr){
            foreach ($arr[0] as $key => $item) {
                $dateTime = new \DateTime($item);
                $item = $dateTime->format('Y年m月d日 H时i分s秒');
                $array[$key]['date_time'] = $item;
                $array[$key]['type'] = $arr[1][$key];
                $array[$key]['content'] = $arr[2][$key];
                $array[$key]['spend_time'] = (isset($arr[4][$key]) && $arr[4][$key]) ? $arr[4][$key] : '';
            }
        }
        unset($arr);
        $recordCount = count($array);
        $array = GardeniaHelper::layPaginate($array);
        $data = [
            'code' => AppConstant::CODE_SUCCESS,
            'msg' => '获取成功！',
            'count' => $recordCount,
            'data' => $array
        ];

        return response($data,200,[],'json');
    }
    private function getLogContent($logDir) {
        $content = '';
        //获取所有日志
        $arr = scandir($logDir,1);
        $arr = array_diff($arr,['.','..']);
        foreach ($arr as $item) {
            if (is_dir($logDir.'/'.$item)){
                $content = $content.$this->getLogContent($logDir.'/'.$item);
            }
            $content = $content.file_get_contents($logDir.'/'.$item);
        }

        return $content;
    }
}
