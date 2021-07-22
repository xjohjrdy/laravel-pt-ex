<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"/>
    <title>我的浏览器圈子</title>
    <link rel="stylesheet" href="https://a119112.oss-cn-beijing.aliyuncs.com/circle/index.css">
    <link rel="stylesheet" href="https://a119112.oss-cn-beijing.aliyuncs.com/circle/css/swiper.min.css">
</head>
<body>
<header>
    <a href="{{$url}}">
        <div>
            <img src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/images/logo.png" alt="" class="lf">
            <div class="lf">
                <p class="header-title">我的浏览器</p>
                <span class="dec">我的浏览器是一款多功能为一体的浏览器APP</span>
            </div>
            <button class="lr">打开</button>
        </div>
    </a>
    <div class="user-frm">
        <img src="{{$circle_ring->ico_img}}" alt="" class="hea lf">
        <div>
            <p><span class="user-title">{{$circle_ring->ico_title}}</span> <span class="good"><img
                            src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/images/good.png" alt="">非常活跃</span>
            </p>
            <p><span>成员</span><span class="num">{{$circle_ring->number_person}}人</span><span>动态</span> <span class="num">{{$circle_ring->number_zone}}
                    条</span></p>
        </div>
    </div>
</header>
<p class="idx">圈子当前价值：{{$circle_ring->add_price}}+人脉价值</p>
<div>
    <div class="content-title">
        <span class="active dynamic">动态</span>
        <span class="Intro">简介</span>
    </div>
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
            @foreach ($circle_active as $item)
                <!--动态-->
                    <div class="list-frm">
                        <div class="list-header">
                            <img src="{{$item->user_ico_img}}" alt=""
                                 class="img-lf">
                            <div style="display: inline-block">
                                <p>{{$item->user_name}}</p>
                                <P class="published-time">{{$item->created_at}}</P>
                            </div>
                        </div>
                        <p class="content">{{$item->circle_content}}</p>
                        <div class="list-footer">
                            <span class="source">来自 {{$item->circle_name}}</span>
                            <div class="lr">
                            <span><img src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/images/message.png"
                                       alt=""><span>{{$item->like}}</span></span>
                                <span><img src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/images/heart.png"
                                           alt=""><span>{{$item->have_number}}</span></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-slide">
                <!--简介-->
                <div class="introduce-frm">
                    <div class="introduce-header">
                        <p class="grapes"><img src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/images/logo.png"
                                               alt="">我的浏览器</p>
                        <p class="introduce-hea">女装外贸交流圈</p>
                        <p class="introduce">人以圈分，圈以格局划分</p>
                    </div>
                    <div class="introduce-content">
                        <p class="con-title">本圈简介</p>
                        <p>这是一个神奇的圈子</p>
                        <p>在这里大家可以畅所欲言，向女装外贸的大佬们请教任何问题</p>
                        <p>欢迎加入</p>
                    </div>
                    <div style="text-align: center">
                        <img src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/images/invi.png" alt=""
                             class="invi">
                    </div>
                    <div class="introduce-content" style="text-align: center;padding-bottom: 0">
                        <p class="succ">成功邀请加入圈子即可 <span class="obtain">获得奖励</span></p>
                        <p>在圈子不仅可以学习知识技能，拓展人脉资源</p>
                        <p>还可以邀请好友加入圈子获得高额佣金</p>
                        <img src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/images/test.png" alt=""
                             class="test-img">
                    </div>
                    <div class="introduce-content" style="text-align: center;margin-top: .1rem">
                        <p class="join">加入圈子可以获得</p>
                        <button>1.参与数千女装外贸相关话题</button>
                        <button>2.免费共享行业最新资讯</button>
                        <button>3.女装外贸经验及技巧</button>
                    </div>
                    <div class="introduce-content code-frm">
                        <img src="{{$url_img}}" alt=""
                             class="code">
                        <p>加入圈子</p>
                        <p>抱紧大咖的大腿</p>
                        <p class="scan">扫描二维码</p>
                        <button id="join">马上加入</button>
                    </div>
                    <div class="introduce-header footer">
                        <p class="grapes">我的浏览器圈子 ● 女装外贸交流圈</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
<script src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/js/jquery.min.js"></script>
<script src="https://a119112.oss-cn-beijing.aliyuncs.com/circle/js/swiper.min.js"></script>
<script>
    var i = 0;
    var mySwiper = new Swiper('.swiper-container', {
        autoplay: false,//可选选项，自动滑动
        watchSlidesProgress: true,
        loop: false,
        autoHeight: true,
    });
    $('.swiper-container').on('touchend', function (ev) {
        if (i == mySwiper.activeIndex) {
            $(".Intro").removeClass('active');
            $(".dynamic").addClass('active')
        } else {
            $(".dynamic").removeClass('active');
            $(".Intro").addClass('active')
        }
    });
    $(".dynamic").click(function () {
        $(".Intro").removeClass('active');
        $(".dynamic").addClass('active');
        mySwiper.slideTo(0)
    });
    $(".Intro").click(function () {
        $(".dynamic").removeClass('active');
        $(".Intro").addClass('active');
        mySwiper.slideTo(1)
    })
</script>
</html>