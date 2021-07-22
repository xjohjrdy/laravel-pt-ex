<!DOCTYPE html>
<html>
<head>
    <title>个人中心</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="telephone=no" name="format-detection">
    <meta name="viewport"
          content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <!--库-->
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/amazeui.css">
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.min.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/amazeui.js"></script>
    <!--核心样式-->
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/style.css">
    <style>
        .msgs {
            display: inline-block;
            width: 100px;
            color: #333;
            font-size: 12px;
            border: 1px solid #cdcdcd;
            text-align: center;
            height: 35px;
            line-height: 35px;
            cursor: pointer;
            border-radius: 8px;
        }

        form .msgs1 {
            background: #E6E6E6;
            color: #818080;
            border: 1px solid #CCCCCC;
        }

        #login .con .num {
            margin-top: 80px;
            padding: 0 20px;
            width: 100%;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.8);
        }

        #login .con {
            padding: 0 20px;
        }
    </style>

    <script>
        $(function () {
            //获取短信验证码
            var validCode = true;
            $(".msgs").click(function () {
                var time = 60;
                var code = $(this);
                console.log(phone);
                if (code.attr("class") === "msgs msgs1"){
                    console.log("已经发送了一次");
                    return false;
                }
                var phone = $('#phone').val();
                //ajax
                var aj = $.ajax({
                    url: '/send_code',// 跳转到 action
                    data: {
                        phone: phone
                    },
                    type: 'post',
                    cache: false,
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        if (data.code == 200) {
                            console.log(data);
                            if (validCode) {
                                validCode = false;
                                code.addClass("msgs1");
                                var t = setInterval(function () {
                                    time--;
                                    code.html(time + "秒");
                                    if (time == 0) {
                                        clearInterval(t);
                                        code.html("重新获取");
                                        validCode = true;
                                        code.removeClass("msgs1");
                                    }
                                }, 1000)
                            }
                        } else {
                            alert(data.msg);
                        }
                    },
                    error: function () {
                        // view("异常！");
                        alert("异常！");
                    }
                });
            })
        })
    </script>
</head>
<body>
<div id="login"
     style="background: url(http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/login_bg.jpg) no-repeat;">
    <div class="head">
        <a class="return" href="#"><i class="am-icon-angle-left"></i></a>
        <span>登 录</span>
    </div>
    <form action="/check_user_login/{{$good_id}}/{{$invite_app_id}}" method="post">
        <div class="con" style="margin-top: 150px;">
            <ul class="num">
                <li class="num_list">
                    <img src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/my1.png"
                         style="margin-top: 20px;">
                    <div class="am-form-group">
                        <input type="text" name="phone" id="phone" placeholder="请输入手机号"
                               style="width: 150px;height: 24px; line-height: 24px; margin-top: 20px;"/><span
                                class="msgs" style="margin-top: 14px;">获取短信验证码</span>
                    </div>
                    <div class="clear"></div>
                </li>
                <li class="num_list">
                    <img src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/password.png"
                         style="margin-top: 20px;">
                    <div class="am-form-group">
                        <input type="text" name="code" id="code" placeholder="请输入验证码"
                               style="height: 24px; line-height: 24px; margin-top: 20px;"/>
                    </div>
                    <div class="clear"></div>
                </li>
            </ul>
            <div class="find">

                <div class="clear"></div>
            </div>
            <input type="submit" class="lanch" value="登 录" style="background: #3bcece;"/>
        </div>
    </form>
</div>
</body>
</html>