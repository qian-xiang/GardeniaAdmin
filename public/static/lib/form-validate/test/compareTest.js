import { Validate } from '../index'

describe('# 字段比较类验证', function() {
  class Login extends Validate {
    constructor() {
      const rule = {
        'user' : 'require|confirm:info.name',
        'pass' : 'require|confirm',
        'code' : 'require|=:100'
      }

      const message = {
        'user.require' : '用户名必须填写',
        'user.confirm' : '用户名和用户信息不一致'
      }
      super(rule, message)

      this.setScene({
        'user' : 'user',
        'pass' : 'pass',
        'code' : 'code'
      })
    }
  }

  const LoginValidate = new Login()

  test('# 测试用户名是否与用户信息中的名字相等', function() {
    const userValue = {
      'user' : 'xieshao',
      'info' : {
        'name' : 'moniang'
      }
    }
    expect(LoginValidate.setSceneName('user').check(userValue)).toBeFalsy()
    userValue.info.name = 'xieshao'
    expect(LoginValidate.setSceneName('user').check(userValue)).toBeTruthy()
  })

  test('# 测试密码字段对比不填入指定字段名', function() {
    const userValue = {
      'pass' : '123456'
    }
    expect(LoginValidate.setSceneName('pass').check(userValue)).toBeFalsy()
    userValue.pass_confirm = '123456'
    expect(LoginValidate.setSceneName('pass').check(userValue)).toBeTruthy()
  })

  test('# 测试验证码为等于指定值', function() {
    const userValue = {
      'code' : '123456'
    }
    expect(LoginValidate.setSceneName('code').check(userValue)).toBeFalsy()
    userValue.code = '100'
    expect(LoginValidate.setSceneName('code').check(userValue)).toBeTruthy()
  })

  test('# 测试大于等于指定值', function() {
    const userValue = {
      'code' : '10'
    }
    LoginValidate.extend('sceneCodeEqt', function() {
      return this.only(['code']).remove('code', '=').append('code', '>=:100')
    })

    LoginValidate.setMsg({
      'code.eq' : '不符合要求值',
      'code.>=' : '不符合要求值2'
    })

    expect(LoginValidate.setSceneName('codeEqt').check(userValue)).toBeFalsy()
    expect(LoginValidate.getError()).toEqual('不符合要求值2')
    userValue.code = 100
    expect(LoginValidate.setSceneName('codeEqt').check(userValue)).toBeTruthy()
  })

  test('# 测试大于指定值', function() {
    const userValue = {
      'code' : '10'
    }
    LoginValidate.extend('sceneCodeQt', function() {
      return this.only(['code']).remove('code', '=').append('code', '>:100')
    })

    expect(LoginValidate.setSceneName('codeQt').check(userValue)).toBeFalsy()
    userValue.code = 101
    expect(LoginValidate.setSceneName('codeQt').check(userValue)).toBeTruthy()
  })

  test('# 测试小于等于指定值', function() {
    const userValue = {
      'code' : '100'
    }
    LoginValidate.extend('sceneCodeElt', function() {
      return this.only(['code']).remove('code', '=').append('code', 'elt:100')
    })

    expect(LoginValidate.setSceneName('codeElt').check(userValue)).toBeTruthy()
    userValue.code = 101
    expect(LoginValidate.setSceneName('codeElt').check(userValue)).toBeFalsy()
  })

  test('# 测试小于指定值', function() {
    const userValue = {
      'code' : '99'
    }
    LoginValidate.extend('sceneCodeLt', function() {
      return this.only(['code']).remove('code', '=').append('code', 'lt:100')
    })

    expect(LoginValidate.setSceneName('codeLt').check(userValue)).toBeTruthy()
    userValue.code = 101
    expect(LoginValidate.setSceneName('codeLt').check(userValue)).toBeFalsy()
  })
})
