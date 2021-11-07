define(function () {
    return {
        /**
         * 判断UA是否是移动端
         * @returns {boolean}
         */
        isMobile: function () {
            var uaList = ['micromessenger','Android','iPhone','iPad','iPod','Mobile'];
            var ua = navigator.userAgent;
            for (let i = 0; i < uaList.length; i++) {
                if (ua.indexOf(uaList[i]) !== -1){
                    return true
                }
            }
            return false;
        },
        /**
         * 将小于0的数字加前导零
         * @param value
         * @returns {*}
         */
        numberToAddZero: function (value) {
            return value < 10 ? '0' + value : value
        }
    }
})