<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>活动倒计时</title>
    <meta name="viewport"
          content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="format-detection" content="telephone=yes"/>
    <meta name="msapplication-tap-highlight" content="no"/>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.min.js"></script>
</head>
<body>
<div style='width: 100%;height: 100%;' >
    <img class="bg" src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/yiyuan.jpg" alt="">
    <div class="js" id="jishi">
        <span id="_d">00</span>
        <span id="_h">00</span>
        <span id="_m">00</span>
        <span id="_s">00</span>
        <!--<span id="_ms">00</span>-->
    </div>
</div>
<script type="text/javascript">
    function countTime() {
        var date = new Date();
        var now = date.getTime();
        var endDate = new Date("2019/5/24 00:00:00");//设置截止时间
        var end = endDate.getTime();
        var leftTime = end - now; //时间差
        var d, h, m, s, ms;
        if(leftTime >= 0) {
            d = Math.floor(leftTime / 1000 / 60 / 60 / 24);
            h = Math.floor(leftTime / 1000 / 60 / 60 % 24);
            m = Math.floor(leftTime / 1000 / 60 % 60);
            s = Math.floor(leftTime / 1000 % 60);
            //ms = Math.floor(leftTime % 1000);
            //if(ms < 100) {
            //    ms = "0" + ms;
            //}
            if(s < 10) {
                s = "0" + s;
            }
            if(d < 10) {
                d = "0" + d;
            }
            if(m < 10) {
                m = "0" + m;
            }
            if(h < 10) {
                h = "0" + h;
            }
        } else {
            d = "00";
            h = "00";
            m = "00";
            s = "00";
            // console.log('已截止')
        }
        //将倒计时赋值到div中
        document.getElementById("_d").innerHTML = d + "天";
        document.getElementById("_h").innerHTML = h + "时";
        document.getElementById("_m").innerHTML = m + "分";
        document.getElementById("_s").innerHTML = s + "秒";
        //document.getElementById("_ms").innerHTML = ms + "毫秒";
        //递归每秒调用countTime方法，显示动态时间效果
        setTimeout(countTime, 500);
    }
    countTime();
    document.getElementById("jishi").style.top = parseInt((screen.width/1080)*777)+'px';
</script>
</body>


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
        /*z-index: -999;*/
    }

    .js {
        position: absolute  ;
        /*top: 280px;*/
        right: 29%;
        color: #0aa6ff;
        z-index: 999;
    }

    /*.dw {*/
        /*position: absolute;*/
        /*top: 1%;*/
        /*right: 1%;*/
    /*}*/

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
</html>