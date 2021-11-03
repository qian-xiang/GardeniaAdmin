interface keyValue{
    [key:string]:any
}
interface sometimesCallback{
    (value:object,v:Validate):boolean
}
interface ruleCallBack{
    (value:any,rule:string,data:object,param:any,key:string):boolean|string,
}
interface filterCallBack{
    (value:any):any
}

type extendCallback = ruleCallBack | filterCallBack


export class ValidateException{
    protected message: any;
    protected rule:string|Array<string>;
    protected key:string|Array<string>;
    protected value: any;

    getMessage(): any
    getRule(): string|Array<string>
    getKey(): string|Array<string>
    getValue() :any
    getError() :any
}

declare class BaseExtend {
    /**
     * 为对象进行扩展方法
     * @param source 如为方法，则直接绑定，如为字符串，则认定为方法名称
     * @param callback 方法体
     */
    extend(source: string|extendCallback,callback?:extendCallback)
}

export class Validate extends BaseExtend{
    /**
     * 验证错误消息
     * @protected
     */
    protected msg:object;

    /**
     * 验证规则
     * @protected
     */
    protected rule:object;

    /**
     * 验证场景
     * @protected
     */
    protected scene:object;

    /**
     * 字段名称
     * @protected
     */
    protected field:object

    /**
     * 构建函数
     * @param rule 验证规则
     * @param msg 错误提示消息
     */
    constructor(rule?: keyValue, msg?: keyValue);

    /**
     * 数据自动验证
     * @param data 要验证的验证数据
     * @param fail 是否以抛出异常的方式反馈
     */
    check(data: object, fail?: boolean): boolean

    /**
     * 初始化验证器，作用为清空获取器，过滤器，规则器，退出验证场景
     */
    init():Validate

    /**
     * 有时候验证，复杂验证条件
     * @param keys 用来验证的字段名称
     * @param rules 想使用的验证规则
     * @param callback 闭包 作为第三个参数传入，如果其返回 true ， 则额外的规则就会被加入
     */
    sometimes(keys: string, rules: string, callback: sometimesCallback): Validate

    /**
     * 设置字段名称
     * @param fieldName
     */
    setField(fieldName: object): Validate

    /**
     * 依赖注入
     * @param abstract
     */
    register(abstract:BaseFilter|BaseGetter|BaseValidator):Validate


    /**
     * 获取规则器
     * @returns {BaseValidator}
     */
    getValidator():BaseValidator

    /**
     * 获取过滤器
     * @returns {BaseGetter}
     */
    getGetter():BaseGetter

    /**
     * 获取过滤器
     * @returns {BaseFilter}
     */
    getFitter():BaseFilter

    /**
     * 获取处理后的数据，可用于表单验证等(过滤器和获取器处理后的数据均体现在这里)
     * @deprecated
     * @see getData
     */
    getProcessedData():object

    /**
     * 获取验证通过的数据(此数据经过了过滤器和获取器的处理)
     */
    getData():object

    /**
     * 获取当前正在验证的数据(原始数据)
     */
    getOriginalData():object

    /**
     * 设置验证场景数据
     * @param scene
     */
    setScene(scene:object):Validate

    /**
     * 设置是否抛出异常
     * @param fail
     */
    setFail(fail?:boolean):Validate

    /**
     * 进入验证场景
     * @param sceneName
     */
    setSceneName(sceneName?:string|null):Validate

    /**
     * 指定需要验证的字段列表(自定义验证场景内使用)
     * @param field 字段名
     */
    only(field:string|boolean|Array<string>):Validate

    /**
     * 移除某个字段的验证规则(自定义验证场景内使用)
     * @param field 字段名
     * @param value 验证规则 null 移除所有规则
     */
    remove(field:string,value:string|Array<string>|null):Validate

    /**
     * 追加某个字段的验证规则(自定义验证场景内使用)
     * @param field 字段名
     * @param value 验证规则
     */
    append(field:string,value:string|Array<string>):Validate

