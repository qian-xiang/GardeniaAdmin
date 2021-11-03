var chai = require('chai')
var expect = chai.expect
import { ValidateRule as Validate } from '../index'

describe('#isNumber是否为数字', function() {
  it('#值为数字：123,返回true', function() {
    expect(Validate.isNumber(123)).to.be.true
  })

  it('#值为字符串：hello，返回false', function() {
    expect(Validate.isNumber('hello')).to.be.false
  })

  it('#值为字符串数字: 123,返回true', function() {
    expect(Validate.isNumber('123')).to.be.true
  })

  it('#值为字符串小数: 123.22,返回true', function() {
    expect(Validate.isNumber('123.22')).to.be.true
  })

  it('#值为字符串负数: -123.22,返回true', function() {
    expect(Validate.isNumber('-123.22')).to.be.true
  })

  it('#值为浮点数负数: -123.22,返回true', function() {
    expect(Validate.isNumber(-123.22)).to.be.true
  })

  it('#值为整数: 1,返回true', function() {
    expect(Validate.isNumber(1)).to.be.true
  })

  it('#值为字符串: 1.,返回false', function() {
    expect(Validate.isNumber('1.')).to.be.false
  })
})

describe('#isChsDash是否为汉字，字母，数字，下划线_破折号-组成', function() {
  it('#值为Password,返回true', function() {
    expect(Validate.isChsDash('Password')).to.be.true
  })

  it('#值为 alert("xss"),返回false', function() {
    expect(Validate.isChsDash('lert("xss")')).to.be.false
  })

  it('#值为 我的用户名_52100,返回true', function() {
    expect(Validate.isChsDash('我的用户名_52100')).to.be.true
  })

  it('#值为 <p style="color: red">name</p>,返回false', function() {
    expect(Validate.isChsDash('<p style="color: red">name</p>')).to.be.false
  })

  it('#值为 123456,返回true', function() {
    expect(Validate.isChsDash('123456')).to.be.true
  })

  it('#值为 AAAAAA,返回true', function() {
    expect(Validate.isChsDash('AAAAAA')).to.be.true
  })

  it('#值为 __-_-__,返回true', function() {
    expect(Validate.isChsDash('__-_-__')).to.be.true
  })

  it('#值为 马云520 ,返回true', function() {
    expect(Validate.isChsDash('马云520')).to.be.true
  })

  it('#值为 邪少 ,返回true', function() {
    expect(Validate.isChsDash('邪少')).to.be.true
  })
})

describe('#isChsAlphaNum,是否为汉字，字母和数字', function() {
  it('#值为Password,返回true', function() {
    expect(Validate.isChsAlphaNum('Password')).to.be.true
  })

  it('#值为 alert("xss"),返回false', function() {
    expect(Validate.isChsAlphaNum('lert("xss")')).to.be.false
  })

  it('#值为 我的用户名_52100,返回false', function() {
    expect(Validate.isChsAlphaNum('我的用户名_52100')).to.be.false
  })

  it('#值为 <p style="color: red">name</p>,返回false', function() {
    expect(Validate.isChsAlphaNum('<p style="color: red">name</p>')).to.be.false
  })

  it('#值为 123456,返回true', function() {
    expect(Validate.isChsAlphaNum('123456')).to.be.true
  })

  it('#值为 AAAAAA,返回true', function() {
    expect(Validate.isChsAlphaNum('AAAAAA')).to.be.true
  })

  it('#值为 __-_-__,返回false', function() {
    expect(Validate.isChsAlphaNum('__-_-__')).to.be.false
  })

  it('#值为 马云520 ,返回true', function() {
    expect(Validate.isChsAlphaNum('马云520')).to.be.true
  })

  it('#值为 邪少 ,返回true', function() {
    expect(Validate.isChsAlphaNum('邪少')).to.be.true
  })
})

describe('#isChsAlpha,是否为汉字、字母', function() {
  it('#值为Password,返回true', function() {
    expect(Validate.isChsAlpha('Password')).to.be.true
  })

  it('#值为 alert("xss"),返回false', function() {
    expect(Validate.isChsAlpha('lert("xss")')).to.be.false
  })

  it('#值为 我的用户名_52100,返回false', function() {
    expect(Validate.isChsAlpha('我的用户名_52100')).to.be.false
  })

  it('#值为 <p style="color: red">name</p>,返回false', function() {
    expect(Validate.isChsAlpha('<p style="color: red">name</p>')).to.be.false
  })

  it('#值为 123456,返回false', function() {
    expect(Validate.isChsAlpha('123456')).to.be.false
  })

  it('#值为 AAAAAA,返回true', function() {
    expect(Validate.isChsAlpha('AAAAAA')).to.be.true
  })

  it('#值为 __-_-__,返回false', function() {
    expect(Validate.isChsAlpha('__-_-__')).to.be.false
  })

  it('#值为 马云520 ,返回false', function() {
    expect(Validate.isChsAlpha('马云520')).to.be.false
  })

  it('#值为 邪少 ,返回true', function() {
    expect(Validate.isChsAlpha('邪少')).to.be.true
  })
})

