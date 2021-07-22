<!DOCTYPE html>
<html>
<head>
    <title>商品详情</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="telephone=no" name="format-detection">
    <meta name="viewport"
          content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <!--库-->
    <script type="text/javascript"
            src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/amazeui.js"></script>
    <!--核心样式-->
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/amazeui.css">
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/style.css">
    <style>
        .checkboxFour1 input[type=checkbox] {
            visibility: hidden;
        }

        .checkboxFour1 {
            width: 30px;
            height: 30px;
            display: inline-block;
            border-radius: 100%;
            position: relative;
        }

        .checkboxFour1 label {
            display: block;
            width: 32px;
            height: 32px;
            border-radius: 100px;
            cursor: pointer;
            position: absolute;
            top: 14px;
            left: -2px;
            z-index: 1;
            background: url(http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/ck_on.png);
        }

        .checkboxFour1 input[type=checkbox]:checked + label {
            background: url(http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/ck_on.png);
        }

        .checkboxFour input[type=checkbox] {
            visibility: hidden;
        }

        .checkboxFour {
            width: 66px;
            height: 30px;
            display: inline-block;
            border-radius: 100%;
            position: relative;
        }

        .checkboxFour label {
            display: block;
            width: 32px;
            height: 32px;
            border-radius: 100px;
            cursor: pointer;
            position: absolute;
            top: 9px;
            left: -2px;
            z-index: 1;
            background: url(http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/ck_on.png);
        }

        .checkboxFour input[type=checkbox]:checked + label {
            background: url(http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/ck_on.png);
        }
    </style>
</head>
<body>
<!-----------头部----------->
<div id="header">
    <a class="return" href="javascript:history.go(-1)"><i class="am-icon-chevron-left"></i></a>
    <span>确认订单</span>
</div>
<!----------------收货人------------------>
<form action="/use_money/{{$order_detail['order_id']}}" method="post">
<div id="sure_order">
    <ul class="consignee" style="height: 120px;">
            @if (!empty($order_detail['address']['collection']))
                <li class="consignee_name" style="margin-left: -5px;">
                    <p>
                        收货人<span
                                style="color: #fff; margin-left: 10px;">{{$order_detail['address']['collection']}}</span>
                    </p>
                    <div class="clear"></div>
                </li>
                <li class="consignee_address" style="position: relative;"><img
                            src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/consignee_address.png">
                    <p style="font-size: 14px">{{$order_detail['address']['zone']}}</p>
                    <p>{{$order_detail['address']['detail']}}</p>
                    <img src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/right.png" alt=""
                         style="position: absolute; top: 10%">
                    <div class="clear"></div>
                </li>
            @else
            <a href="/add_address/{{$good_id}}/{{$invite_app_id}}/{{$order_detail['order_id']}}">
                <li class="consignee_name" style="margin-left: -5px;">
                    <p>用户名<span style="color: #fff; margin-left: 10px;"></span></p>
                    <div class="clear"></div>
                </li>
                <li class="consignee_address" style="position: relative;"><img
                            src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/icon_consignee_address.png">
                    <p style="font-size: 14px">请设置收货地址</p>
                    <img src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/right.png" alt=""
                         style="position: absolute; top: 10%">
                    <div class="clear"></div>
                </li>
            </a>
            @endif
    </ul>

    <!----------------商品清单------------------>
    <div class="my_goods_list" style="margin-bottom: 10px;">
        <div class="commodity_list" style="margin-bottom: 0px;">商品金额<span style="float: right; margin-right: 10px;">￥&nbsp;{{$order_detail['all_price']}}</span>
        </div>
        <div class="commodity_list">总运费<span style="float: right; margin-right: 10px;">&nbsp;支付的时候系统会自动计算</span></div>
        <div class="commodity_list" style="line-height: 60px;">
            <div class="checkboxFour1">
                <input type="checkbox" value="1" id="checkboxFourInput1" name=""/>
                <label for="checkboxFourInput1"></label>
            </div>
            使用我的币<span style="color: #999;margin-left: 20px;">共{{$order_detail['account_ptb']}}我的币</span><span
                    style="float: right; margin-right: 10px;">-{{$order_detail['deduct_ptb']}}我的币</span></div>
    </div>
    <div class="my_goods_list">
        {{--<div class="commodity_list" style="font-size: 16px; line-height: 50px;">全部使用支付宝支付（如果勾选我的币，则会优先扣除我的币）<span--}}
                    {{--style="float: right; margin-right: 10px; color: #dc141f;">￥&nbsp;{{$order_detail['all_price']}}</span></div>--}}

        <div class="commodity_list" style="line-height: 50px; position: relative; margin-bottom: 10px;">
            <div class="checkboxFour">
                <input type="checkbox" value="2" id="checkboxFourInput" name=""/>
                <label for="checkboxFourInput"></label>
            </div>
            <div style="display: inline-block; width: 28px; height: 28px; background: url(http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/images/zfb.png); position: absolute; top: 11px;left: 42px;"></div>
            微信
        </div>

        <ul>
            @foreach ($order_detail['order_detail'] as $item)
                <li class="commodity_list_nav">
                    <img src="{{$item['header_img'][0]}}"/>
                    <div class="project_cont">
                        <p style="font-size: 13px; float: left; width: 85%;">{{$item['title']}}</p><span
                                style="float: right; font-size: 14px; color: #333;">￥&nbsp;{{$item['real_price']}}</span>
                        <p style="float: left; font-size: 12px; color: #999;">{{$item['desc']}}</p><span
                                style="float: right;">x{{$item['number']}}</span>
                    </div>
                </li>
            @endforeach
        </ul>

    </div>

    <!----------------合计------------------>
    <div id="total_money">
        <input class="sure" href="#"  value="付款" type="submit"/>
    </div>
</div>
</form>
</body>
</html>

