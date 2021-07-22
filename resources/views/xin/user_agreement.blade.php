<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{!! $data->title !!}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <script type="application/javascript" src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/ycm.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/ycm.min.css">
    <link rel="stylesheet" type="text/css" href="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/common.css">
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
<body>
<div class="wrap">
    <div class="xq_content">
        <div class="d1">
            <div class="d1_left">
                <img src="http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/logo/logo.png">
            </div>
            <div class="d1_right">
                <p class="p1 font28">葡萄浏览器</p>
            </div>
        </div>
        <div class="d2">
            {!! $data->content !!}
        </div>
    </div>
</div>
</body>
</html>