/**
 * 破坏性测试，不良测试
 */

import { Validate } from '../index'

describe('#不走寻常路', function() {
  test('#不传对象规则', function() {
    expect(() => {
      Validate.make([12, 3], { 1 : 2 })
    }).toThrow()
  })

  test('#不传对象消息', function() {
    expect(() => {
      Validate.make({ 1 : 2 }, 123)
    }).toThrow()
  })

  test('#不传对象值', function() {
    const validate = Validate.make({
      'message' : 'require'
    }, { 1 : 2 })
    expect(() => {
      validate.check(false)
    }).toThrow()
  })

  test('#不传字符串对应规则', function() {
    const validate = Validate.make({
      'name'    : 'require|number',
      'message' : ['require', 'number']
    }, { 1 : 2 })
    validate.check({
      'name'    : 123,
      'message' : 'ssssssss'
    })
    expect(validate.getError()).toBe('message不是有效的数值')
    expect(() => {
      Validate.make({
        'message' : { 'require' : '123' }
      }, { 1 : 2 }).check({})
    }).toThrow()
  })

  test('#移除验证中不传数组也不传字符串', function() {
    const validate = Validate.make({
      'name'    : 'require|number',
      'message' : ['require', 'number']
    }, { 1 : 2 })
    expect(() => {
      validate.remove('name', { 1 : 2 }).check({ 1 : 2 })
    }).toThrow()
  })

  test('#添加验证中不传数组也不传字符串', function() {
    const validate = Validate.make({
      'name'    : 'require|number',
      'message' : ['require', 'number']
    }, { 1 : 2 })
    expect(() => {
      validate.append('name', { 1 : 2 }).check({ 1 : 2 })
    }).toThrow()
  })
})
