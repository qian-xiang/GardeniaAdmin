/**
 * 模拟真实场景下的测试
 */

import { BaseGetter, BaseFilter, BaseValidator, Validate } from '../index'

describe('#登录场景下的使用-Class', function() {
  class Login extends Validate {
    constructor() {
      const rule = { // 定义规则
        'user|用户名' : 'require|length:6,20|chsDash', // 账号必须填写，且长度为6~20位，只能由汉字、字母、数字和下划线_及破折号-组成
        'pass'     : 'require|length:6,20|alphaDash|check_pass', // 密码必须填写，且长度为6~20位，只能由字母和数字，下划线_及破折号-组成,以及必须通过自定义验证规则
        'code'     : 'require|number|length:6' // 验证码必须为数字，且长度为6，非必填
      }

      const msg = { // 定义错误提示消息,此处使用的{}为变量，作为示例，实际使用看个人要求
        'user.require'   : '{field}必须填写', // rule中|后跟随的字段名字，均会被{field}替换
        'user.length'    : '{field}长度错误',
        'user.chsDash'   : '{field}格式错误',
        'pass.require'   : '密码必须填写',
        'pass.length'    : '密码长度为{minPassLength}至{maxPassLength}位',
        'pass.alphaDash' : '密码格式错误',
        'code.number'    : '验证码错误',
        'code.length'    : '验证码错误',
        'code.require'   : '请填写验证码'
      }
      super(rule, msg)

      this.setScene({
        'index' : ['user', 'pass']
      })
      this.setMsg({
        'value.qq' : '请输入正确的QQ格式'
      })

      // 设置别称，用于上方错误提示，注意，此处不要设置field
      this.setAlias({
        'minPassLength' : 6,
        'maxPassLength' : 20
      })

      this.setSceneName('index') // 默认为检查账号和密码
    }

    // 设定一个自定义验证场景，删掉了用户名的长度限制
    sceneXss() {
      return this.only('user,pass').remove('user', 'length')
    }

    sceneCode() {
      return this.only(true)
    }

    // 自定义验证规则，为了下面的测试，此处先行通过
    check_pass(value, rule, data = {}) {
      return true
    }
  }

  const loginValidate = new Login()
  test('#检验结果是否通过', function() {
    const userLoginData = { // 定义一个被检验的数据
      'user' : '我是一个用户名520',
      'pass' : 'this_is_password'
    }

    expect(loginValidate.check(userLoginData, true)).toBeTruthy
  })

  test('#XSS结果是否可以通过', function() {
    const userLoginData = { // 定义一个被检验的数据
      'user' : '<script>alert("xss")</script>', // 判断是否可以被XSS
      'pass' : 'this_is_password'
    }
    expect(loginValidate.check(userLoginData)).toBeFalsy
    expect(loginValidate.getError()).toBe('用户名长度错误')

    loginValidate.setSceneName('xss') // 设置验证场景为Xss，现在不判断长度

    expect(loginValidate.check(userLoginData)).toBeFalsy
    expect(loginValidate.getError()).toBe('用户名格式错误')
  })

  test('#设定一个长度不符合的密码 ', function() {
    const userLoginData = { // 定义一个被检验的数据
      'user' : '我是一个用户名520',
      'pass' : 'this_is_password_111111111111111111111111111111'
    }
    expect(loginValidate.check(userLoginData)).toBeFalsy
    expect(loginValidate.getError()).toBe('密码长度为6至20位') // 定义的变量已经生效
  })

  test('#当值不符合要求的时候，抛出异常', function() {
    const userLoginData = { // 定义一个被检验的数据
      'user' : '我是一个用户名520',
      'pass' : 'this_is_password_111111111111111111111111111111'
    }

    expect(() => {
      loginValidate.check(userLoginData, true)
    }).toThrow()

    try {
      loginValidate.check(userLoginData, true) // check的第二个参数为是否以抛出异常的方式反馈，一次性有效
    } catch (e) {
      expect(e.getMessage()).toBe('密码长度为6至20位') // 获取错误提示消息
      expect(e.getRule()).toBe('length') // 获取没有通过的规则
      expect(e.getKey()).toBe('pass') // 获取没有通过的字段
      expect(e.getValue()).toBe('this_is_password_111111111111111111111111111111') // 获取没有通过的值
      expect(e.getError()).toBe('密码长度为6至20位') // 获取错误提示消息 同 getMessage
    }

    expect(() => {
      // 也可以使用链式操作来开启异常
      loginValidate.setFail(true) // 注意，此操作对以后的操作均生效
      loginValidate.check(userLoginData, true)
    }).toThrow()

    loginValidate.setFail(false)
  })

  test('#自定义验证规则验证', function() {
    loginValidate.extend('check_pass', function(value, rule, data = {}) { // 由于这里是跟在pass后面的，所以value就是pass的值
      if (value === '123' && data.user === 'administrator') { // 这里的data是全部的值
        return true
      }
      return '账号或密码错误，另外偷偷告诉你，密码长度为{minPassLength}至{maxPassLength}位' // 这里直接返回错误文本，而不是false，此处也可以使用{}变量
    })
    const userLoginData = { // 定义一个被检验的数据
      'user' : 'admin520',
      'pass' : 'this_is_password'
    }

    expect(loginValidate.check(userLoginData)).toBeFalsy()
    expect(loginValidate.getError()).toBe('账号或密码错误，另外偷偷告诉你，密码长度为6至20位')
  })

  test('#当登录需要验证码', function() {
    const userLoginData = { // 定义一个被检验的数据
      'user' : '我是一个用户名520',
      'pass' : 'this_is_password'
    }

    expect(loginValidate.setSceneName('code').check(userLoginData)).toBeFalsy()
  })

  test('#在自定义验证场景外使用了验证场景函数', function() {
    const userLoginData = { // 定义一个被检验的数据
      'code' : '123456',
      'pass' : '111111111111111111111111111111111111111111111'
    }
    expect(loginValidate.only('code').check(userLoginData)).toBeTruthy()
    expect(loginValidate.only('code,pass').remove('pass', 'length|check_pass').check(userLoginData)).toBeTruthy()
    expect(loginValidate.only(true).remove('pass', 'length|check_pass').remove('user', null).check(userLoginData)).toBeTruthy() // 选择检验全部字段，但是删除user的全部规则和pass的长度限制
  })
})