    /**
     * 设置别称字段
     * @param alias
     */
    setAlias(alias:object):Validate

    /**
     * 设置批量验证
     * @param batch
     */
    setBatch(batch?:boolean):Validate

    /**
     * 设置验证规则
     * @param rule
     */
    setRule(rule:keyValue):Validate

    /**
     * 设置验证消息
     * @param msg
     */
    setMsg(msg:keyValue):Validate

    /**
     * 设置过滤信息
     * @param filter 过滤器信息，如果为null则清空过滤器
     */
    setFilter(filter:keyValue):Validate

    /**
     * 获取错误消息
     */
    getError():string|Array<object>

    /**
     * 静态创建验证类
     * @param rule 验证规则
     * @param msg 验证消息
     */
    static make(rule?: keyValue, msg?: keyValue): Validate
}

export class ValidateRule extends BaseExtend{
    /**
     * 验证某个字段是否为数字文本
     * @param value
     * @returns {boolean}
     */
    static isNumber(value:any):boolean

    /**
     * 验证某个字段的值只能是汉字、字母、数字和下划线_及破折号-
     * @param value
     * @returns {boolean}
     */
    static isChsDash(value:any):boolean

    /**
     * 验证某个字段的值是否为字母和数字，下划线_及破折号-
     * @param value
     * @returns {boolean}
     */
    static isAlphaDash(value:any):boolean

    /**
     * 验证某个字段的值只能是汉字、字母和数字
     * @param value
     * @returns {boolean}
     */
    static isAlphaDash(value:any):boolean

    /**
     * 验证某个字段的值只能是汉字、字母和数字
     * @param value
     * @returns {boolean}
     */
    static isChsAlphaNum(value:any):boolean

    /**
     * 验证某个字段的值只能是汉字、字母
     * @param value
     * @returns {boolean}
     */
    static isChsAlpha(value:any):boolean

    /**
     * 验证某个字段的值只能是汉字
     * @param value
     * @returns {boolean}
     */
    static isChs(value:any):boolean

    /**
     * 验证某个字段的值是否为统一社会信用代码
     * @param value
     * @returns {boolean}
     */
    static isCreaditCode(value:any):boolean

    /**
     * 判断是否有值
     * @param value
     * @returns {boolean}
     */
    static require(value:any):boolean

    /**
     * 是否为邮箱
     * @param value
     * @returns {boolean}
     */
    static isMail(value:any):boolean

    /**
     * 是否为字符串类型
     * @param value
     * @returns {boolean}
     */
    static isString(value:any):boolean

    /**
     * 验证某个字段的值的长度是否在某个范围或指定长度
     * @param value
     * @param rule
     * @returns {boolean}
     */
    static isLength(value:number|string|Array<any>,rule:string):boolean

    /**
     * 验证某个字段的值是否在某个区间
     * @param value
     * @param rule
     * @returns {boolean}
     */
    static between(value:number,rule:string):boolean

    /**
     * 验证某个字段的值不在某个范围
     * @param value
     * @param rule
     * @returns {boolean}
     */
    static notBetween(value:number,rule:string):boolean

    /**
     * 验证某个字段的值是否在某个范围
     * @param value
     * @param rule
     * @returns {boolean}
     */
    static in(value:number|string,rule:string|Array<number>):boolean

    /**
     * 验证某个字段的值不在某个范围
     * @param value
     * @param rule
     * @returns {boolean}
     */
    static notIn(value:number|string,rule:string|Array<number>):boolean

    /**
     * 最大值限制
     * 当类型为字符串时，判断文本长度
     * 当类型为数值时，判断数值大小
     * 当类型为数组时，判断成员数
     *
     * @param value
     * @param rule
     * @returns {boolean}
     */
    static max(value:number|string|object,rule:string|number|Array<number>):boolean

