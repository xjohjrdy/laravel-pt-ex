<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{!!$data->title!!}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <script type="application/javascript"
            src="http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/ycm.min.js"></script>
    <link rel="stylesheet" type="text/css"
          href="http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/common.css">
    <link rel="stylesheet" type="text/css"
          href="http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/ycm.min.css">
    <script type="text/javascript">!function (N, M) {
            function L() {
                var a = I.getBoundingClientRect().width;
                a / F > 540 && (a = 540 * F);
                var d = a / 10;
                I.style.fontSize = d + "px", D.rem = N.rem = d
            }

            var K, J = N.document, I = J.documentElement, H = J.querySelector('meta[name="viewport"]'),
                G = J.querySelector('meta[name="flexible"]'), F = 0, E = 0, D = M.flexible || (M.flexible = {});
            if (H) {
                console.warn("将根据已有的meta标签来设置缩放比例");
                var C = H.getAttribute("content").match(/initial\-scale=([\d\.]+)/);
                C && (E = parseFloat(C[1]), F = parseInt(1 / E))
            } else {
                if (G) {
                    var B = G.getAttribute("content");
                    if (B) {
                        var A = B.match(/initial\-dpr=([\d\.]+)/), z = B.match(/maximum\-dpr=([\d\.]+)/);
                        A && (F = parseFloat(A[1]), E = parseFloat((1 / F).toFixed(2))), z && (F = parseFloat(z[1]), E = parseFloat((1 / F).toFixed(2)))
                    }
                }
            }
            if (!F && !E) {
                var y = N.navigator.userAgent, x = (!!y.match(/android/gi), !!y.match(/iphone/gi)),
                    w = x && !!y.match(/OS 9_3/), v = N.devicePixelRatio;
                F = x && !w ? v >= 3 && (!F || F >= 3) ? 3 : v >= 2 && (!F || F >= 2) ? 2 : 1 : 1, E = 1 / F
            }
            if (I.setAttribute("data-dpr", F), !H) {
                if (H = J.createElement("meta"), H.setAttribute("name", "viewport"), H.setAttribute("content", "initial-scale=" + E + ", maximum-scale=" + E + ", minimum-scale=" + E + ", user-scalable=no"), I.firstElementChild) {
                    I.firstElementChild.appendChild(H)
                } else {
                    var u = J.createElement("div");
                    u.appendChild(H), J.write(u.innerHTML)
                }
            }
            N.addEventListener("resize", function () {
                clearTimeout(K), K = setTimeout(L, 300)
            }, !1), N.addEventListener("pageshow", function (b) {
                b.persisted && (clearTimeout(K), K = setTimeout(L, 300))
            }, !1), "complete" === J.readyState ? J.body.style.fontSize = 12 * F + "px" : J.addEventListener("DOMContentLoaded", function () {
                J.body.style.fontSize = 12 * F + "px"
            }, !1), L(), D.dpr = N.dpr = F, D.refreshRem = L, D.rem2px = function (d) {
                var c = parseFloat(d) * this.rem;
                return "string" == typeof d && d.match(/rem$/) && (c += "px"), c
            }, D.px2rem = function (d) {
                var c = parseFloat(d) / this.rem;
                return "string" == typeof d && d.match(/px$/) && (c += "rem"), c
            }
        }(window, window.lib || (window.lib = {}));</script>
</head>
<body style="font-size: 12px;background-color: #16171c;">
{{--<div class="wrap">--}}
{{--<div class="xq_content">--}}
{{--<div class="d1">--}}
{{--<div class="d1_left">--}}
{{--<img src="http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/logo/logo.png">--}}
{{--</div>--}}
{{--<div class="d1_right">--}}
{{--<p class="p1 font28">我的浏览器</p>--}}
{{--</div>--}}
{{--</div>--}}
<a href="http://a119112.oss-cn-beijing.aliyuncs.com/%E4%BA%A4%E6%98%93%E5%B8%82%E5%9C%BA/show/%E8%91%A1%E8%90%84%E6%B5%8F%E8%A7%88%E5%99%A8%E5%AE%A3%E4%BC%A0%E5%8A%A8%E7%94%BB%E5%BE%AE%E4%BF%A1%E7%89%882(3).mp4">
    {{--<div class="d2">--}}
    {!!$data->content!!}
    {{--</div>--}}
</a>
{{--</div>--}}
{{--</div>--}}
</body>
</html>