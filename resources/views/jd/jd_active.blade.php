<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>注册京东白条奖励10元！我的浏览器联合京东活动</title>
    <meta name="viewport"
          content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="format-detection" content="telephone=yes"/>
    <meta name="msapplication-tap-highlight" content="no"/>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.min.js"></script>
</head>
<body>
<div style='width: 100%;height: 100%;'>
    <img class="bg"
         src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/jieshu.jpg"
         width="100%" height="100%" alt="">
    {{--<div class="but">--}}
        {{--<a href="/jd_activity"><button class="guize"> </button></a>--}}
        {{--<a href="/jd_invite"><button class="yaoqing"> </button></a>--}}
        {{--<a href="https://jrunion.jd.com/s/zE36ny?uid=103365"><button class="jihuo"> </button></a>--}}
    {{--</div>--}}
    @if (empty($arr_res['data']))
        <div class="d1">
            <div class="left_img" style="position: absolute;bottom: 39%;left: 21%;">
                <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/shouji.png"
                     alt="" width="22" height="39">
            </div>
            <div class="left_img" style="width: 37%;height:34px;position: absolute;bottom: 40%;left: 30%;">
                <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/shurukuang.png"
                     alt="" width="100%" height="100%">
            </div>
            <input style="width: 34%;position: absolute;bottom: 40%;left: 32%;height: 4%;outline:none;border-width: 0"  id="mobile" type="text" name="phone" placeholder="输入激活成功的手机号" class="phone">
            <div class="left_img" style="position: absolute;bottom: 39%;left: 69%;">
                <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/tijiao.png"
                     alt="" width="50" height="32" onclick="submitForm({!! $app_id !!})">
            </div>
            {{--<a style="position: absolute;bottom: 36%;left: 70%;" id="form-submit" class="complete font30" onclick="submitForm()">提交</a>--}}
        </div>
    @else
        <p style="position: absolute;bottom: 40%;left: 22%;color: #ffffff;font-size: 5vw;">手机号：{!!$arr_res['data']['phone']!!}</p>
    @endif
    <p style="position: absolute;bottom: 35%;left: 32%;color: #ffffff;font-size: 5vw;">我的奖励：{!!$good['money']!!}元</p>
    <p style="position: absolute;bottom: 30%;left: 35%;color: #ffffff;font-size: 4vw;">激活人数：{!!$good['number']!!}人</p>
    {{--<p style="position: fixed;bottom: 25%;left: 32%;color: #ffffff;font-size: 13px;">预估累计收益：0000元</p>--}}

</div>
</body>

<script>

</script>
<style>
    * {
        margin: 0;
        padding: 0;
    }

    html {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    body {
        margin: 0 auto;
        width: 100%;
        height: 100%;
    }

    .bg {
        margin: 0 auto;
        width: 100%;
        height: 100%;
        z-index: -999;
        position: relative;
    }

    .guize {
        position: fixed;
        top: 5.1%;
        right: -6%;
        color: #ffffff;
    }

    .yaoqing {
        position: fixed;
        top: 52%;
        left: 10%;
        color: red;
        letter-spacing: 2px;
    }

    .jihuo {
        position: fixed;
        top: 52%;
        right: 10%;
        color: red;
        letter-spacing: 2px;
    }

    .rule {
        position: fixed;
        top: 20%;
        right: 2%;
        width: 40%;
    }

    .but {
        position: fixed;
        bottom: 0px;
        height: 26px;
        overflow: hidden;
        box-sizing: border-box;
        width: 100%;
        text-align: center;
    }

    .but button {
        margin: 0 1%;
        border: none;
        background: transparent;
        width: 30%;
        height: 26px;
        line-height: 26px;
    }

</style>
<script src="http://jiaoyi.grachain.net/CDN/common/js/jquery.min.js"></script>
<script src="http://jiaoyi.grachain.net/CDN/common/layer/layer.js"></script>
<script src="http://jiaoyi.grachain.net/CDN/js/check-browser.js"></script>
<script>
    //提交手机号
    function submitForm(id) {
        //手机号验证
        var mobile = $("#mobile").val();
        if (mobile == '') {
            layer.msg("手机号码不能为空！");
            $("#mobile").focus();
            return false;
        }
        if (!/^1\d{10}$/.test(mobile)) {
            layer.msg("手机号格式错误！");
            $("#mobile").focus();
            return false;
        }
        //提交手机号
        //表单提交
        var param = {
            'phone': mobile,
        };
        $.ajax({
            url: "xin_jd_submit_phone?id="+id,
            type: 'post',
            data: param,
            async: true,
            dataType: 'json',
            success: function (data) {
                layer.msg(data.msg);
                location.reload();

            }
        })
    }
</script>
</html>