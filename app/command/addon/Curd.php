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
            ->addOption('table','t',Option::VALUE_REQUIRED,'数据库表名称，可不加表前缀','gardenia')
            ->addOption('controller','c',Option::VALUE_OPTIONAL,'控制器名，无需加控制器后缀，可在前面加目录名称以/相隔','')
            ->addOption('model','m',Option::VALUE_OPTIONAL,'模型名，可在前面加目录名称以/相隔','')
            ->addOption('field','f',Option::VALUE_OPTIONAL,'显示字段，*=全部，多个字段用半角逗号,隔开')
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
        switch ($arguments['operate']) {
            case 'c';
                //执行新增curd操作
                //先创建模型
                $modelTplPath = base_path('common').'/core/tpl/curd/model.tpl';
                $content = file_get_contents($modelTplPath);
                $content = str_replace('[appName]',$options['app'],$content);
                $content = str_replace('[modelName]',$options['model'],$content);
                $modelPath = base_path($options['app'].DIRECTORY_SEPARATOR).'model'.DIRECTORY_SEPARATOR.Str::studly($options['model']).'.php';
                $res = file_put_contents($modelPath,$content);
                if (!$res) {
                    throw new AppException('新增模型失败，请稍候重试');
                }
                //再创建控制器
                $modelTplPath = base_path('common').'/core/tpl/curd/controller.tpl';
                $content = file_get_contents($modelTplPath);
                $content = str_replace('[appName]',$options['app'],$content);
                $content = str_replace('[controllerName]',$options['controller'],$content);
                $controllerPath = base_path($options['app'].DIRECTORY_SEPARATOR).'controller'.DIRECTORY_SEPARATOR.Str::studly($options['controller']).(config('route.controller_suffix') ? 'Controller' : '').'.php';
                $res = file_put_contents($controllerPath,$content);
                if (!$res) {
                    throw new AppException('新增控制器失败，请稍候重试');
                }
                //新增视图文件

                break;
            case 'd';
                break;
            default:
                throw new AppException('operate参数暂不支持该值');
        }
        // 指令输出
        $output->writeln('curd');
    }
}
