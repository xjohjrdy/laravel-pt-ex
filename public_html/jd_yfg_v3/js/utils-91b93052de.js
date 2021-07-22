/**
 * Created by zz on 2018/5/7.
 */
(function (win) {
    win.utils = {
        getDate: function (times) {
            /*
            * str string @时间戳
            * */
            var day = 0,
                hour = 0,
                minute = 0,
                second = 0;
            if(times > 0){
                day = Math.floor(times / (60 * 60 * 24));
                hour = Math.floor(times / (60 * 60)) - (day * 24);
                minute = Math.floor(times / 60) - (day * 24 * 60) - (hour * 60);
                second = Math.floor(times) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
            }
            var date = [day,hour,minute,second];
            var dateMap = date.map(function(val){
                return val <= 9 ? '0'+val : val.toString();
            });
            return{
                day:dateMap[0],
                hour:dateMap[1],
                minute:dateMap[2],
                second:dateMap[3]
            }
        },
        clipboard:function(Clipboard,el,text,success,error){
            var clipboard = new Clipboard(el, {
                text: function() {
                    return text;
                },
            });
            clipboard.on('success', function(e) {
                success && success();
                e.clearSelection();
                clipboard.destroy();
            });
            clipboard.on('error', function(e) {
                error && error();
            });
        },
        clipboard2:function (Clipboard,el,text,success,error) {
            var childNode = document.createElement('div');
            childNode.innerHTML = text;
            childNode.setAttribute('id','copy');
            document.body.appendChild(childNode);
            var clipboard2 = new Clipboard(el, {
                target: function() {
                    return document.getElementById('copy');
                },
            });
            clipboard2.on('success', function(e) {
                success && success();
                e.clearSelection();
                clipboard2.destroy();
                document.body.removeChild(childNode);
            });
            clipboard2.on('error', function(e) {
                error && error();
            });
        },
        urlEncode:function(str){
            str = (str + '').toString();
            return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
        },
        urlDecode: function (str) {
            return decodeURIComponent((str + '')
                .replace(/%(?![\da-f]{2})/gi, function () {
                    return '%25';
                })
                .replace(/\+/g, '%20'));
        },
        getUrlParams:function(url) {
            var params = {};
            url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
                params[key] = value;
            });
            return params;
        },
        setQueryConfig:function(queryConfig) {
            var _str = "";
            for(var o in queryConfig){
                if(queryConfig[o] != -1){
                    _str += o + "=" + queryConfig[o] + "&";
                }
            }
            _str = _str.substring(0, _str.length-1);
            return _str;
        },
        throttle:function (fn, threshold) {
            var timer;
            var last;
            return function () {
                var now = +new Date();
                var context = this;
                var args = arguments;
                if (last && now < last + threshold) {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        last = now;
                        fn.apply(context, args);
                    }, threshold);
                } else {
                    last = now;
                    fn.apply(context, args);
                }
            }
        },
        debounce:function (fn, delay) {
            var timer;

            return function () {
                var context = this;
                var args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    fn.apply(context, args);
                },delay);
            }
        },
        toast: function (msg, type) {
          var parentNode = document.createElement('div'),
              maskNode = document.createElement('div'),
              msgNode = document.createElement('div');

          msgNode.appendChild(document.createTextNode(msg));
          parentNode.appendChild(maskNode);
          parentNode.appendChild(msgNode);

          var typeStyle = type == 'light' ? 'background: rgba(255, 255, 255, .7);color: black;' : 'background: rgba(0, 0, 0, .7);color: white;';

          parentNode.style.cssText = 'position: fixed;left: 0;right: 0;top: 0;bottom: 0;z-index: 10;';
          maskNode.style.cssText = 'position: absolute;top: 0;left: 0;width: 100%;height: 100%;';
          msgNode.style.cssText = 'position: absolute;top: 65%;min-width: 1.6rem;left: 50%;transform: translateX(-50%);line-height: 0.2rem;padding: 0.1rem 0.2rem;border-radius: 0.08rem;'+typeStyle+'font-size: 0.14rem;text-align: center;';

          document.body.appendChild(parentNode);

          setTimeout(function () {
            document.body.removeChild(parentNode);
          }, 2000);
        },
    }
})(window);