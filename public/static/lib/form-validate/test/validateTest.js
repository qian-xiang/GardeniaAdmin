var chai = require('chai')
var expect = chai.expect
import { Validate } from '../index'

describe('#验证器的定义', function() {
  const rule = {
    'name' : 'require'
  }

  const message = {
    'name.require' : '名字不可为空'
  }

  it('#实例化验证码定义规则和消息', function() {
    const validate = new Validate(rule, message)
    expect(validate.rule).to.eql(rule)
    expect(validate.msg).to.eql(message)
  })

  it('#静态方法验证码定义规则和消息', function() {
    const validate = Validate.make(rule, message)
    expect(validate.rule).to.eql(rule)
    expect(validate.msg).to.eql(message)
  })

  it('#通过方法定义规则和消息', function() {
    const validate = Validate.make()
    validate.setRule(rule)
    validate.setMsg(message)
    expect(validate.rule).to.eql(rule)
    expect(validate.msg).to.eql(message)
  })

  it('#通过类的继承来定义', function() {
    class Test extends Validate {
      constructor() {
        super(rule, message)
      }
    }
    const validate = new Test()
    expect(validate.rule).to.eql(rule)
    expect(validate.msg).to.eql(message)
  })

  it('#通过类的继承后用方法来定义', function() {
    class Test extends Validate {

    }
    const validate = new Test()
    validate.setRule(rule)
    validate.setMsg(message)
    expect(validate.rule).to.eql(rule)
    expect(validate.msg).to.eql(message)
  })
})

describe('#验证场景相关', function() {
  // class TestValidate extends Validate {
  //   constructor() {
  //     const rule = {
  //       'phone|手机号' : 'require|mobile'
  //     }
  //
  //     const message = {
  //       'phone.require' : '请填写{field}',
  //       'phone.mobile'  : '{field}格式错误'
  //     }
  //     super(rule, message)
  //   }
  // }

  it('#添加验证规则', function() {

  })
})
