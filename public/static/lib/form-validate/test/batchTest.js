/**
 * 批量验证测试
 * @type {*}
 */
import { Validate } from '../index'

describe('# 批量验证', function() {
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
  const vali = Validate.make(rule, msg)

  vali.extend('check_pass', (value, rule, data = {}) => {
    return '自定义规则验证失败'
  })

  vali.setBatch(true) // 开启批量验证
  test('# 所有值不填写', function() {
    const user = {}
    vali.check(user)
    expect(vali.getError().length).toEqual(3)
  })

  test('# 填写一个正确值，并使用异常抛出', function() {
    const user = {
      'user' : '123456'
    }
    expect(() => {
      vali.check(user, true)
    }).toThrow()
    vali.check(user)
    expect(vali.getError().length).toEqual(2)
  })
})
