<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace app\admin\extend\core\core_class;


class GardeniaTable
{
    protected $tableHeader = [];
    protected $data = [];

//    public static function getInstance() {
//        if (!self::$gardeniaTable) {
//            self::$gardeniaTable = new GardeniaTable();
//        }
//        return self::$gardeniaTable;
//    }
    public function addTableHeader($field,$title) {
        $this->tableHeader[] = ['field'=> $field, 'title'=> $title];
        return $this;
    }
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    public function display() {
        return view('index/index',[
            'data'=> $this->data,
            'tableHeader'=> $this->tableHeader,
        ]);
    }
}