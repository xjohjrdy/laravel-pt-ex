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
         src="http://a119112.oss-cn-beijing.aliyuncs.com/shop/%E8%83%8C%E6%99%AF.jpg"
         width="100%" height="100%" alt="">

    <img class="buy"
         src="http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/test/img/go.gif" alt="">

    <img class="tell re"
         src="http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/test/img/idxTime.gif"
         alt="">

    <img class="happy re"
         src="http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/test/img/bag.gif" alt="">
    <div class="but">
        <button class="re">生成专属海报</button>
        <button class="re">我的推广订单</button>
        <button class="rule">活动规则</button>
    </div>
</div>
</body>

<script>
    $(document).ready(function () {
        $(".re").click(function () {
            console.log('点击');
            window.location.href = 'http://xin_new.36qq.com/mobile/auth/register?id={{$parent_id}}'
        });

        $(".buy").click(function () {
            console.log('点击立即下单');
            var url = '/api/jd_test_wu_order';
            var data = {
                app_id:{{$parent_id}},
                type: 1
            };
            $.post(url, data, function (res) {
                if (res.code === 200) {
                    window.location.href = res.data;
                } else {
                    alert('请求失败！');
                }
            });
        });

        $(".rule").click(function () {
            console.log('点击活动规则');
            var url = '/api/jd_test_wu_regular';
            var data = {
                app_id:{{$parent_id}},
                type: 1
            };
            $.post(url, data, function (res) {
                if (res.code === 200) {
                    window.location.href = res.data;
                } else {
                    alert('请求失败！');
                }
            });
        });
    });
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

    .buy {
        position: fixed;
        top: 40%;
        left: 30%;
        width: 40%;
    }

    .tell {
        position: fixed;
        top: 50%;
        left: 30%;
        width: 40%;
    }

    .happy {
        position: fixed;
        top: 70%;
        left: 30%;
        width: 40%;
    }

    .but {
        position: fixed;
        bottom: 0px;
        height: 26px;
        overflow: hidden;
        box-sizing: border-box;
        /*margin-bottom: 10px;*/
        width: 100%;
        text-align: center;
    }

    .but button {
        margin: 0 1%;
        border: none;
        /*color="#bb0119"ef4e22*/
        background-color: #ef4e22;
        color: #ffffff;
        font-size: 10px;
        width: 30%;
        height: 26px;
        line-height: 26px;
    }

</style>
</html>