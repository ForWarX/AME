(function($) {
    $.extend({
        myTime: {
            /**
             * 当前时间戳
             * @return {int}        unix时间戳(秒)
             */
            CurTime: function(){
                return Date.parse(new Date())/1000;
            },
            /**
             * 日期 转换为 Unix时间戳
             * @param string 2014-01-01 20:20:20  日期格式
             * @return         unix时间戳(秒)
             */
            DateToUnix: function(string) {
                var f = string.split(' ', 2);
                var d = (f[0] ? f[0] : '').split('-', 3);
                var t = (f[1] ? f[1] : '').split(':', 3);
                return (new Date(
                        parseInt(d[0], 10) || null,
                        (parseInt(d[1], 10) || 1) - 1,
                        parseInt(d[2], 10) || null,
                        parseInt(t[0], 10) || null,
                        parseInt(t[1], 10) || null,
                        parseInt(t[2], 10) || null
                    )).getTime() / 1000;
            },
            /**
             * 时间戳转换日期
             * @param unixTime    待时间戳(秒)
             * @param type        "date"或"time"或"all"
             * @param timeZone    时区
             * @return {Array}
             */
            UnixToDate: function(unixTime, type, timeZone) {
                if (typeof (timeZone) == 'number')
                {
                    unixTime = parseInt(unixTime) + parseInt(timeZone) * 60 * 60;
                }
                var time = new Date(unixTime * 1000);
                var ymdhis = [];
                if (type != "time") {
                    ymdhis['year'] = time.getUTCFullYear();
                    ymdhis['month'] = (time.getUTCMonth() + 1);
                    ymdhis['day'] = time.getUTCDate();
                }
                if (type != "date")
                {
                    ymdhis['hour'] = time.getUTCHours();
                    ymdhis['minute'] = time.getUTCMinutes();
                    ymdhis['second'] = time.getUTCSeconds();
                }
                return ymdhis;
            },

            /**
             * 根据时间戳计算剩余时间，返回剩余时间数组，最大值小时
             * @param start
             * @param end
             * @constructor
             * @return {Array}
             */
            TimeRemain: function(start, end) {
                var result = [];
                start = Number(start);
                end = Number(end);
                if (end > start) {
                    var delta = end - start;
                    var hour = parseInt(delta / 3600);
                    delta -= 3600 * hour;
                    var minute = parseInt(delta / 60);
                    var second = delta - minute * 60;
                    result['hour'] = hour;
                    result['minute'] = minute;
                    result['second'] = second;
                } else {
                    result['hour'] = 0;
                    result['minute'] = 0;
                    result['second'] = 0;
                }
                return result;
            }
        }
    });
})(jQuery);