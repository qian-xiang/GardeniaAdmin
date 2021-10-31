<?php


namespace app\common\taglib;


use think\Exception;
use think\template\TagLib;

class Gar extends TagLib
{
    protected $tagLib = 'gar';
    /**
     * 定义标签列表
     */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'arttypelist'     => ['attr' => 'channel_id', 'close' => 1], //闭合标签，默认为不闭合

    ];

    /**
     * 这是一个闭合标签的简单演示
     */
    public function tagArtTypeList($tag,$content)
    {
        if (empty($tag['channel_id'])) {
            throw new Exception('使用arttypelist标签时频道ID：channel_id必填');
        }
        $id = $tag['id'] ?? 'item';
        $parse = '<?php ';
        $parse .= 'use app\\common\\model\\core\\ArticleType;';
        $parse .= '$___garArtTypeList___ = ArticleType::where([\'delete_time\' => 0,])
        ->where("channel_id","in",'.$tag['channel_id'].')->select();';
        $parse .= ' ?>';

        $parse .= '{volist name="___garArtTypeList___" id="' . $id . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 这是一个闭合标签的简单演示
     */
//    public function tagClose($tag, $content)
//    {
//        $type = empty($tag['type']) ? 0 : 1; // 这个type目的是为了区分类型，一般来源是数据库
//        $name = $tag['name']; // name是必填项，这里不做判断了
//        $parse = '<?php ';
//        $parse .= '$test_arr=[[1,3,5,7,9],[2,4,6,8,10]];'; // 这里是模拟数据
//        $parse .= '$__LIST__ = $test_arr[' . $type . '];';
/*        $parse .= ' ?>';*/
//        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
//        $parse .= $content;
//        $parse .= '{/volist}';
//        return $parse;
//    }


}