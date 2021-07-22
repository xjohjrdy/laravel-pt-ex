<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{$title}}--葡萄代理商管理后台</title>
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
    <blockquote class="layui-elem-quote quoteBox">
        <div class="layui-form">
            <div class="layui-inline">
                <label class="layui-form-label">开始时间</label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" id="test-begin" placeholder="创建时间-开始时间">
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">结束时间</label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" id="test-end" placeholder="创建时间-结束时间">
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-inline">
                    <select id="status" lay-verify="required" lay-search="">
                        <option value="">搜索-订单状态</option>
                        <option value="0">待付款</option>
                        <option value="1">待发货（已付款）</option>
                        <option value="2">待收货（已发货）</option>
                        <option value="3">待评价</option>
                        <option value="4">退款与售后</option>
                        <!--0：待付款，1：待发货，2：待收货（已发货），3：待评价，4：退款与售后-->
                    </select>
                </div>
            </div>

            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal" href="#" id="excel-data">导出对应供应商订单</a>
            </div>
        </div>
    </blockquote>
<script type="text/javascript" src="{{ URL::asset('agent/layui/layui.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('agent/js/center/educe.js') }}"></script>
</body>
</html>