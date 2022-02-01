<?php
declare (strict_types = 1);

namespace app\command\addon;

use app\common\core\exception\AppException;
use app\validate\admin\CurdValidate;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\helper\Str;

class Curd extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('curd')
            ->addArgument('operate',Argument::OPTIONAL,'具体操作','c')
            ->addOption('app','app',Option::VALUE_REQUIRED,'应用名称，默认为admin','admin')
            ->addOption('table','t',Option::VALUE_OPTIONAL,'数据库表名称，可不加表前缀','gardenia')
            ->addOption('controller','c',Option::VALUE_REQUIRED,'控制器名，无需加控制器后缀，可在前面加目录名称以/相隔','')
            ->addOption('model','m',Option::VALUE_REQUIRED,'模型名，可在前面加目录名称以/相隔','')
            ->addOption('field','f',Option::VALUE_REQUIRED,'显示字段，*=全部，多个字段用半角逗号,隔开','*')
            ->setDescription('GardeniaAdmin官方一键curd命令');
    }

    protected function execute(Input $input, Output $output)
    {
        $arguments = $input->getArguments();
        $validate = new CurdValidate();
        $validate->setCurdParamRule();
        if (!$validate->check($arguments)) {
            throw new AppException($validate->getError());
        }
        $options = $input->getOptions();
        $validate->setCurdOptionRule();
        if (!$validate->check($options)) {
            throw new AppException($validate->getError());
        }
        $options['model'] = $options['model'] ?: Str::studly($options['table']);
        $namespaceModel = 'app\\'.$options['app'].'\\model\\'.$options['model'];
        $model = new $namespaceModel();
        $output->writeln(json_encode($model->getFields(),JSON_UNESCAPED_UNICODE));
        return ;

        switch ($arguments['operate']) {
            case 'c';
                //执行新增curd操作
                $curlTplDir = base_path('common').'/core/tpl/curd/';
                //先创建模型
                $modelTplPath = $curlTplDir.'model.tpl';
                $content = file_get_contents($modelTplPath);
                $content = str_replace('[appName]',$options['app'],$content);
                $content = str_replace('[modelName]',$options['model'],$content);
                $modelPath = base_path($options['app'].DIRECTORY_SEPARATOR).'model'.DIRECTORY_SEPARATOR.Str::studly($options['model']).'.php';
                $res = file_put_contents($modelPath,$content);
                if (!$res) {
                    throw new AppException('新增模型失败，请稍候重试');
                }
                //再创建控制器
                $modelTplPath = $curlTplDir.'controller.tpl';
                $content = file_get_contents($modelTplPath);
                $content = str_replace('[appName]',$options['app'],$content);
                $content = str_replace('[controllerName]',$options['controller'],$content);
                $controllerPath = base_path($options['app'].DIRECTORY_SEPARATOR).'controller'.DIRECTORY_SEPARATOR.Str::studly($options['controller']).(config('route.controller_suffix') ? 'Controller' : '').'.php';
                $res = file_put_contents($controllerPath,$content);
                if (!$res) {
                    throw new AppException('新增控制器失败，请稍候重试');
                }
                //新增视图文件
                //加载指定应用的视图配置文件
                $viewConfig = require_once base_path($options['app'].DIRECTORY_SEPARATOR).'config'.DIRECTORY_SEPARATOR.'view.php';
                $viewConfig['view_dir_name'] = $viewConfig['view_dir_name'] ?: 'view';
                $suffixText = $viewConfig['view_suffix'] ? '.'.$viewConfig['view_suffix'] : '.html';
                $res = file_put_contents(base_path($options['app'].DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.Str::snake($options['controller']).DIRECTORY_SEPARATOR.'index').$suffixText,file_get_contents($curlTplDir.'view'.DIRECTORY_SEPARATOR.'index.tpl'));
                if (!$res) {
                    throw new AppException('新增index.html失败，请稍候重试');
                }

                break;
            case 'd';
                break;
            default:
                throw new AppException('operate参数暂不支持该值');
        }
        // 指令输出
        $output->writeln('curd');
    }
    private function getValidateRuleAndTemplateByField($field = '',$fieldInfo = []) {
        $info = [
            $field => [
                'validateRule' => 'require'
            ]
        ];
        if (strpos($field,'image') !== false || strpos($field,'picture') !== false) {
            $title = $fieldInfo['comment'];
            $info[$field]['template'] = '';
            $rule = 'url';
            $info[$field]['validateRule'] = $info[$field]['validateRule'] ? '|'.$rule : $rule;
        } elseif (strpos($field,'attach') !== false || strpos($field,'file') !== false) {
            $rule = 'url';
            $info[$field]['validateRule'] = $info[$field]['validateRule'] ? '|'.$rule : $rule;
        } elseif (strpos($field,'status') !== false) {
            $title = mb_substr($fieldInfo['comment'],0,mb_strpos($fieldInfo['comment'],':'));
            $_comment = mb_substr($fieldInfo['comment'],mb_strpos($fieldInfo['comment'],':') + 1);
            $_comment = explode(',',$_comment);
            $optionText = '';
            $rule = '';
            foreach ($_comment as $item) {
                $temp = explode('=',$item);
                $optionText .= '<option value="'.$temp[0].'">'.$temp[1].'</option>';
                $rule = $rule ? ','.$temp[0] : $temp[0];
            }
            //前端仅做简单的验证
            $fieldHtml = '<div class="form-group row"><label class="col-form-label col-sm-2 text-center" for="'.$field.'">'.$title.'</label><select class="form-control col-sm-5" data-rule="required" id="'.$field.'"  name="'.$field.'">'.$optionText.'</select></div>';
            $info[$field]['template'] = $fieldHtml;
            $info[$field]['validateRule'] = $info[$field]['validateRule'] ? '|'.$rule : $rule;
        } elseif (strpos($field,'is_') !== false) {
            $title = mb_substr($fieldInfo['comment'],0,mb_strpos($fieldInfo['comment'],':'));
            $_comment = mb_substr($fieldInfo['comment'],mb_strpos($fieldInfo['comment'],':') + 1);
            $_comment = explode(',',$_comment);
            $optionText = '';
            $rule = '';
            foreach ($_comment as $item) {
                $temp = explode('=',$item);
                $optionText .= '<div class="form-check-inline"><input class="form-check-input" type="radio" name="'.$field
                    .'" id="'.$field.'"><label class="form-check-label" for="'.
                    $field.'">'.$title.'</label></div>';
                $rule = $rule ? ','.$temp[0] : $temp[0];
            }
            //前端仅做简单的验证
            $fieldHtml = '<div class="form-group row"><label class="col-form-label col-sm-2 text-center" for="'.$field.'">'.$title.'</label>'.$optionText.'</div>';
            $info[$field]['template'] = $fieldHtml;
            $info[$field]['validateRule'] = $info[$field]['validateRule'] ? '|'.$rule : $rule;
        } elseif (($mapList = array_map(function ($value) use ($field) {
            return strpos($field,$value) !== false;
        },['time','date','birthday','year','month'])) && in_array(true,$mapList)) {
            $rule = 'require|integer|length:10';
            $title = $fieldInfo['comment'];
            //前端仅做简单的验证
            $info[$field]['template'] = '<div class="form-group row"><label for="'.
                $field.'">'.$title.'</label><input class="form-control flatpickr-input" type="text" name="'.$field
                .'" id="'.$field.'"></div>';
            $info[$field]['validateRule'] = $info[$field]['validateRule'] ? '|'.$rule : $rule;
        }

    }
}