describe('#isChs,是否为汉字', function() {
  it('#值为Password,返回false', function() {
    expect(Validate.isChs('Password')).to.be.false
  })

  it('#值为 alert("xss"),返回false', function() {
    expect(Validate.isChs('lert("xss")')).to.be.false
  })

  it('#值为 我的用户名_52100,返回false', function() {
    expect(Validate.isChs('我的用户名_52100')).to.be.false
  })

  it('#值为 <p style="color: red">name</p>,返回false', function() {
    expect(Validate.isChs('<p style="color: red">name</p>')).to.be.false
  })

  it('#值为 123456,返回false', function() {
    expect(Validate.isChs('123456')).to.be.false
  })

  it('#值为 AAAAAA,返回false', function() {
    expect(Validate.isChs('AAAAAA')).to.be.false
  })

  it('#值为 __-_-__,返回false', function() {
    expect(Validate.isChs('__-_-__')).to.be.false
  })

  it('#值为 马云520 ,返回false', function() {
    expect(Validate.isChs('马云520')).to.be.false
  })

  it('#值为 邪少 ,返回true', function() {
    expect(Validate.isChs('邪少')).to.be.true
  })
})

describe('#isCreaditCode,是否为统一社会信用代码', function() {
  it('#值为91330301MA2HD7B65Q，返回true ', function() {
    expect(Validate.isCreaditCode('91330301MA2HD7B65Q')).to.be.true
  })

  it('#值为91330301MA2HD7B650，返回true ', function() {
    expect(Validate.isCreaditCode('913100007109328220')).to.be.true
  })

  it('#值为91330100799655058B，返回true ', function() {
    expect(Validate.isCreaditCode('91330100799655058B')).to.be.true
  })

  it('#值为91330100799655058BX，返回false ', function() {
    expect(Validate.isCreaditCode('91330100799655058X')).to.be.false
  })

  it('#值为123456，返回false ', function() {
    expect(Validate.isCreaditCode('123456')).to.be.false
  })
})

describe('#length,长度判断', function() {
  it('#值为aaaaa,长度限制为1-6位，返回true ', function() {
    expect(Validate.isLength('aaaaa', '1,6')).to.be.true
  })

  it('#值为aaaaa,长度限制为4位，返回false', function() {
    expect(Validate.isLength('aaaaa', '4')).to.be.false
  })

  it('#值为aaaaa,长度限制为5位，返回true', function() {
    expect(Validate.isLength('aaaaa', '5')).to.be.true
  })

  it('#值为数值123456,长度限制为5位，返回false', function() {
    expect(Validate.isLength(123456, '5')).to.be.false
  })

  it('#值为数值123456,长度限制为6位，返回true', function() {
    expect(Validate.isLength(123456, '6')).to.be.true
  })

  it('#值为数组[1,2,3,4],长度限制为1-5位，返回true', function() {
    expect(Validate.isLength([1, 2, 3, 4], '1,5')).to.be.true
  })

  it('#值为数组[1,2,3,4],长度限制为空，返回false', function() {
    expect(Validate.isLength([1, 2, 3, 4])).to.be.false
  })

  it('#值为对象{a:1,b:2},长度限制为空，返回false', function() {
    expect(Validate.isLength({ a : 1, b : 2 })).to.be.false
  })

  it('#值为对象{a:1,b:2},长度限制为2，返回false', function() {
    expect(Validate.isLength({ a : 1, b : 2 }, 2)).to.be.false
  })
})

