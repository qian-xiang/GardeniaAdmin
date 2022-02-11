<?php
declare (strict_types=1);

namespace app\command\addon;

use app\common\core\exception\AppException;
use app\validate\admin\CurdValidate;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use think\helper\Str;

class Curd extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('curd')
            ->addArgument('operate', Argument::OPTIONAL, '具体操作', 'c')
            ->addOption('app', 'app', Option::VALUE_REQUIRED, '应用名称，默认为admin', 'admin')
            ->addOption('table', 't', Option::VALUE_OPTIONAL, '数据库表名称，可不加表前缀', 'gardenia')
            ->addOption('controller', 'c', Option::VALUE_REQUIRED, '控制器名，无需加控制器后缀，可在前面加目录名称以/相隔', '')
            ->addOption('model', 'm', Option::VALUE_REQUIRED, '模型名，可在前面加目录名称以/相隔', '')
            ->addOption('field', 'f', Option::VALUE_REQUIRED, '显示字段，*=全部，多个字段用半角逗号,隔开', '*')
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

        switch ($arguments['operate']) {
            case 'c';
                //执行新增curd操作
                $curlTplDir = base_path('common') . '/core/tpl/curd/';

                //新增视图文件
                //加载指定应用的视图配置文件
                $viewConfig = require_once base_path($options['app'] . DIRECTORY_SEPARATOR) . 'config' . DIRECTORY_SEPARATOR . 'view.php';
                $viewConfig['view_dir_name'] = $viewConfig['view_dir_name'] ?: 'view';
                $suffixText = $viewConfig['view_suffix'] ? '.' . $viewConfig['view_suffix'] : '.html';
                $res = file_put_contents(base_path($options['app'] . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . Str::snake($options['controller']) . DIRECTORY_SEPARATOR . 'index') . $suffixText, file_get_contents($curlTplDir . 'view' . DIRECTORY_SEPARATOR . 'index.tpl'));
                if (!$res) {
                    throw new AppException('新增index.html失败，请稍候重试');
                }
//                $namespaceModel = 'app\\'.$options['app'].'\\model\\'.$options['model'];
//                $model = new $namespaceModel();
                $dbName = lcfirst(Str::snake($options['model']));
                $fields = Db::name($dbName)->getFields();
                $validateRule = [];
                $addFields = [];
                $pk = Db::name($dbName)->getPk();
                $extraModel = '';
                $extraController = '';
                $groupListAddText = '';
                $groupListEditText = '';
                foreach ($fields as $field => $fieldInfo) {
                    if (!in_array($field, ['create_time', 'update_time'])) {
                        if ($field === $pk) {
                            $validateRule[$field] = 'require|integer|>:0';
                        } else {
                            $res = $this->getValidateRuleAndTemplateByField($field, $fieldInfo);
                            $validateRule[$field . '|' . $fieldInfo['comment']] = $res['validateRule'];
                            $addFields[] = $field;
                            $extraModel .= $res['model'] . PHP_EOL;
                            $extraController .= $res['controller'] . PHP_EOL;
                            $groupListAddText .= $res['template_add'].PHP_EOL;
                            $groupListEditText .= $res['template_edit'].PHP_EOL;
                        }
                    }
                }
                //先创建模型
                $modelTplPath = $curlTplDir . 'model.tpl';
                $content = file_get_contents($modelTplPath);
                $content = str_replace('[appName]', $options['app'], $content);
                $content = str_replace('[modelName]', $options['model'], $content);
                $studlyModel = Str::studly($options['model']);
                $modelPath = base_path($options['app'] . DIRECTORY_SEPARATOR) . 'model' . DIRECTORY_SEPARATOR . $studlyModel . '.php';

                //如果有额外的模型内容则写入
                if (trim($extraModel)) {
                    $content = str_replace('[extra]', $extraModel, $content);
                }
                $res = file_put_contents($modelPath, $content);
                if (!$res) {
                    throw new AppException('新增模型失败，请稍候重试');
                }
                //再创建控制器
                $modelTplPath = $curlTplDir . 'controller.tpl';
                $content = file_get_contents($modelTplPath);
                $content = str_replace('[appName]', $options['app'], $content);
                $content = str_replace('[controllerName]', $options['controller'], $content);
                $controllerPath = base_path($options['app'] . DIRECTORY_SEPARATOR) . 'controller' . DIRECTORY_SEPARATOR . Str::studly($options['controller']) . (config('route.controller_suffix') ? 'Controller' : '') . '.php';
                //如果有额外的模型内容则写入
                if (trim($extraModel)) {
                    $template = <<<'EOT'
    protected function initialize()
    {
        $model = new [modelName];
        [content]
        parent::initialize();
    }
EOT;
                    $template = str_replace('[modelName]', "\\{$options['app']}\\model\\" . $studlyModel, $template);
                    $template = str_replace('[content]', $extraController, $template);
                    $content = str_replace('[extra]', $template, $content);
                }
                $res = file_put_contents($controllerPath, $content);
                if (!$res) {
                    throw new AppException('新增控制器失败，请稍候重试');
                }

                $addScene = join(',', $addFields);
                array_push($addFields, $pk);
                $editScene = join(',', $addFields);
                $validateScene = [
                    'add' => $addScene,
                    'edit' => $editScene,
                ];
                $validateTplPath = $curlTplDir . 'validate.tpl';
                $validateName = $options['controller'];
                $content = file_get_contents($validateTplPath);
                $content = str_replace('[validateName]', $validateName, $content);
                $content = str_replace('[rule]', $validateRule, $content);
                $content = str_replace('[scene]', $validateScene, $content);
                $path = base_path('validate' . DIRECTORY_SEPARATOR) . $options['app'] . DIRECTORY_SEPARATOR . Str::studly($validateName) . 'Validate.php';
                $res = file_put_contents($path, $content);
                if (!$res) {
                    throw new AppException('新增验证器失败，请稍候重试');
                }
                //新增表单新增模板文件
                $tplPath = $curlTplDir . 'view'.DIRECTORY_SEPARATOR.'add.tpl';
                $content = file_get_contents($tplPath);
                $content = str_replace('[formGroupList]', $groupListAddText, $content);
                $path = base_path($options['app'] . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . Str::snake($options['controller']) . DIRECTORY_SEPARATOR ) .'index'. $suffixText;
                $res = file_put_contents($path,$content);
                if (!$res) {
                    throw new AppException('新增表单新增模板失败，请稍候重试');
                }
                //新增表单编辑模板文件
                $tplPath = $curlTplDir . 'view'.DIRECTORY_SEPARATOR.'edit.tpl';
                $groupListEditText = '<input type="hidden" name="{$pk}" value="{\$row.{$pk}}">'.PHP_EOL.$groupListEditText;
                $content = file_get_contents($tplPath);
                $content = str_replace('[formGroupList]', $groupListEditText, $content);
                $path = base_path($options['app'] . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . Str::snake($options['controller']). DIRECTORY_SEPARATOR) .'edit'.$suffixText;
                $res = file_put_contents($path,$content);
                if (!$res) {
                    throw new AppException('新增表单编辑模板失败，请稍候重试');
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

    private function getValidateRuleAndTemplateByField($field = '', $fieldInfo = [])
    {
        $info = [
            'validateRule' => 'require',
            'template_add' => '',
            'template_edit' => '',
            'model' => '',
        ];
        $template = <<<'EOT'
<div class="form-group row">
        <label class="col-xs-12 col-sm-2">
            [title]
        </label>
        <div class="col-xs-12 col-sm-10 gardenia-upload" data-field="[field]" data-mime="[mime]">

        </div>
    </div>
EOT;
        $fieldModelTpl = <<<'EOT'
            public function [function]() {
                return [list];
            }
EOT;
        if (strpos($field, 'image') !== false || strpos($field, 'picture') !== false) {
            $title = $fieldInfo['comment'];
            //图片的mime类型暂定为这些
            $mime = 'image/gif,image/png,image/jpeg,image/bmp';

            $template = str_replace('[title]', $title, $template);
            $template = str_replace('[field]', $field, $template);
            $template = str_replace('[mime]', $mime, $template);
            $info['template_add'] = $template;
            //还需修改
            $info['template_edit'] = $template;
            $rule = 'url';
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
            $info['js'] = "{
                                field: '{$field}',
                                title: '{$title}',
                                formatter: ,
                            },";
        } elseif (strpos($field, 'attach') !== false || strpos($field, 'file') !== false) {
            $title = $fieldInfo['comment'];

            $template = str_replace('[title]', $title, $template);
            $template = str_replace('[field]', $field, $template);
            $template = str_replace('[mime]', '', $template);
            $info['template_add'] = $template;
            //还需修改
            $info['template_edit'] = $template;
            $rule = 'url';
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        } elseif (strpos($field, 'status') !== false) {
            $title = mb_substr($fieldInfo['comment'], 0, mb_strpos($fieldInfo['comment'], ':'));
            $_comment = mb_substr($fieldInfo['comment'], mb_strpos($fieldInfo['comment'], ':') + 1);
            $_comment = explode(',', $_comment);
            $rule = '';
            $listText = '';
            foreach ($_comment as $item) {
                $temp = explode('=', $item);
                $rule = $rule ? ',' . $temp[0] : $temp[0];
                $value = is_numeric($temp[1]) ? $temp[1] : "'{$temp[1]}'";
                $listText .= "'{$temp[0]}' => {$value},";
            }
            $studlyField = Str::studly($field);
            $functionName = 'get' . $studlyField . 'List';
            $list = '[' . $listText . ']';
            $fieldModel = str_replace('[function]', $functionName, $fieldModelTpl);
            $fieldModel = str_replace('[list]', $list, $fieldModel);

            $optionText = '{foreach $' . $field . 'List as $key => $vo} <option value="$key" {eq name="key" value="' . "row.{$field}" . '"}selected{/eq}>{$vo}</option> {/foreach}';
            //前端仅做简单的验证
            $fieldHtml = '<div class="form-group row"><label class="col-form-label col-sm-2 text-center" for="' . $field . '">' . $title . '</label><select class="form-control col-xs-12 col-sm-10" data-rule="required" id="' . $field . '"  name="' . $field . '">' . $optionText . '</select></div>';
            $info['template_add'] = $fieldHtml;
            //还需修改
            $info['template_edit'] = $template;
            $info['model'] = $fieldModel;
            $info['controller'] = "\$${$field}List = \$this->model->get${$studlyField}List();" . PHP_EOL . "\\think\\facade\\View::assign('${$field}List',\$${$field}List);" . PHP_EOL;
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        } elseif (strpos($field, 'is_') !== false) {
            $title = mb_substr($fieldInfo['comment'], 0, mb_strpos($fieldInfo['comment'], ':'));
            $_comment = mb_substr($fieldInfo['comment'], mb_strpos($fieldInfo['comment'], ':') + 1);
            $_comment = explode(',', $_comment);
            $optionText = '';
            $rule = '';
            $listText = '';

            foreach ($_comment as $item) {
                $temp = explode('=', $item);
                $optionText .= '<div class="form-check-inline"><input class="form-check-input" type="radio" name="' . $field
                    . '" id="' . $field . '"><label class="form-check-label" for="' .
                    $field . '">' . $title . '</label></div>';
                $rule = $rule ? ',' . $temp[0] : $temp[0];

                $value = is_numeric($temp[1]) ? $temp[1] : "'{$temp[1]}'";
                $listText .= "'{$temp[0]}' => {$value},";

            }
            $studlyField = Str::studly($field);
            $functionName = 'get' . $studlyField . 'List';
            $list = '[' . $listText . ']';
            $fieldModel = str_replace('[function]', $functionName, $fieldModelTpl);
            $fieldModel = str_replace('[list]', $list, $fieldModel);
            $info['model'] = $fieldModel;

            //前端仅做简单的验证
            $fieldHtml = '<div class="form-group row"><label class="col-xs-12 col-sm-2 col-form-label text-center" for="' . $field . '">' . $title . '</label><div class="col-xs-12 col-sm-10">' . $optionText . '</div></div>';
            $info['template'] = $fieldHtml;
            $info['controller'] = "\$${$field}List = \$this->model->get${$studlyField}List();" . PHP_EOL . "\\think\\facade\\View::assign('${$field}List',\$${$field}List);" . PHP_EOL;
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        } elseif (($mapList = array_map(function ($value) use ($field) {
                return strpos($field, $value) !== false;
            }, ['time', 'date', 'birthday', 'year', 'month'])) && in_array(true, $mapList)) {
            $rule = 'require|integer|length:10';
            $title = $fieldInfo['comment'];
            //前端仅做简单的验证
            $info['template'] = '<div class="form-group row"><label for="' .
                $field . '">' . $title . '</label><div class="col-xs-12 col-sm-2"><input class="form-control flatpickr-input" type="text" name="' . $field
                . '" id="' . $field . '"></div></div>';
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        } elseif (strpos($fieldInfo['type'], 'set') !== false) {
            //如果字段是set类型 则表明是多选
            $title = mb_substr($fieldInfo['comment'], 0, mb_strpos($fieldInfo['comment'], ':'));
            $_comment = mb_substr($fieldInfo['comment'], mb_strpos($fieldInfo['comment'], ':') + 1);
            $_comment = explode(',', $_comment);
            $optionText = '';
            $rule = 'require';
            $listText = '';

            foreach ($_comment as $item) {
                $temp = explode('=', $item);
                $optionText .= '<option value="' . $temp[0] . '">' . $temp[1] . '</option>';
                $value = is_numeric($temp[1]) ? $temp[1] : "'{$temp[1]}'";
                $listText .= "'{$temp[0]}' => {$value},";
            }
            $studlyField = Str::studly($field);
            $functionName = 'get' . $studlyField . 'List';
            $list = '[' . $listText . ']';
            $fieldModel = str_replace('[function]', $functionName, $fieldModelTpl);
            $fieldModel = str_replace('[list]', $list, $fieldModel);
            $info['model'] = $fieldModel;
            //前端仅做简单的验证
            $info['template'] = '<div class="form-group row"><label class="col-xs-12 col-sm-2 col-form-label" for="' .
                $field . '">' . $title . '</label><select class="col-xs-12 col-sm-10 selectpicker" multiple name="' . $field . '" id="' . $field . '">' . $optionText . '</select></div>';
            $info['controller'] = "\$${$field}List = \$this->model->get${$studlyField}List();" . PHP_EOL . "\\think\\facade\\View::assign('${$field}List',\$${$field}List);" . PHP_EOL;
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        } elseif (($mapList = array_map(function ($value) use ($field) {
                return strpos($field, $value) !== false;
            }, ['password', 'pwd', 'secret'])) && in_array(true, $mapList)) {
            $rule = 'require';
            $title = $fieldInfo['comment'];
            //前端仅做简单的验证
            $info['template'] = '<div class="form-group row"><label for="' .
                $field . '">' . $title . '</label><div class="col-xs-12 col-sm-2"><input class="form-control" type="password" name="' . $field
                . '" id="' . $field . '"></div></div>';
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        } elseif (($mapList = array_map(function ($value) use ($field) {
                return strpos($field, $value) !== false;
            }, ['price', 'count', 'num', 'number', 'stock'])) && in_array(true, $mapList)) {
            $rule = 'require';
            $title = $fieldInfo['comment'];
            //前端仅做简单的验证
            $info['template'] = '<div class="form-group row"><label for="' .
                $field . '">' . $title . '</label><div class="col-xs-12 col-sm-2"><input class="form-control" type="number" name="' . $field
                . '" id="' . $field . '"></div></div>';
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        } elseif (($mapList = array_map(function ($value) use ($field) {
                return strpos($field, $value) !== false;
            }, ['email'])) && in_array(true, $mapList)) {
            $rule = 'require';
            $title = $fieldInfo['comment'];
            //前端仅做简单的验证
            $info['template'] = '<div class="form-group row"><label for="' .
                $field . '">' . $title . '</label><div class="col-xs-12 col-sm-2"><input class="form-control" data-rule="required;email"  type="email" name="' . $field
                . '" id="' . $field . '"></div></div>';
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        } elseif (($mapList = array_map(function ($value) use ($field) {
                return strpos($field, $value) !== false;
            }, ['mobile', 'phone'])) && in_array(true, $mapList)) {
            $rule = 'require';
            $title = $fieldInfo['comment'];
            //前端仅做简单的验证
            $info['template'] = '<div class="form-group row"><label for="' .
                $field . '">' . $title . '</label><div class="col-xs-12 col-sm-2"><input class="form-control" data-rule="required;mobile" type="text" name="' . $field
                . '" id="' . $field . '"></div></div>';
            $info['validateRule'] = $info['validateRule'] ? '|' . $rule : $rule;
        }
        return $info;
    }

    private function buildAddTemplate($template = '')
    {
        $_template = <<<'EOT'
        <form id="form-add" onsubmit="return false;">
            [template]
            <div class="form-group row" style="margin-top: 36px;">
            <div class="col-sm-7 row">
                <div class="col-sm-6 col-xs-12 row flex-xs-row flex-sm-row justify-content-sm-end">
                    <button type="reset" class="btn btn-danger">重置</button>
                </div>
                <div class="col-sm-6 col-xs-12 row flex-xs-row flex-sm-row justify-content-sm-center">
                    <button type="submit" class="btn btn-primary">提交</button>
                </div>
    
            </div>
    
            </div>
        </form>
EOT;
        return str_replace('[template]', $template, $_template);
    }

    private function buildEditTemplate($template = '', $primaryKey = '')
    {
        $_template = <<<'EOT'
        <form id="form-add" onsubmit="return false;">
            [template]
            <div class="form-group row" style="margin-top: 36px;">
            <div class="col-sm-7 row">
                <div class="col-sm-6 col-xs-12 row flex-xs-row flex-sm-row justify-content-sm-end">
                    <button type="reset" class="btn btn-danger">重置</button>
                </div>
                <div class="col-sm-6 col-xs-12 row flex-xs-row flex-sm-row justify-content-sm-center">
                    <button type="submit" class="btn btn-primary">提交</button>
                </div>
    
            </div>
    
            </div>
        </form>
EOT;
        return str_replace('[template]', $template, $_template);
    }
}
