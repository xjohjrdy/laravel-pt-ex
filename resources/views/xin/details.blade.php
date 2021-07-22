<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <link href="https://a119112.oss-cn-beijing.aliyuncs.com/app_question/css/mui.min.css" rel="stylesheet" />
    <style>
        .mui-bar{
            background-color: #fff;
        }
        body,
        .mui-content{
            background-color: #F5F5F5;
        }
        .answer,
        .ask{
            position: relative;
            width: 100%;
            padding: 25px 15px;
            font-size: 14px;
            line-height: 22px;
            color: #000;
            background-color: #fff;
        }
        .answer:after{
            content: "";
            position: absolute;
            bottom: 0;
            left:0;
            right:0;
            height: 1px;
            transform: scaleY(.5);
            -webkit-transform: scaleY(.5);
            background-color: #f5f5f5;
        }
        .ask{
            margin-bottom: 5px;
        }
        .red,
        .blue{
            width: 22px;
            height: 22px;
            font-size: 14px;
            display: inline-block;
            line-height: 22px;
            margin-right: 10px;
            text-align: center;
            border-radius: 5px;
            background-color: #66B6FA;
            color: #fff;
            vertical-align: middle;
        }
        .red{
            background-color: #FF747C;
        }
    </style>
</head>

<body>
<!-- 	<header class="mui-bar mui-bar-nav" style="box-shadow: 0 0 0; border-bottom:1px solid #f5f5f5;">
        <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left" style="color: #000;"></a>
        <h1 class="mui-title" >客服中心</h1>
    </header> -->
<div class="mui-content">
    <!--问-->
    <div class="ask">
        <span class="blue">问</span>{!!$data->question!!}
    </div>
    <!--答-->
    <div class="answer">
        <span class="red">答</span>{!!$data->answer!!}
    </div>
</div>
</body>

</html>