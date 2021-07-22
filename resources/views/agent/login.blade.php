<!DOCTYPE html>
<html class="loginHtml">
<head>
    <meta charset="utf-8">
    <title>登录</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="icon" href="{{URL::asset('agent/favicon.ico')}}">
    <link rel="stylesheet" href="{{ URL::asset('agent/layui/css/layui.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('agent/css/public.css') }}">
    <script type="text/javascript" src="{{ URL::asset('agent/js/jquery.min.js') }}"></script>

</head>
<body class="loginBody">
<canvas style="position:fixed;z-index:-1;"></canvas>
<form class="layui-form">
    <div class="login_face"><img src="{{ URL::asset('agent/images/face.jpg') }}" class="userAvatar"></div>
    <div class="layui-form-item input-item">
        <label for="userPhone">手机号</label>
        <input type="text" placeholder="请输入手机号" autocomplete="off" id="userPhone" class="layui-input"
               lay-verify="required|phone">
    </div>
    <div class="layui-form-item input-item">
        <label for="password">密码</label>
        <input type="password" placeholder="请输入密码" autocomplete="off" id="password" class="layui-input"
               lay-verify="required">
    </div>
    <div class="layui-form-item input-item" id="phoneCode">
        <label for="code">短信验证码</label>
        <input type="text" placeholder="请输入验证码" autocomplete="off" id="code" class="layui-input" lay-verify="required">
        <a id="getVerifications" class="layui-btn layui-btn-danger">获取验证码</a>
    </div>
    <div class="layui-form-item">
        <button class="layui-btn layui-block" lay-filter="login" lay-submit>登录</button>
    </div>
</form>
<script type="text/javascript" src="{{ URL::asset('agent/layui/layui.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('agent/page/login/login.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('agent/js/cache.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('agent/page/login/jell.js') }}"></script>
</body>
</html>