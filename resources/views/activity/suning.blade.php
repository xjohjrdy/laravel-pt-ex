<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="width=device-width,initial-scale=1.0, user-scalable=no" name="viewport"/>
    <meta content="yes" name=" apple-mobile-web-app-capable"/>
    <meta content="no" name="apple-touch-fullscreen"/>
    <meta content="black" name=" apple-mobile-web-app-status-bar-style"/>
    <meta content="telephone=no" name="format-detection"/>
    <title>苏宁易购</title>
    <!--rem单位换算JS（切图专用）-->
    <script>
        /*rem单位的专用js,所有的psd图请缩放到414大小来切,1rem=100px;因此1px=0.01rem,例如需要设置宽度为15px，则 15*0.01=0.15rem*/
        (function (doc, win) {
            var docEl = doc.documentElement;
            var resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize';
            var recalc = function () {
                var clientWidth = docEl.clientWidth;
                if (!clientWidth) return;
                if (clientWidth >= 640) {
                    clientWidth = 640;
                }
                docEl.style.fontSize = (clientWidth / 414 * 100).toFixed(1) + 'px';
            };
            recalc();
            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
        })(document, window);
    </script>
    <script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="js/activity.js"></script><!--苏宁活动对应的效果表-->
    <link rel="stylesheet" type="text/css" href="css/hm_base.css">
    <link rel="stylesheet" type="text/css" href="css/activity.css"><!--苏宁活动对应的样式表-->

</head>
<body class="ac_bg_line">
<!--banner 开始-->
<div class="banner_box">
    <img src="images/ac_bg.png" class="hm_w_100">
</div>
<!--banner 结束-->

<!--领取优惠券口令按钮 开始-->
<div class="kl_btn_box">
    <section class="hm_copy_txt">{!! $copy !!}</section>
    <a href="javascript:void(0);" class="kl_btn run_kl_btn" data-clipboard-action="copy"
       data-clipboard-target="section">
        <img src="images/kl_btn.png" class="hm_w_100" alt="领取优惠券口令按钮">
    </a>
</div>
<!--领取优惠券口令按钮 结束-->

<!--其他按钮 开始-->
<div class="other_btn_box">
    <a href="{!! $jump !!}" class="down_btn">
        <img src="images/down_app_btn.png" class="hm_w_100" alt="下载苏宁易购APP">
    </a>
    <a href="{!! $this_url !!}" class="tx_btn">
    {{--<a href="#" class="tx_btn">--}}
        <img src="images/tx_btn.png" class="hm_w_100" alt="去提现">
    </a>
</div>
<!--其他按钮 结束-->

<!--活动规则 开始-->
<div class="ac_rule_box">
    <img src="images/rule_bg.png" class="hm_w_100">
</div>
<!--活动规则 结束-->

<!--提示 开始-->
<div class="tip_box">优惠券口令复制成功！请点击下方的“下载苏宁易购”，新人享收单一分购福利！</div>
<!--提示 结束-->


<script type="text/javascript" src="js/clipboard.js"></script><!--复制到剪切板对应的效果表-->
<script>
    $(function () {
        var clipboard = new Clipboard('.kl_btn');

        clipboard.on('success', function (e) {
            console.log(e);
        });

        clipboard.on('error', function (e) {
            console.log(e);
        });
    });
</script>
</body>
</html>