    /**
     * 最小值限制
     * 当类型为字符串时，判断文本长度
     * 当类型为数值时，判断数值大小
     * 当类型为数组时，判断成员数
     *
     * @param value
     * @param rule
     * @returns {boolean}
     */
    static min(value:number|string|object,rule:string|number|Array<number>):boolean

    /**
     * 是否为数组类型
     * @param value
     * @returns boolean
     */
    static isArray(value:any):boolean

    /**
     * 是否为合法URL
     * @param value
     * @returns {boolean}
     */
    static isUrl(value:any):boolean

    /**
     * 是否为合法手机号
     * @param value
     * @returns {boolean}
     */
    static isMobile(value:any):boolean

    /**
     * 是否为合法身份证号码(非正则，会对校验码进行检查)
     * @param value
     * @returns {boolean}
     */
    static isIdCard(value:any):boolean

    /**
     * 是否小写字母
     * @param value
     * @returns {boolean}
     */
    static isLower(value:any):boolean

    /**
     * 是否大写字母
     * @param value
     * @returns {boolean}
     */
    static isUpper(value:any):boolean

    /**
     * 是否为纯字母
     * @param value
     * @returns {boolean}
     */
    static isAlpha(value:any):boolean

    /**
     * 金额是否格式正确  最多保留两位小数
     * @param value
     * @returns {boolean}
     */
    static isAmount(value:any):boolean

    /**
     * 判断是否为小数文本
     * @param value
     * @returns {boolean}
     */
    static isDecimal(value:any):boolean

    /**
     * 判断是否为整数文本
     * @param value
     * @returns {boolean}
     */
    static isInteger(value:any):boolean

    /**
     * 判断是否为IPV4
     * @param value
     * @returns {boolean}
     */
    static isIpV4(value:any):boolean

    /**
     * 判断是否为文件
     * @param value
     * @returns {boolean}
     */
    static isFile(value:any):boolean

    /**
     * 判断是否为对象
     * @param value
     * @returns {boolean}
     */
    static isObject(value:any):boolean
}

export class BaseFilter extends BaseExtend{
    /**
     * 删除左右空格
     * @param value
     */
    trim(value :any):string

    /**
     * 删除全部空格
     * @param value
     */
    removeSpace(value:any):string

    /**
     * 转换为字符串
     * @param value
     */
    toString(value):string

    /**
     * 转换为整数
     * @param value
     */
    toInt(value):number

    /**
     * 转换为浮点数
     * @param value
     */
    toFloat(value):number
}

export class BaseValidator extends ValidateRule{
    /**
     * 设置自定义正则表达式规则
     * @param regex
     */
    setRegex(regex:object):BaseValidator

    isNumber(value:any):boolean
    isChsDash(value:any):boolean
    isAlphaDash(value:any):boolean
    isAlphaDash(value:any):boolean
    isChsAlphaNum(value:any):boolean
    isChsAlpha(value:any):boolean
    isChs(value:any):boolean
    isCreaditCode(value:any):boolean
    require(value:any):boolean
    isMail(value:any):boolean
    isString(value:any):boolean
    isLength(value:number|string|Array<any>,rule:string):boolean
    between(value:number,rule:string):boolean
    notBetween(value:number,rule:string):boolean
    in(value:number|string,rule:string|Array<number>):boolean
    notIn(value:number|string,rule:string|Array<number>):boolean
    max(value:number|string|object,rule:string|number|Array<number>):boolean
    min(value:number|string|object,rule:string|number|Array<number>):boolean
    isArray(value:any):boolean
    isUrl(value:any):boolean
    isMobile(value:any):boolean
    isIdCard(value:any):boolean
    isLower(value:any):boolean
    isUpper(value:any):boolean
    isAlpha(value:any):boolean
    isAmount(value:any):boolean
    isDecimal(value:any):boolean
    isInteger(value:any):boolean
    isIpV4(value:any):boolean
    isFile(value:any):boolean
    isObject(value:any):boolean
}

export class BaseGetter extends BaseExtend{

}
