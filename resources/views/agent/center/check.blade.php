<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{$title}}--我的代理商管理后台</title>
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
<form class="layui-form">
    <blockquote class="layui-elem-quote quoteBox">
        <form class="layui-form">
            {{--<div class="layui-inline">--}}
                {{--<div class="layui-input-inline">--}}
                    {{--<input type="text" class="layui-input searchVal" placeholder="请输入搜索的内容"/>--}}
                {{--</div>--}}
                {{--<a class="layui-btn search_btn" data-type="reload">搜索</a>--}}
            {{--</div>--}}
            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal addNews_btn">添加商品</a>
            </div>
            <div class="layui-inline">
                <a class="layui-btn layui-btn-danger layui-btn-normal delAll_btn">批量删除</a>
            </div>
        </form>
    </blockquote>
    <table id="newsList" lay-filter="newsList"></table>

    <!--审核状态-->
    <script type="text/html" id="newsStatus">
        @{{#  if(d.review_status == "0"){ }}
        <span class="layui-blue">审核中</span>
        @{{#  } else if(d.review_status == "1"){ }}
        <span class="layui-green">审核通过</span>
        @{{#  } else if(d.review_status == "2"){ }}
        <span class="layui-red">审核失败</span>
        @{{#  } else { }}
        状态异常
        @{{#  }}}
    </script>

    <!--操作-->
    <script type="text/html" id="newsListBar">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
        {{--<a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="look">预览</a>--}}
    </script>
</form>
<script type="text/javascript" src="{{ URL::asset('agent/layui/layui.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('agent/js/center/check.js') }}"></script>
</body>
</html>