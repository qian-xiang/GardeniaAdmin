<?php
declare (strict_types = 1);

namespace app\admin;

use think\App;
use think\Exception;
use think\exception\ValidateException;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
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
        //
//        return redirect(url('/GardeniaTransit/error',['content' => '出错了！'])->build())->send();
//        return $this->success('成功');
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
        return response([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'redirectUrl' => $redirectUrl,
        ],200,[],'json');
    }
    protected function success($content = '',$redirectUrl = null,$second = 3) {
        $redirectUrl = $redirectUrl === null ? $this->request->header('referer') : $redirectUrl;
        return view('common/success',[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ])->send();
    }
    protected function error($content = '',$redirectUrl = null,$second = 3) {
        $redirectUrl = $redirectUrl === null ? $this->request->header('referer') : $redirectUrl;
        return view('common/error',[
            'content' => $content,
            'redirectUrl' => $redirectUrl,
            'second' => $second
        ])->send();
    }
}
