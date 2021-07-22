<!DOCTYPE html>
<html>
<head>
    <title>错误提示</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="telephone=no" name="format-detection">
    <meta name="viewport"
          content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <!--库-->
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/amazeui.css">
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.min.js"></script>
    <script type="text/javascript"
            src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.Spinner.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/amazeui.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/honeySwitch.js"></script>
    <!--核心样式-->
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/style.css">

</head>
<body style="background: #fff;">
<!-----------公共版头----------->
<div id="header">
    <a class="return" href="javascript:history.go(-1)"><i class="am-icon-angle-left"></i></a>
    <span>错误页面</span>
</div>
<!-----------常用联系人----------->
<div>
    {{--<img src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/img_succeed.png" width="40%" alt="" style="margin: 0 auto; display: block; margin-top: 90px;">--}}
    <img src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/img_failure.png" width="40%" alt=""
         style="margin: 0 auto; display: block; margin-top: 90px;">
    <p style="text-align: center; font-size: 24px; margin-top: 10px; line-height: 32px;"> 出错了！<br><span
                style="font-size: 20px;">{{$data}}</span></p>
</div>
</body>
</html>