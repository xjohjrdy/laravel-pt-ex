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
    <link rel="stylesheet" type="text/css" href="https://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/amazeui.css">
    <script type="text/javascript" src="https://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.min.js"></script>
    <script type="text/javascript"
            src="https://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.Spinner.js"></script>
    <script type="text/javascript" src="https://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/amazeui.js"></script>
    <!--核心样式-->
    <link rel="stylesheet" type="text/css" href="https://a119112.oss-cn-beijing.aliyuncs.com/style.css">
<body>

<body>
<!-----------轮播banner----------->
<div id="project_basic">
    <a title="" class="return" href="specialty_mall.html"><i class="am-icon-angle-left"
                                                             style="font-size: 24px;text-align: center;"></i></a>
    <div class="sail">
        <div class="am-slider am-slider-default">
            <ul class="am-slides">
                <li><img src="{{$good->header_img}}"/></li>
            </ul>
        </div>

        <p style="color: #F10105; font-size: 20px;">￥{{$good->price}}<span
                    style="float: none;font-size: 14px; color: #666;text-decoration:line-through;">￥{{$good->cost_price}}</span>
        </p>
        <p style="font-size: 4px; color: #999; margin-bottom: 8px;">推广赚{{$good->profit_value}}元</p>
        <p>{{$good->title}}</p>
        <label>{{$good->zone}}</label>
        {{--<font style="width: 50%;">销售5001笔</font>--}}
    </div>
    <!-----------点评----------->
    <!--<div class="all_comment" style="height: 88px;">
         <div class="item" style="position: relative;">
             <span style="font-size: 10px; line-height: 28px;">选择商品属性</span>
             <i class="am-icon-angle-right" style="font-size: 24px; position: absolute;right: 10px; top: -5px;"></i>
         </div>
        <ul>
            <li>
                    <div class="message" style="position: relative;">
                        <span style="font-size: 10px; line-height: 42px;">商品参数</span>
                        <i class="am-icon-angle-right" style="font-size: 24px; position: absolute;right: 10px;"></i>
                    </div>

            </li>
        </ul>

    </div>-->
    <!-----------商品详情----------->
    <div class="hot_sail">
        <p class="words" style="text-align: center; font-size: 14px; color: #999;">————&nbsp;&nbsp;&nbsp;<i
                    class="am-icon-image" style="margin-right: 3px;"></i>图文详情&nbsp;&nbsp;&nbsp;————</p>
        <div style="width: 94%;margin: 0 auto;">
            <h5>--------------------------------------------------</h5>
            <font>{{$good->detail_desc}}</font>
        </div>
        <img src="{{$good->detail_img}}"/>
    </div>
    <!-----------加入购物车/购买----------->
    <div id="buy_cart">
        <!-- <div class="collection"><a href="#"> <img src="images/collection_01.png"/><p>收藏</p></a></div> -->
        <ul class="am-avg-sm-1">
            <!--<li><a class="cart" href="#" data-am-modal="{target: '#my-popup1'}" style="width: 60%; background: #fff; border-right: 1px solid #f1f1f1;"><img src="images/icon-shop_car3.png" width="40%" alt=""></a></li>-->
            <!--<li><a class="cart" href="#" data-am-modal="#" style="width: 60%;margin-left: -40%; background: #fff"><img src="images/icon-shop_car4.png" width="40%" alt=""></a></li>-->
            <!--<li><a  class="cur" href="sure_order.html" style="margin-left: -80%;background: #df1523;">立即购买</a></li>-->
            <li><a class="cur" href="#" data-am-modal="{target: '#my-popup1'}" style="background: #df1523;">立即购买</a>
            </li>
        </ul>
        <div class="clear"></div>
    </div>

    <!------弹出层------>
    <div class="am-popup" id="my-popup1">
        <div class="white_bg_box">
            <span data-am-modal-close class="am-close">&times;</span>
            <div class="am-popup-bd am1">
                <!-------------商品属性（价位数量）---------------->
                <div class="p_number" style="background: #fff;">
                    <!------------商品规格-------------->
                    <div class="yListr" style="border-bottom: 1px solid #f1f1f1; padding: 15px 10px;">
                        <img src="{{$good->header_img}}" width="30%" alt="">
                        <div style="float: right; margin-right: 26%">
                            <p style="color: #F10105; font-size: 20px;">￥104.00<span
                                        style="float: none;font-size: 14px; color: #666;text-decoration:line-through;">￥298.00</span>
                            </p>
                            <p style="font-size: 4px; color: #999; margin-bottom: 8px;">&nbsp;已选属性：2瓶</p></div>
                    </div>
                    <!-----------数量----------->
                    <script type="text/javascript">
                        $(function () {
                            $(".yListr ul li input").click(function () {
                                $(this).addClass("yListrclickem").siblings().removeClass("yListrclickem");
                            })
                        })
                    </script>
                    <form action="/generate_order/{{$good->id}}/{{$invite_app_id}}" name="check" method="post" >
                        <div class="yListr" style="border-bottom: 1px solid #f1f1f1; padding: 15px 16px;">

                            @foreach ($good->custom as $k=>$customs)
                                <p>{{$k}}</p>
                                <ul style="border-bottom: 1px solid #f1f1f1; padding-bottom: 15px;">
                                    <li>
                                        @foreach ($customs as $v)
                                            <input name="{{$k}}" value="{{$v}}" type="text"/>
                                        @endforeach
                                    </li>
                                </ul>
                            @endforeach
                        </div>
                        <!-----------数量----------->
                        {{--<div class="number" style="position: relative;">--}}
                            {{--<p>购买数量 ：1</p >--}}
                            {{--<div style="position: absolute; top: 30%;right: 20px;">--}}
                                {{--<input name="" type="text" class="spinnerExample" style="" /></div>--}}
                        {{--</div>--}}
                        {{--<script type="text/javascript">--}}
                            {{--$('.spinnerExample').spinner({});--}}
                        {{--</script>--}}
                        <input class="sure" type="submit"  value="确定">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-----------轮播----------->
<script>
    $(function () {
        $('.am-slider').flexslider({
            controlNav: true,               // Boolean: 是否创建控制点
            directionNav: false,             // Boolean: 是否创建上/下一个按钮（previous/next）
            touch: true,                    // Boolean: 允许触摸屏触摸滑动滑块
        });
    });
</script>
<!-----------门票数量----------->
<script type="text/javascript">
    $(function () {

        $("#a").Spinner({value: 868, min: 10, len: 3, max: 1000});

        $("#b").Spinner({value: 99});

        $("#c").Spinner({value: 66});

        $("#d").Spinner();

    });
</script>

</body>
</html>
