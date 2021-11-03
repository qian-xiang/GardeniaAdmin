import { Validate } from '../index'

describe('# 测试错误消息是否正确', function() {
  test('# 测试规则的默认错误信息是否生效', function() {
    const v = Validate.make({
      'user' : 'require'
    })

    expect(v.check({})).toBeFalsy()
    expect(v.getError()).toEqual('user必须填写')
  })

  test('# 测试给规则定义一个错误消息是否生效', function() {
    const v = Validate.make({
      'user' : 'require'
    }, {
      'user.require' : '账号必须填写'
    })

    expect(v.check({})).toBeFalsy()
    expect(v.getError()).toEqual('账号必须填写')
  })

  test('# 测试给规则中的自定义别称是否生效', function() {
    const v = Validate.make({
      'phone|手机' : 'mobile'
    }, {
      'phone.mobile' : '{field}格式错误，如有区号如：{example}请去除'
    })

    v.setAlias({
      example : '+86'
    })

    expect(v.check({ phone : 123456 })).toBeFalsy()
    expect(v.getError()).toEqual('手机格式错误，如有区号如：+86请去除')
  })

  test('# 测试对字段定义别称是否生效-1', function() {
    const form = {
      'user' : ''
    }

    const v = Validate.make({
      'user' : 'require'
    })

    v.setField({
      'user' : '账号'
    })

    expect(v.check(form)).toBeFalsy()
    expect(v.getError()).toEqual('账号必须填写')
  })

  test('# 测试对字段定义别称是否生效-2', function() {
    const form = {
      'user' : ''
    }

    const v = Validate.make({
      'user|账号' : 'require'
    })

    expect(v.check(form)).toBeFalsy()
    expect(v.getError()).toEqual('账号必须填写')
  })

  test('# 测试对字段定义别称是否生效-3', function() {
    class TestValidate extends Validate {
      rule = {
        'user' : 'require'
      }

      field = {
        'user' : '账号'
      }
    }
    const form = {
      'user' : ''
    }

    const v = TestValidate.make()

    expect(v.check(form)).toBeFalsy()
    expect(v.getError()).toEqual('账号必须填写')
  })

  test('# 测试错误消息中包含字段内容是否生效', function() {
    const v = Validate.make({
      'id' : 'number'
    }, {
      'id.number' : 'ID错误，{:id}不是一个数字'
    })

    expect(v.check({ id : 'abc' })).toBeFalsy()
    expect(v.getError()).toEqual('ID错误，abc不是一个数字')
  })
})
