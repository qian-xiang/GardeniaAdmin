import { Validate, BaseFilter } from '../index'

describe('# 过滤器的使用测试', function() {
  // 定义一个过滤器
  class TestFilter extends BaseFilter {
    getStatus(value) {
      switch (value) {
        case '正常':
          return '0'
        case '到期':
          return '1'
        case '封禁':
          return '-1'
      }
      return value
    }
  }

  // 定义一个验证器
  class TestValidate extends Validate {
    constructor() {
      super()

      // 让此验证器使用自定义的过滤器类,如果你没有使用自定义过滤方法的话，可以不填这里
      this.register(new TestFilter())
    }
  }
  test('# 测试使用过滤器修改状态', function() {
    const v = new TestValidate()
    const rule = {
      'status' : 'require|in:-1,0,1'
    }
    const message = {
      'status.require' : '状态必须填写',
      'status.in'      : '状态错误'
    }

    v.setRule(rule)
    v.setMsg(message)
    v.setFilter({ // 让status使用过滤器中的getStatus方法
      'status' : 'getStatus,toInt'
    })

    const testData = {
      'status' : '正常'
    }
    expect(v.check(testData)).toBeTruthy()

    // getData取回过滤后的数据，仅在check通过后有效
    expect(v.getData().status).toEqual(0)

    v.setFilter(null) // 清空过滤器

    expect(v.check(testData)).toBeFalsy() // 由于规则制定status必须是-1,0,1三个元素的任意一个，而目前status没有经过过滤器，还是“正常”
    expect(v.getError()).toEqual('状态错误')
  })

  test('# 使用过滤器加载动态过滤方法', function() {
    const testFilter = new TestFilter()
    testFilter.extend('toSeason', function(value) {
      switch (value) {
        case value >= 3 && value <= 5:
          return '春'
        case value >= 6 && value <= 8:
          return '夏'
        case value >= 9 && value <= 11:
          return '秋'
        default:
          return '冬'
      }
    })

    const v = new TestValidate()
    v.setRule({
      'season' : 'require|in:春,夏,秋,冬'
    })

    // 让此验证器使用自定义的过滤器类
    v.register(testFilter)

    // 让status使用过滤器中的getStatus方法
    v.setFilter({
      'season' : ['toInt', 'toSeason']
    })

    const testData = {
      'season' : '12'
    }
    expect(v.check(testData)).toBeTruthy()
    expect(v.getData().season).toEqual('冬')
  })

  test('# 使用验证器加载动态过滤器方法', function() {
    const v = new TestValidate()
    v.extend('filterToSeason', function(value) {
      switch (value) {
        case value >= 3 && value <= 5:
          return '春'
        case value >= 6 && value <= 8:
          return '夏'
        case value >= 9 && value <= 11:
          return '秋'
        default:
          return '冬'
      }
    })

    v.setRule({
      'season' : 'require|in:春,夏,秋,冬'
    })

    // 让status使用过滤器中的getStatus方法
    v.setFilter({
      'season' : ['toInt', 'toSeason']
    })

    const testData = {
      'season' : '12'
    }
    expect(v.check(testData)).toBeTruthy()
    expect(v.getData().season).toEqual('冬')
  })
})