describe('# 其他Demo', function() {
  test('#自定义正则规则', function() {
    const validate = new Validate()
    const valiRule = new BaseValidator()

    valiRule.setRegex({
      'qq' : /[1-9][0-9]{4,}/
    })

    validate.register(valiRule)

    validate.setRule({
      'qq' : 'require|qq'
    })

    validate.check({ 'qq' : 'sssssss' })

    expect(validate.getError()).toBe('qq验证失败')
  })

  test('# 非必填是否验证规则', function() {
    const vali = Validate.make({
      'tel' : 'mobile'
    })
    vali.check({}, true)
  })
})

describe('#一个多个地方通用的验证-分页', function() {
  const pageValidate = Validate.make({
    'page' : 'require|number', // 页数必填，且必须是数字
    'size' : 'require|number|in:1,5,10,20' // 每页数据量必填，且必须是数字，并且在1,5,10,20这四个选项中(此处number验证可以不要，毕竟in已经限制死了)
  }, {
    'page.require' : '{field}必须填写',
    'page.number'  : '{field}填写错误',
    'size.size'    : '{field}必须填写',
    'size.number'  : '{field}填写错误',
    'size.in'      : '{field}填写错误'
  })

  test('#获取用户列表分页', function() {
    let userData = { // 模拟用户数据
      'page' : 1,
      'size' : 5
    }

    expect(pageValidate.check(userData)).toBeTruthy()

    userData = { // 模拟用户数据
      'page' : 1,
      'size' : 9
    } // 获取第一页，每页9条数据

    pageValidate.setField({ // 设置字段的名称，也可以在rule后的|加上
      'page' : '用户列表页数',
      'size' : '用户列表每页展现数据量'
    })
    expect(pageValidate.check(userData)).toBeFalsy()
    expect(pageValidate.getError()).toBe('用户列表每页展现数据量填写错误')
  })

  test('#获取商品列表分页', function() {
    let userData = { // 模拟用户数据
      'page' : 1,
      'size' : 5
    }

    expect(pageValidate.check(userData)).toBeTruthy()

    userData = { // 模拟用户数据
      'page' : 5,
      'size' : 1000
    } // 获取第五页，每页1000条数据

    pageValidate.setField({ // 设置字段的名称，也可以在rule后的|加上
      'page' : '商品列表页数',
      'size' : '商品列表每页展现数据量'
    })
    expect(pageValidate.check(userData)).toBeFalsy()
    expect(pageValidate.getError()).toBe('商品列表每页展现数据量填写错误')
  })
})

