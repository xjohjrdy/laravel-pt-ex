<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>首页--我的代理商管理后台</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="{{ URL::asset('agent/layui/css/layui.css') }}" media="all"/>
    <link rel="stylesheet" href="{{ URL::asset('agent/css/public.css') }}" media="all"/>
</head>
<body class="childrenBody">
<blockquote class="layui-elem-quote layui-bg-green">
    <div id="nowTime"></div>
</blockquote>
<div class="layui-row layui-col-space10 panel_box">

    <div class="panel layui-col-xs12 layui-col-sm6 layui-col-md4 layui-col-lg2">
        <a href="javascript:;">
            <div class="panel_icon layui-bg-orange">
                <i class="layui-anim seraph icon-liulanqi" data-icon="icon-liulanqi"></i>
            </div>
            <div class="panel_word userAll">
                <span style="font-size:15px;" class="ipLog"></span>
                <cite>登录ip地址</cite>
            </div>
        </a>
    </div>
    <div class="panel layui-col-xs12 layui-col-sm6 layui-col-md4 layui-col-lg2">
        <a href="javascript:;">
            <div class="panel_icon layui-bg-cyan">
                <i class="layui-anim seraph icon-wechat"></i>
            </div>
            <div class="panel_word outIcons">
                <span class="weChat">XXX</span>
                <cite>微信客服</cite>
            </div>
        </a>
    </div>
    <div class="panel layui-col-xs12 layui-col-sm6 layui-col-md4 layui-col-lg2">
        <a href="javascript:;">
            <div class="panel_icon layui-bg-blue">
                <i class="layui-anim seraph icon-clock"></i>
            </div>
            <div class="panel_word">
                <span class="loginTime"></span>
                <cite>上次登录时间</cite>
            </div>
        </a>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset('agent/layui/layui.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('agent/js/main.js') }}"></script>
</body>
</html>