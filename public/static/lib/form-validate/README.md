
Web表单验证类
===============

![Test](https://img.shields.io/badge/Test-ject-yellow)
![License](https://img.shields.io/badge/License-MIT-success)
[![Document](https://img.shields.io/badge/Document-green)](https://validate.itwmw.com/)
[![Demo](https://img.shields.io/badge/Demo-blue)](https://github.com/moniang/validate-demo)


此NPM包可用于Vue，React，Angular，小程序，NodeJS,HarmonyOS开发等项目中，如果你的程序不支持NPM，可使用npm i @itwmw/form-validate下载本包后，提取其中的index.js复制到您的项目中，在页面上引入js文件即可开始使用。

## 文档目录
* [安装使用](https://validate.itwmw.com/)
* [验证器](https://validate.itwmw.com/validate.html)
* [验证规则](https://validate.itwmw.com/rule.html)
* [错误信息](https://validate.itwmw.com/error.html)
* [验证场景](https://validate.itwmw.com/scene.html)
* [内置验证规则](https://validate.itwmw.com/built-validator.html)
* [内置过滤规则](https://validate.itwmw.com/built-filter.html)
* [独立验证](https://validate.itwmw.com/stand.html)
* [静态调用](https://validate.itwmw.com/static.html)
* [更新说明](https://validate.itwmw.com/update.html)
* [演示Demo](https://github.com/moniang/validate-demo)

## 安装
``` shell
npm i @itwmw/form-validate
```

## 使用
JavaScript ES6中的用法
```javascript
import { Validate } from '@itwmw/form-validate'
new Validate();
```
Html Script引入后
```html
<script src="path/index.js"></script>
<script>
    new Validate();
</script>
```
CommonJs中的用法
```javascript
var Validate = require('@itwmw/form-validate').Validate;
new Validate();
```


## 验证器定义
为具体的验证场景或者数据表单定义验证器类，直接调用验证类的`check`方法即可完成验证，下面是一个例子：

我们定义一个`LoginValidate `验证器类用于`登录`的验证。
```
class LoginValidate extends Index
{
    constructor()
    {
        const rules = {
            'user'  : 'require|mail|max:30',
            'pass'  : 'require|chsDash|length:6,16'
        }
        
        super(rules)
    }
}
```
可以直接在验证器类中使用`super()`方法第二个参数定义错误提示信息，例如：
~~~
class LoginValidate extends Index
{
    constructor()
    {
        const rules = {
            'user'  : 'require|mail|max:30',
            'pass'  : 'require|chsDash|length:6,16'
        }

        const message = {
            'user.require' : '用户名必须填写',
            'user.mail'    : '用户名需为邮箱',
            'user.max'     : '你使用了长度过长的邮箱号码',
            'pass.require' : '密码必须填写',
            'pass.chsDash' : '密码格式错误',
            'pass.length'  : '密码长度为6~16个字符',
        }
        super(rules,message)
    }
}
~~~
> 如果没有定义错误提示信息，则使用系统默认的提示信息

## 数据验证
在需要进行`登录`验证的控制器方法中，添加如下代码即可：
~~~
const data = {
    'user' : 'admin@admin.com',
    'pass' : '123456'
};
const login = new LoginValidate();
if(!login.check(data)){
    console.log(login.getError())
}
~~~
## 抛出验证异常
默认情况下验证失败后不会抛出异常，如果希望验证失败自动抛出异常，可以在验证器方法中进行设置：
在constructor中添加`super.fail = true` 或者在检验时添加
~~~
if(!login.check(data,true)){
    console.log(login.getError())
}
~~~
也可以使用链表操作
~~~
login.setFail(true)
~~~
设置开启了验证失败后抛出异常的话，会自动抛出ValidateException异常或者自己捕获处理。
验证规则的定义通常有三种方式，如果你使用了验证器的话，通常通过`constructor`构建函数中的`super()`方法或者修改类属性来定义验证规则，而如果使用的是独立验证的话，则是通过`setRule`方法进行定义。
## super定义
``` javascript {10}
class LoginValidate extends Validate
{
    constructor()
    {
        const rules = {
            'user'  : 'require|mail|max:30',
            'pass'  : 'require|chsDash|length:6,16'
        }
        
        super(rules)
    }
}
```
## 修改类属性
``` javascript {3-6}
class LoginValidate extends Validate
{
    rule = {
        'user'  : 'require|mail|max:30',
        'pass'  : 'require|chsDash|length:6,16'
    }
}
```
::: warning
系统内置了一些常用的验证规则可以满足大部分的验证需求，具体每个规则的含义参考[内置规则](./built-validator.md)一节。

一个字段可以使用多个验证规则（如上面的`user`字段定义了`require`和`max`，`mail`三个验证规则)
:::
## 方法定义
如果使用的是独立验证（即手动调用验证类进行验证）方式的话，通常使用`setRule`方法进行验证规则的设置，举例说明如下。
~~~
$data = {
    'user' : 'admin@admin.com',
    'pass' : '123456'
};

const rules = {
    'user'  : 'require|mail|max:30|diy:1111',
    'pass'  : 'require|chsDash|length:6,16'
};

const login = new Index();
login.setRule(rules)

if(!login.check($data)){
    console.log(login.getError())
}
~~~
## 自定义验证规则
系统内置了一些常用的规则（参考后面的内置规则），如果不能满足需求，可以在验证器重添加额外的验证方法，例如：

~~~
class User extends Index
{
    constructor(){
        const rules = {
            'name'  : 'require|check_name:michael',
            'email' : 'mail'
        };
        const message = {
            'name.require' : '用户名必须填写',
            'email.mail'   : '填入的邮箱不是有效的电子邮件地址'
        }
        super(rules,message)
    }

    check_name(value, rule, data = {})
    {
        return value === rule ? true : '用户名错误';
    }
}
~~~
验证方法可以传入的参数共有`4`个（后面两个根据情况选用），依次为：

*   验证数据
*   验证规则
*   全部数据（数组）
*   其他数据（数组）如`check_name:michael:1:2` 此处为除去`michael`以外的`1,2`数据

[全部完整文档](https://validate.itwmw.com/)