describe('#between,判断字段值是否在指定范围', function() {
  it('#值为5,判断是否在1-6之间，返回true', function() {
    expect(Validate.between(5, '1,6')).to.be.true
  })

  it('#值为0,判断是否在1-6之间，返回false', function() {
    expect(Validate.between(0, '1,6')).to.be.false
  })

  it('#值为5555,判断是否在1-6之间，返回false ', function() {
    expect(Validate.between(5555, '1,6')).to.be.false
  })

  it('#值为H,判断是否在A,I之间，返回true ', function() {
    expect(Validate.between('H', 'A,I')).to.be.true
  })

  it('#值为H,判断是否在A,B之间，返回false ', function() {
    expect(Validate.between('H', 'A,B')).to.be.false
  })

  it('#值为H,判断为未定义，返回false', function() {
    expect(Validate.between('H')).to.be.false
  })

  it('#值为未定义,判断是否在A,B之间，返回false', function() {
    expect(Validate.between(undefined, 'A,B')).to.be.false
  })

  it('#值为H,判断为不符合要求的长度1,2,3，返回false', function() {
    expect(Validate.between('H', '1,2,3')).to.be.false
  })
})

describe('#notBetween,判断字段值不是否在指定范围', function() {
  it('#值为5,判断是否在1-6之间，返回false', function() {
    expect(Validate.notBetween(5, '1,6')).to.be.false
  })

  it('#值为0,判断是否在1-6之间，返回true', function() {
    expect(Validate.notBetween(0, '1,6')).to.be.true
  })

  it('#值为5555,判断是否在1-6之间，返回true', function() {
    expect(Validate.notBetween(5555, '1,6')).to.be.true
  })

  it('#值为H,判断是否在A,I之间，返回false', function() {
    expect(Validate.notBetween('H', 'A,I')).to.be.false
  })

  it('#值为H,判断是否在A,B之间，返回true', function() {
    expect(Validate.notBetween('H', 'A,B')).to.be.true
  })

  it('#值为H,判断为未定义，返回false', function() {
    expect(Validate.notBetween('H')).to.be.false
  })

  it('#值为G,判断是否在未定义之间，返回false', function() {
    expect(Validate.notBetween('G')).to.be.false
  })
})

describe('#in,判断指定的值是否在要求的内容中', function() {
  it('#值为字符串on,判断是否在on,off之内，返回true', function() {
    expect(Validate.in('on', 'on,off')).to.be.true
  })

  it('#值为数值1,判断是否在1,2,3,4之内，返回true', function() {
    expect(Validate.in(1, '1,2,3,4')).to.be.true
  })

  it('#值为字符串on,判断是否在1,2,3,4之内，返回false', function() {
    expect(Validate.in('on', '1,2,3,4')).to.be.false
  })

  it('#值为字符串on,判断是否在数组["on","off"]之内，返回true', function() {
    expect(Validate.in('on', ['on', 'off'])).to.be.true
  })

  it('#值为数值NaN,判断是否在数组[0,NaN,null,undefined]之内，返回true', function() {
    expect(Validate.in(NaN, [0, NaN, null, undefined])).to.be.true
  })

  it('#值为数值NaN,判断是否在数组["0","NaN","null","undefined"]之内，返回true', function() {
    expect(Validate.in(NaN, ['0', 'NaN', 'null', 'undefined'])).to.be.true
  })

  it('#值为字符串undefined,判断是否在数组["0","NaN","null",undefined]之内，返回true', function() {
    expect(Validate.in('undefined', ['0', 'NaN', 'null', undefined])).to.be.true
  })

  it('#值为字符串undefined,判断条件为undefined，返回false', function() {
    expect(Validate.in('undefined', undefined)).to.be.false
  })
})

describe('#notIn,判断指定的值是否不在要求的内容中', function() {
  it('#值为字符串on,判断是否在on,off之内，返回false', function() {
    expect(Validate.notIn('on', 'on,off')).to.be.false
  })

  it('#值为数值1,判断是否在1,2,3,4之内，返回false', function() {
    expect(Validate.notIn(1, '1,2,3,4')).to.be.false
  })

  it('#值为字符串on,判断是否在1,2,3,4之内，返回true', function() {
    expect(Validate.notIn('on', '1,2,3,4')).to.be.true
  })

  it('#值为字符串on,判断是否在数组["on","off"]之内，返回false', function() {
    expect(Validate.notIn('on', ['on', 'off'])).to.be.false
  })

  it('#值为数值NaN,判断是否在数组[0,NaN,null,undefined]之内，返回false', function() {
    expect(Validate.notIn(NaN, [0, NaN, null, undefined])).to.be.false
  })

  it('#值为数值NaN,判断是否在数组["0","NaN","null","undefined"]之内，返回false', function() {
    expect(Validate.notIn(NaN, ['0', 'NaN', 'null', 'undefined'])).to.be.false
  })

  it('#值为字符串undefined,判断是否在数组["0","NaN","null",undefined]之内，返回false', function() {
    expect(Validate.notIn('undefined', ['0', 'NaN', 'null', undefined])).to.be.false
  })

  it('#值为字符串undefined,判断条件为undefined，返回false', function() {
    expect(Validate.notIn('undefined', undefined)).to.be.false
  })
})

