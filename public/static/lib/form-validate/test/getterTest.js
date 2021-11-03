import { Validate, BaseGetter } from '../index'

describe('# 获取器的使用测试', function() {
  class TestData extends BaseGetter {
    getStatusAttr(value) {
      if (value === undefined) {
        return 1
      }
      return value
    }
  }

  class TestValidate extends Validate {

  }

  test('# 数据获取测试', function() {
    class DataValidate extends Validate {
            rule = {
              'a'      : 'require',
              'b'      : '',
              'status' : []
            }

            scene = {
              'test' : 'a,status,d'
            }
    }

    const data = {
      'a' : 1,
      'b' : 2,
      'c' : 5,
      'd' : 100
    }
    const v = new DataValidate()
    v.register(new TestData()).setSceneName('test')
    expect(v.check(data)).toBeTruthy()
    expect(v.getData().status).toEqual(1)
    expect(v.getData().d).toEqual(100)
  })

  test('# 通过获取器设定一个默认值', function() {
    const v = new TestValidate()
    v.setRule({
      'status' : 'require|number'
    })

    v.register(new TestData()) // 将获取器注册到验证器内
    v.check({})
    expect(v.check({})).toBeTruthy()
    expect(v.getData().status).toEqual(1)
  })

  test('# 获取器通过另一个属性得到一个值', function() {
    const data = new TestData()
    data.extend('getSeasonAttr', function(value, data) {
      switch (true) {
        case data.month >= 3 && data.month <= 5:
          return '春'
        case data.month >= 6 && data.month <= 8:
          return '夏'
        case data.month >= 9 && data.month <= 11:
          return '秋'
        default:
          return '冬'
      }
    })

    const v = new TestValidate()
    v.register(data) // 将获取器注册到验证器内

    v.setRule({
      'month'  : 'require|between:1,12',
      'season' : 'require'
    })

    const testData = {
      month : 11
    }

    expect(v.check(testData)).toBeTruthy()
    expect(v.getData().season).toEqual('秋')

    v.init()

    expect(v.check(testData)).toBeFalsy()
  })

  test('# 通过验证器来进行获取器的使用', function() {
    const v = new Validate()
    v.getGetter().extend('getNameAttr', function(value) {
      return '墨娘'
    })

    v.setRule({
      'name' : 'require'
    })

    expect(v.check({})).toBeTruthy()
    expect(v.getData().name).toEqual('墨娘')
  })
})
