<!doctype html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>{{$title}}</title>

    <!-- Set render engine for 360 browser -->
    <meta name="renderer" content="webkit">

    <!-- No Baidu Siteapp-->
    <meta http-equiv="Cache-Control" content="no-siteapp"/>

    <link rel="icon" type="image/png" href="https://a119112.oss-cn-beijing.aliyuncs.com/app_question/3.png">

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" sizes="192x192" href="https://a119112.oss-cn-beijing.aliyuncs.com/app_question/3.png">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Amaze UI"/>
    <link rel="apple-touch-icon-precomposed" href="https://a119112.oss-cn-beijing.aliyuncs.com/app_question/3.png">

    <!-- Tile icon for Win8 (144x144 + tile color) -->
    <meta name="msapplication-TileImage" content="https://a119112.oss-cn-beijing.aliyuncs.com/app_question/3.png">
    <meta name="msapplication-TileColor" content="#0e90d2">

    <!--核心样式-->
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/amazeui.min.css" />

    <!--库-->
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.min.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/amazeui.js"></script>

    <script>
        $(function() {
            setTimeout(function(){//三秒后跳转
                var uri = $('<div/>').html('{{$article_link}}').text();
                console.log(uri);
                window.location.replace(uri);
            },3000);
        });
    </script>
<body>

    <div class="am-cf" style="padding:0px 20px 20px;background-color: #939393">
        <a style="position: relative;left: 95%;top: 13px;" href="{{$article_link}}" class="am-close am-close-alt am-close-spin am-icon-times"></a>
        <a href="JavaScript:;" onclick="click_ad()"><img src="{{$ad_img}}" class="am-img-thumbnail"/></a>
    </div>

{{--广告图片{{$ad_img}}--}}
{{--广告链接{{$ad_link}}--}}
{{--文章链接{{$article_link}}--}}

</body>
    <script>
        function click_ad() {
            //
            var url_code = encodeURIComponent('{{$article_link}}');
            window.location.href = "{{$ad_link}}?article_id={{$article_id}}&url=" + url_code;
        }
    </script>
</html>