describe('#max,判断成员最大值限制', function() {
  it('#值为字符串 aaaaa,判断是否满足未超过最大长度限制10，返回true', function() {
    expect(Validate.max('aaaaa', 10)).to.be.true
  })

  it('#值为字符串 aaaaa,判断是否满足未超过最大长度限制5，返回true', function() {
    expect(Validate.max('aaaaa', 5)).to.be.true
  })

  it('#值为字符串 aaaaa,判断是否满足未超过最大长度限制4，返回false', function() {
    expect(Validate.max('aaaaa', 4)).to.be.false
  })

  it('#值为数值 100,判断是否满足未超过最大数值限制200，返回true', function() {
    expect(Validate.max(100, 200)).to.be.true
  })

  it('#值为数值 100,判断是否满足未超过最大数值限制100，返回true', function() {
    expect(Validate.max(100, 100)).to.be.true
  })

  it('#值为数值 100,判断是否满足未超过最大数值限制99，返回false', function() {
    expect(Validate.max(100, 99)).to.be.false
  })

  it('#值为数组 [1,2,3,4,5],判断是否满足未超过最大成员限制5，返回true', function() {
    expect(Validate.max([1, 2, 3, 4, 5], 5)).to.be.true
  })

  it('#值为数组 [1,2,3,4,5],判断是否满足未超过最大成员限制2，返回false', function() {
    expect(Validate.max([1, 2, 3, 4, 5], 2)).to.be.false
  })

  it('#值为数组 {a:1,b:2,c:3},判断是否满足未超过最大成员限制2，返回false', function() {
    expect(Validate.max({ a : 1, b : 2, c : 3 }, 2)).to.be.false
  })
})

describe('#min,判断成员最小值限制', function() {
  it('#值为字符串 aaaaa,判断是否满足未超过最小长度限制10，返回false', function() {
    expect(Validate.min('aaaaa', 10)).to.be.false
  })

  it('#值为字符串 aaaaa,判断是否满足未超过最小长度限制5，返回true', function() {
    expect(Validate.min('aaaaa', 5)).to.be.true
  })

  it('#值为字符串 aaaaa,判断是否满足未超过最小长度限制4，返回true', function() {
    expect(Validate.min('aaaaa', 4)).to.be.true
  })

  it('#值为数值 100,判断是否满足未超过最小数值限制200，返回false', function() {
    expect(Validate.min(100, 200)).to.be.false
  })

  it('#值为数值 100,判断是否满足未超过最小数值限制100，返回true', function() {
    expect(Validate.min(100, 100)).to.be.true
  })

  it('#值为数值 100,判断是否满足未超过最小数值限制99，返回true', function() {
    expect(Validate.min(100, 99)).to.be.true
  })

  it('#值为数组 [1,2,3,4,5],判断是否满足未超过最小成员限制5，返回true', function() {
    expect(Validate.min([1, 2, 3, 4, 5], 5)).to.be.true
  })

  it('#值为数组 [1,2,3,4,5],判断是否满足未超过最小成员限制2，返回true', function() {
    expect(Validate.min([1, 2, 3, 4, 5], 2)).to.be.true
  })

  it('#值为数组 {a:1,b:2,c:3},判断是否满足未超过最大成员限制1，返回false', function() {
    expect(Validate.min({ a : 1, b : 2, c : 3 }, 1)).to.be.false
  })
})

describe('#require,判断值是否有填写', function() {
  it('#值为字符串 aaaaa,返回true', function() {
    expect(Validate.require('aaaaa')).to.be.true
  })

  it('#值为字符串 111222,返回true', function() {
    expect(Validate.require('111222')).to.be.true
  })

  it('#值为数值 123,返回true', function() {
    expect(Validate.require(123)).to.be.true
  })

  it('#值为空字符串,返回false', function() {
    expect(Validate.require('')).to.be.false
  })

  it('#值为null,返回false', function() {
    expect(Validate.require(null)).to.be.false
  })

  it('#值未定义,返回false', function() {
    expect(Validate.require(null)).to.be.false
  })
})

