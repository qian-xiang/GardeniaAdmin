<?php


namespace app\common\core\lib;


use app\common\core\exception\AppException;

class Tree
{
    protected $config = [];
    public function __construct($config = [])
    {
        if (!$config) {
            $this->config = [
                //pid的名称
                'pidName' => 'pid',
                //id的名称
                'idName' => 'id',
                'childrenName' => 'children',
            ];
        } else {
            $configKeys = [
                'pidName',
                'idName',
                'childrenName',
            ];
            $paramConfig = array_keys($config);
            $res = array_intersect($configKeys,$paramConfig);
            sort($res);
            sort($configKeys);
            if ($configKeys !== $res) {
                throw new AppException('必须对以下信息进行配置：'.join(',',$configKeys));
            }
            $this->config = $config;
        }
    }

    /**
     * 获取父亲节点ID
     * @param int $id
     * @param int $pid
     * @param array $data
     * @return int
     */
    public function getParent($id = 0, $pid = 0, $data = []) {
        foreach ($data as $index => $item) {
            if ($item[$this->config['idName']] === $id) {
                return $pid;
            }
            if (!empty($item[$this->config['childrenName']])) {
                $resPid = $this->getParent($id,$item[$this->config['idName']],$item[$this->config['childrenName']]);
                if ($resPid) {
                    return $resPid;
                }
            }
        }
        return 0;
    }
}