describe('# sometimes测试', function() {
  const v = new Validate()

  test('# 一个单key的sometimes条件', function() {
    const checked = {
      'age'     : 10000,
      'species' : 'human'
    }

    v.sometimes('species', 'notIn:human,dog', function(data) {
      return data.age > 200 // 如果年龄大于200岁，则判断它不是人，也不是狗
    })
    v.setMsg({
      'species.notIn' : '不可能，人和狗不可能超过200岁！！！'
    })

    expect(v.check(checked)).toBeFalsy()
    expect(v.getError()).toBe('不可能，人和狗不可能超过200岁！！！')
    checked.age = 100
    expect(v.check(checked)).toBeTruthy()
  })

  test('# 多个key的sometimes条件', function() {
    const checked = {
      'gender' : 'girl',
      'money'  : -10000,
      'room'   : 0,
      'car'    : 0
    }

    v.sometimes(['room', 'car'], 'egt:1', function(data) {
      return data.gender === 'boy' // 如果你是个男的，你至少需要有一套房，一辆车
    })

    v.sometimes('money', 'min:10000000', function(data) {
      return data.gender === 'boy' // 如果你是个男的，你至少需要有1000万积蓄
    })

    v.setMsg({
      'room.egt'  : '房都没有！快走开！',
      'car.egt'   : '车都没有！快走开！',
      'money.min' : '一千万都没有，你拿什么养活我？'
    })

    expect(v.check(checked)).toBeTruthy()
    checked.gender = 'boy'
    expect(v.check(checked)).toBeFalsy()
    expect(v.getError()).toBe('房都没有！快走开！')

    checked.room = 1 // 当你有房了
    expect(v.check(checked)).toBeFalsy()
    expect(v.getError()).toBe('车都没有！快走开！')

    checked.car = 1 // 当你有车了
    expect(v.check(checked)).toBeFalsy()
    expect(v.getError()).toBe('一千万都没有，你拿什么养活我？')
  })
})

describe('# 过滤器，获取器，sometimes的混用', function() {
  test('# 共同完成验证', function() {
    class TestFilter extends BaseFilter {
      toMonth(value) {
        if (value > 12) {
          return new Date(value).getMonth() + 1
        }
        return value
      }
    }

    class TestData extends BaseGetter {
      getMonthAttr(value) {
        if (value === undefined) {
          return (new Date()).valueOf()
        }
        return value
      }
    }

    class TestValidate extends Validate {
      constructor(props) {
        const rule = {
          'month' : 'require'
        }
        const message = {
          'month.require' : 'month必须填写',
          'month.between' : 'month不在指定范围内'
        }
        super(rule, message)
      }
    }

    const v = new TestValidate()
    v.register(new TestFilter())
    v.register(new TestData())
    v.sometimes('month', 'between:1,12', function(value) {
      return true
    })

    v.setFilter({
      'month' : 'toMonth'
    })

    expect(v.check({})).toBeTruthy()
    expect(v.getData().month).toEqual(new Date().getMonth() + 1)
  })
})