describe('#isMail,判断是否为邮箱', function() {
  it('#值为 453618847@qq.com,返回true', function() {
    expect(Validate.isMail('453618847@qq.com')).to.be.true
  })

  it('#值为 admin@topx5.com,返回true', function() {
    expect(Validate.isMail('admin@topx5.com')).to.be.true
  })

  it('#值为 @topx5.com,返回false', function() {
    expect(Validate.isMail('@topx5.com')).to.be.false
  })

  it('#值为 453618847@qq,返回false', function() {
    expect(Validate.isMail('453618847@qq')).to.be.false
  })

  it('#值为空字符串,返回false', function() {
    expect(Validate.isMail('')).to.be.false
  })
})

describe('#isString,判断是否为字符串', function() {
  it('#值为字符串 123456，返回true', function() {
    expect(Validate.isString('123456')).to.be.true
  })

  it('#值为数值 123456，返回false', function() {
    expect(Validate.isString(123456)).to.be.false
  })

  it('#值为空字符串 ""，返回true', function() {
    expect(Validate.isString('')).to.be.true
  })
})

describe('#isArray,判断是否为数组', function() {
  const data = [
    {
      value  : [1, 2, 3, 4],
      result : true,
      type   : '整数数组'
    },
    {
      value  : { a : 1, b : 2 },
      result : false,
      type   : '对象'
    },
    {
      value  : '',
      result : false,
      type   : '字符串'
    },
    {
      value  : 1,
      result : false,
      type   : '数值'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isArray(data[i].value)).to.equal(data[i].result)
    })
  }

  it('#当Array的IsArray方法不存在时，进行的判断 ', function() {
    Array.isArray = undefined
    expect(Validate.isArray([1, 2, 3])).to.equal(true)
  })
})

