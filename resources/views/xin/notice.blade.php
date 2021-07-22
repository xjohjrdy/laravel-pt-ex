<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <style>
        *{
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        body{
            background-color: #fff;
            background: url('https://a119112.oss-cn-beijing.aliyuncs.com/app_question/notice/body-bg.png')no-repeat;
            background-size: 100%;
            background-position: 0 88px;
        }
        /*头部*/
        header{
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            height: 44px;
            line-height: 34px;
            text-align: center;
            color: #000;
            font-size: 18px;
            border-bottom: 1px solid #EDEDED;
            background-color: #fff;
            padding: 5px 15px;
        }
        /*返回按钮*/
        .back{
            position: absolute;
            top: 14px;
            left: 15px;
            width: 9px;
            height: 16px;
            background-image: url('https://a119112.oss-cn-beijing.aliyuncs.com/app_question/notice/back.png');
            background-size: 100% 100%;
        }
        /*分享按钮*/
        @media only screen and (min-width: 0px) and (max-width: 400px) {
            .share{
                position: absolute;
                top: 14px;
                right: 15px;
                width: 16px;
                height: 16px;
                background-image: url('https://a119112.oss-cn-beijing.aliyuncs.com/app_question/notice/share2.png');
                background-size: 100% 100%;
            }
        }
        @media only screen and (min-width: 400px) and (max-width: 600px) {
            .share{
                position: absolute;
                top: 14px;
                right: 15px;
                width: 16px;
                height: 16px;
                background: url('https://a119112.oss-cn-beijing.aliyuncs.com/app_question/notice/share2.png') no-repeat;
                background-size: 100% 100%;
            }
        }
        /*内容*/
        .content{
            padding: 67px 15px 0 15px;
        }
        /*文章标题*/
        .title{
            width: 5.82rem;
            height: auto;
            margin: 0 auto;
            padding: 0 .4rem .05rem .4rem;
            color: #000;
            background: url('https://a119112.oss-cn-beijing.aliyuncs.com/app_question/notice/bg_xiangqing%402x.png') bottom no-repeat;
            background-size: 100% auto;
        }
        .title-text{
            margin-top: .2rem;
            font-size: .3rem;
            line-height: .4rem;
            font-weight: bold;
            margin: 0 auto;
            text-align: center;
        }
        .title>p{
            font-size: .2rem;
            font-weight: 400;
            line-height: .38rem;
            text-align: right;
            margin-right: -.2rem;
            color: #ccc;
        }
        .time{
            display: inline-block;
            margin-left: .2rem;
        }
        /*文章内容*/
        .article{
            width: 100%;
            padding-top: .5rem;
        }
        .article>p{
            padding-bottom: 5px;
            line-height: 22px;
            font-size: 14px;
            color: #666;
        }
        .article img{display:block;width:100%;}
    </style>
    <script>
        //rem适配（把整个屏幕的宽度当成7.5rem）
        var _html = document.getElementsByTagName('html')[0];
        var ch = document.documentElement.clientWidth;
        if (ch>750){
            _html.style.fontSize ='100px';
        }else{
            _html.style.fontSize = ch/7.5 +'px';
        }
    </script>
</head>
<body>
<!--头部-->
<!--<header>-->
<!--&lt;!&ndash;返回按钮&ndash;&gt;-->
<!--<div class="back"></div>-->
<!--&lt;!&ndash;分享按钮&ndash;&gt;-->
<!--<div class="share"></div>-->
<!--公告详情-->
<!--</header>-->
<!--内容-->
<div class="content">
    <!--文章标题-->
    <div class="title">
        <div class="title-text">
            {!! $data->title !!}
        </div>
        <p><span class="time">{!!date("Y-m-d H:i:s" ,$data->create_time)!!}</span></p>
    </div>
    <!--文章内容-->
    <div class="article">
        <!--一个段落-->
        {!!$data->content!!}
    </div>
</div>
</body>
</html>