describe('#isUrl,判断是否为合法Url', function() {
  const data = [
    {
      value  : 'http://www.itwmw.com',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'https://www.itwmw.com',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'https://www.itwmw.com/s/s/s/s',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'https://www.itwmw.com/login?from=1',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'www.itwmw.com',
      result : false,
      type   : '字符串'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isUrl(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isMobile,判断是否为合法手机号', function() {
  const data = [
    {
      value  : '13788889999',
      result : true,
      type   : '字符串'
    },
    {
      value  : '+86 13788889999',
      result : false,
      type   : '字符串'
    },
    {
      value  : '1567486756',
      result : false,
      type   : '字符串'
    },
    {
      value  : 'asdas4sd65f7',
      result : false,
      type   : '字符串'
    },
    {
      value  : 13788889999,
      result : true,
      type   : '长整数'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isMobile(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isIdCard,判断是否为合法身份证号', function() {
  const data = [
    {
      value  : '110101199003078531',
      result : true,
      type   : '字符串'
    },
    {
      value  : '110101199003078532',
      result : false,
      type   : '字符串'
    },
    {
      value  : '110101204003089293',
      result : false,
      type   : '字符串'
    }, {
      value  : '33010220160505928X',
      result : true,
      type   : '字符串'
    }, {
      value  : '110101201606069319',
      result : true,
      type   : '字符串'
    }, {
      value  : '110101201606060998',
      result : true,
      type   : '字符串'
    }, {
      value  : '110101201606066775',
      result : true,
      type   : '字符串'
    }, {
      value  : '11010120160606223X',
      result : true,
      type   : '字符串'
    }, {
      value  : '110101201606066599',
      result : true,
      type   : '字符串'
    },
    {
      value  : '110101180003072690',
      result : true,
      type   : '字符串'
    }, {
      value  : '110101199013077815',
      result : false,
      type   : '字符串'
    },
    {
      value  : '46456456456',
      result : false,
      type   : '字符串'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isIdCard(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isLower,判断是否为小写字母', function() {
  const data = [
    {
      value  : 'sssssssssss',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'Sssssssssss',
      result : false,
      type   : '字符串'
    },
    {
      value  : 'SSSSSSS',
      result : false,
      type   : '字符串'
    },
    {
      value  : '1231232',
      result : false,
      type   : '字符串'
    },
    {
      value  : 123123123,
      result : false,
      type   : '数值'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isLower(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isUpper,判断是否为大写字母', function() {
  const data = [
    {
      value  : 'sssssssssss',
      result : false,
      type   : '字符串'
    },
    {
      value  : 'Sssssssssss',
      result : false,
      type   : '字符串'
    },
    {
      value  : 'SSSSSSS',
      result : true,
      type   : '字符串'
    },
    {
      value  : '1231232',
      result : false,
      type   : '字符串'
    },
    {
      value  : 123123123,
      result : false,
      type   : '数值'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isUpper(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isAlpha,判断是否为纯字母', function() {
  const data = [
    {
      value  : 'sssssssssss',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'Sssssssssss',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'SSSSSSS',
      result : true,
      type   : '字符串'
    },
    {
      value  : '1231232',
      result : false,
      type   : '字符串'
    },
    {
      value  : 123123123,
      result : false,
      type   : '字符串'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isAlpha(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isAmount,判断金额格式是否正确', function() {
  const data = [
    {
      value  : '1.22',
      result : true,
      type   : '字符串'
    },
    {
      value  : '1',
      result : true,
      type   : '字符串'
    },
    {
      value  : '100000000',
      result : true,
      type   : '字符串'
    },
    {
      value  : '1231232.56454545',
      result : false,
      type   : '字符串'
    },
    {
      value  : 'sads',
      result : false,
      type   : '字符串'
    },
    {
      value  : '1.2',
      result : true,
      type   : '字符串'
    },
    {
      value  : 0,
      result : true,
      type   : '数值'
    },
    {
      value  : 1.95,
      result : true,
      type   : '数值'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isAmount(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isDecimal,判断是否为小数', function() {
  const data = [
    {
      value  : '1.22',
      result : true,
      type   : '字符串'
    },
    {
      value  : '1',
      result : false,
      type   : '字符串'
    },
    {
      value  : '100000000',
      result : false,
      type   : '字符串'
    },
    {
      value  : '1231232.56454545',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'sads',
      result : false,
      type   : '字符串'
    },
    {
      value  : '0',
      result : false,
      type   : '字符串'
    },
    {
      value  : '0.00',
      result : true,
      type   : '字符串'
    },
    {
      value  : 123,
      result : false,
      type   : '数值'
    },
    {
      value  : 0.95,
      result : true,
      type   : '数值'
    },
    {
      value  : 0.00,
      result : false,
      type   : '数值'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isDecimal(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isInteger,判断是否为整数', function() {
  const data = [
    {
      value  : '1.22',
      result : false,
      type   : '字符串'
    },
    {
      value  : '1',
      result : true,
      type   : '字符串'
    },
    {
      value  : '100000000',
      result : true,
      type   : '字符串'
    },
    {
      value  : '1231232.56454545',
      result : false,
      type   : '字符串'
    },
    {
      value  : 'sads',
      result : false,
      type   : '字符串'
    },
    {
      value  : '0',
      result : true,
      type   : '字符串'
    },
    {
      value  : '0.00',
      result : false,
      type   : '字符串'
    },
    {
      value  : 123,
      result : true,
      type   : '数值'
    },
    {
      value  : 0.95,
      result : false,
      type   : '数值'
    },
    {
      value  : 0.00,
      result : true,
      type   : '数值'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isInteger(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isIpV4,判断是否为ipV4', function() {
  const data = [
    {
      value  : '1.95.25',
      result : false,
      type   : '字符串'
    },
    {
      value  : '1.1.1.1',
      result : true,
      type   : '字符串'
    },
    {
      value  : '92.125.201.1',
      result : true,
      type   : '字符串'
    },
    {
      value  : '255.265.252.1',
      result : false,
      type   : '字符串'
    },
    {
      value  : '1231232.56454545',
      result : false,
      type   : '字符串'
    },
    {
      value  : '192.168.0.1',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'localhost',
      result : false,
      type   : '字符串'
    },
    {
      value  : '127.0.0.1',
      result : true,
      type   : '字符串'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isIpV4(data[i].value)).to.equal(data[i].result)
    })
  }
})

describe('#isFile,判断是否为文件', function() {
  it('#值为对象{a:1},返回false', function() {
    expect(Validate.isFile({ a : 1 })).to.be.false
  })
})

describe('#isAlphaDash,值是否为字母和数字，下划线_及破折号-组成', function() {
  const data = [
    {
      value  : '1.95.25',
      result : false,
      type   : '字符串'
    },
    {
      value  : 'password123',
      result : true,
      type   : '字符串'
    },
    {
      value  : 'Nice_China',
      result : true,
      type   : '字符串'
    },
    {
      value  : '用户名',
      result : false,
      type   : '字符串'
    },
    {
      value  : '1991_1-1',
      result : true,
      type   : '字符串'
    }
  ]

  for (let i = 0; i < data.length; i++) {
    it(`#值为${data[i].type}${data[i].value}，返回${data[i].result}`, function() {
      expect(Validate.isAlphaDash(data[i].value)).to.equal(data[i].result)
    })
  }
})
