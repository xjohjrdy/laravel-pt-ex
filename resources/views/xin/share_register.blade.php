<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{!! isset($poster->title)?$poster->title:"立即注册" !!}</title>
    <meta name="viewport" content="width=device-width,maximum-scale=1,initial-scale=1"/>
    <script type="application/javascript"
            src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register-ycm.min.js"></script>

    <link rel="stylesheet" type="text/css"
          href="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register-ycm.min.css">
    <link href="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register-common.css" type="text/css"
          rel="stylesheet">
    <style>

        .send_layer {
            background: rgba(0, 0, 0, 0.5);
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            display: none;
        }

        .send_layer .img {
            width: 80%;
            margin: 40% auto;
        }

        .send_layer .img img {
            width: 100%;
        }

        .tc_layer {
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
        }

        .tc_layer img {
            width: 100%;
            height: 100%;
        }

        .tc_layer .logister_btn {
            display: block;
            width: 30%;
            height: 1rem;
            border: 1px solid #fff;
            border-radius: 0.2rem;
            color: #333;
            background: #fff;
            line-height: 1rem;
            text-align: center;
            position: fixed;
            bottom: 2rem;
            left: 35%;
            z-index: 200;
        }
    </style>

    <script type="text/javascript">!function (N, M) {
            function L() {
                var a = I.getBoundingClientRect().width;
                a / F > 540 && (a = 540 * F);
                var d = a / 10;
                I.style.fontSize = d + "px", D.rem = N.rem = d
            }

            var K, J = N.document, I = J.documentElement, H = J.querySelector('meta[name="viewport"]'),
                G = J.querySelector('meta[name="flexible"]'), F = 0, E = 0, D = M.flexible || (M.flexible = {});
            if (H) {
                console.warn("将根据已有的meta标签来设置缩放比例");
                var C = H.getAttribute("content").match(/initial\-scale=([\d\.]+)/);
                C && (E = parseFloat(C[1]), F = parseInt(1 / E))
            } else {
                if (G) {
                    var B = G.getAttribute("content");
                    if (B) {
                        var A = B.match(/initial\-dpr=([\d\.]+)/), z = B.match(/maximum\-dpr=([\d\.]+)/);
                        A && (F = parseFloat(A[1]), E = parseFloat((1 / F).toFixed(2))), z && (F = parseFloat(z[1]), E = parseFloat((1 / F).toFixed(2)))
                    }
                }
            }
            if (!F && !E) {
                var y = N.navigator.userAgent, x = (!!y.match(/android/gi), !!y.match(/iphone/gi)),
                    w = x && !!y.match(/OS 9_3/), v = N.devicePixelRatio;
                F = x && !w ? v >= 3 && (!F || F >= 3) ? 3 : v >= 2 && (!F || F >= 2) ? 2 : 1 : 1, E = 1 / F
            }
            if (I.setAttribute("data-dpr", F), !H) {
                if (H = J.createElement("meta"), H.setAttribute("name", "viewport"), H.setAttribute("content", "initial-scale=" + E + ", maximum-scale=" + E + ", minimum-scale=" + E + ", user-scalable=no"), I.firstElementChild) {
                    I.firstElementChild.appendChild(H)
                } else {
                    var u = J.createElement("div");
                    u.appendChild(H), J.write(u.innerHTML)
                }
            }
            N.addEventListener("resize", function () {
                clearTimeout(K), K = setTimeout(L, 300)
            }, !1), N.addEventListener("pageshow", function (b) {
                b.persisted && (clearTimeout(K), K = setTimeout(L, 300))
            }, !1), "complete" === J.readyState ? J.body.style.fontSize = 12 * F + "px" : J.addEventListener("DOMContentLoaded", function () {
                J.body.style.fontSize = 12 * F + "px"
            }, !1), L(), D.dpr = N.dpr = F, D.refreshRem = L, D.rem2px = function (d) {
                var c = parseFloat(d) * this.rem;
                return "string" == typeof d && d.match(/rem$/) && (c += "px"), c
            }, D.px2rem = function (d) {
                var c = parseFloat(d) / this.rem;
                return "string" == typeof d && d.match(/px$/) && (c += "rem"), c
            }
        }(window, window.lib || (window.lib = {}));</script>
</head>
<body>
<div class="wrap">
    <div class="head">
        <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register_img_03.png" alt=""
             class="return">
        <p class="title font32">注册</p>
    </div>
    <form id="form" action="xin_do_register" method="post">
        <div class="register_content font24">
            <div class="dd1"
                 style="width: 100%;height: 1.4rem;background: #f2f2f2;overflow: hidden;margin-bottom: 0.3rem;">
                <select id="area-sel" onchange="toggle(this)"
                        style="background:url('https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/top1.png') no-repeat right center;width:55px;padding:0.05rem 0.1rem;margin-left: 0.4rem;height: 1.4rem;background-size: 8px 5px;">
                    <option value="86">中国</option>
                    <option value="95">缅甸</option>
                    <option value="84">越南</option>
                    <option value="65">新加坡</option>
                    <option value="376">安道尔</option>
                    <option value="93">阿富汗</option>
                    <option value="1268">安提瓜和巴布达</option>
                    <option value="1264">安圭拉</option>
                    <option value="355">阿尔巴尼亚</option>
                    <option value="374">亚美尼亚</option>
                    <option value="244">安哥拉</option>
                    <option value="1684">美属萨摩亚</option>
                    <option value="43">奥地利</option>
                    <option value="297">阿鲁巴</option>
                    <option value="994">阿塞拜疆</option>
                    <option value="387">波斯尼亚和黑塞哥维那</option>
                    <option value="1246">巴巴多斯</option>
                    <option value="880">孟加拉国</option>
                    <option value="32">比利时</option>
                    <option value="226">布基纳法索</option>
                    <option value="359">保加利亚</option>
                    <option value="257">布隆迪</option>
                    <option value="229">贝宁</option>
                    <option value="1441">百慕大群岛</option>
                    <option value="673">文莱</option>
                    <option value="591">玻利维亚</option>
                    <option value="55">巴西</option>
                    <option value="1242">巴哈马</option>
                    <option value="975">不丹</option>
                    <option value="267">博茨瓦纳</option>
                    <option value="375">白俄罗斯</option>
                    <option value="501">伯利兹</option>
                    <option value="243">刚果民主共和国</option>
                    <option value="236">中非共和国</option>
                    <option value="242">刚果共和国</option>
                    <option value="41">瑞士</option>
                    <option value="225">象牙海岸</option>
                    <option value="682">库克群岛</option>
                    <option value="56">智利</option>
                    <option value="237">喀麦隆</option>
                    <option value="57">哥伦比亚</option>
                    <option value="506">哥斯达黎加</option>
                    <option value="53">古巴</option>
                    <option value="238">开普</option>
                    <option value="599">库拉索</option>
                    <option value="357">塞浦路斯</option>
                    <option value="420">捷克</option>
                    <option value="253">吉布提</option>
                    <option value="45">丹麦</option>
                    <option value="1767">多米尼加</option>
                    <option value="213">阿尔及利亚</option>
                    <option value="593">厄瓜多尔</option>
                    <option value="372">爱沙尼亚</option>
                    <option value="20">埃及</option>
                    <option value="251">埃塞俄比亚</option>
                    <option value="358">芬兰</option>
                    <option value="679">斐济</option>
                    <option value="298">法罗群岛</option>
                    <option value="241">加蓬</option>
                    <option value="1473">格林纳达</option>
                    <option value="995">格鲁吉亚</option>
                    <option value="594">法属圭亚那</option>
                    <option value="350">直布罗陀</option>
                    <option value="299">格陵兰岛</option>
                    <option value="220">冈比亚</option>
                    <option value="224">几内亚</option>
                    <option value="590">瓜德罗普岛</option>
                    <option value="240">赤道几内亚</option>
                    <option value="30">希腊</option>
                    <option value="502">瓜地马拉</option>
                    <option value="1671">关岛</option>
                    <option value="245">几内亚比绍共和国</option>
                    <option value="592">圭亚那</option>
                    <option value="504">洪都拉斯</option>
                    <option value="385">克罗地亚</option>
                    <option value="509">海地</option>
                    <option value="36">匈牙利</option>
                    <option value="353">爱尔兰</option>
                    <option value="972">以色列</option>
                    <option value="354">冰岛</option>
                    <option value="1876">牙买加</option>
                    <option value="962">约旦</option>
                    <option value="81">日本</option>
                    <option value="996">吉尔吉斯斯坦</option>
                    <option value="855">柬埔寨</option>
                    <option value="686">基里巴斯</option>
                    <option value="269">科摩罗</option>
                    <option value="1869">圣基茨和尼维斯</option>
                    <option value="82">韩国</option>
                    <option value="965">科威特</option>
                    <option value="1345">开曼群岛</option>
                    <option value="856">老挝</option>
                    <option value="961">黎巴嫩</option>
                    <option value="1758">圣露西亚</option>
                    <option value="423">列支敦士登</option>
                    <option value="94">斯里兰卡</option>
                    <option value="231">利比里亚</option>
                    <option value="266">莱索托</option>
                    <option value="370">立陶宛</option>
                    <option value="352">卢森堡</option>
                    <option value="371">拉脱维亚</option>
                    <option value="218">利比亚</option>
                    <option value="212">摩洛哥</option>
                    <option value="377">摩纳哥</option>
                    <option value="373">摩尔多瓦</option>
                    <option value="382">黑山</option>
                    <option value="261">马达加斯加</option>
                    <option value="389">马其顿</option>
                    <option value="223">马里</option>
                    <option value="976">蒙古</option>
                    <option value="853">中国澳门</option>
                    <option value="596">马提尼克</option>
                    <option value="222">毛里塔尼亚</option>
                    <option value="1664">蒙特塞拉特岛</option>
                    <option value="356">马耳他</option>
                    <option value="230">毛里求斯</option>
                    <option value="960">马尔代夫</option>
                    <option value="265">马拉维</option>
                    <option value="258">莫桑比克</option>
                    <option value="264">纳米比亚</option>
                    <option value="687">新喀里多尼亚</option>
                    <option value="227">尼日尔</option>
                    <option value="505">尼加拉瓜</option>
                    <option value="31">荷兰</option>
                    <option value="47">挪威</option>
                    <option value="977">尼泊尔</option>
                    <option value="64">新西兰</option>
                    <option value="968">阿曼</option>
                    <option value="507">巴拿马</option>
                    <option value="51">秘鲁</option>
                    <option value="689">法属波利尼西亚</option>
                    <option value="675">巴布亚新几内亚</option>
                    <option value="48">波兰</option>
                    <option value="508">圣彼埃尔和密克隆岛</option>
                    <option value="1787">波多黎各</option>
                    <option value="351">葡萄牙</option>
                    <option value="680">帕劳</option>
                    <option value="595">巴拉圭</option>
                    <option value="974">卡塔尔</option>
                    <option value="262">留尼汪</option>
                    <option value="40">罗马尼亚</option>
                    <option value="381">塞尔维亚</option>
                    <option value="250">卢旺达</option>
                    <option value="677">所罗门群岛</option>
                    <option value="248">塞舌尔</option>
                    <option value="249">苏丹</option>
                    <option value="386">斯洛文尼亚</option>
                    <option value="421">斯洛伐克</option>
                    <option value="232">塞拉利昂</option>
                    <option value="378">圣马力诺</option>
                    <option value="221">塞内加尔</option>
                    <option value="252">索马里</option>
                    <option value="597">苏里南</option>
                    <option value="239">圣多美和普林西比</option>
                    <option value="503">萨尔瓦多</option>
                    <option value="1721">圣马丁岛（荷兰部分）</option>
                    <option value="963">叙利亚</option>
                    <option value="268">斯威士兰</option>
                    <option value="1649">特克斯和凯科斯群岛</option>
                    <option value="235">乍得</option>
                    <option value="228">多哥</option>
                    <option value="992">塔吉克斯坦</option>
                    <option value="670">东帝汶</option>
                    <option value="993">土库曼斯坦</option>
                    <option value="676">汤加</option>
                    <option value="90">土耳其</option>
                    <option value="1868">特立尼达和多巴哥</option>
                    <option value="255">坦桑尼亚</option>
                    <option value="380">乌克兰</option>
                    <option value="256">乌干达</option>
                    <option value="598">乌拉圭</option>
                    <option value="998">乌兹别克斯坦</option>
                    <option value="1784">圣文森特和格林纳丁斯</option>
                    <option value="58">委内瑞拉</option>
                    <option value="1340">英属处女群岛</option>
                    <option value="1284">美属维尔京群岛</option>
                    <option value="678">瓦努阿图</option>
                    <option value="685">萨摩亚</option>
                    <option value="269">马约特</option>
                    <option value="260">赞比亚</option>
                    <option value="263">津巴布韦</option>
                    <option value="971">阿拉伯联合酋长国</option>
                    <option value="54">阿根廷</option>
                    <option value="61">澳大利亚</option>
                    <option value="973">巴林</option>
                    <option value="1">加拿大</option>
                    <option value="49">德国</option>
                    <option value="34">西班牙</option>
                    <option value="33">法国</option>
                    <option value="44">英国</option>
                    <option value="233">加纳</option>
                    <option value="852">中国香港</option>
                    <option value="62">印度尼西亚</option>
                    <option value="91">印度</option>
                    <option value="964">伊拉克</option>
                    <option value="98">伊朗</option>
                    <option value="39">意大利</option>
                    <option value="254">肯尼亚</option>
                    <option value="7">哈萨克斯坦</option>
                    <option value="52">墨西哥</option>
                    <option value="60">马来西亚</option>
                    <option value="234">尼日利亚</option>
                    <option value="63">菲律宾</option>
                    <option value="92">巴基斯坦</option>
                    <option value="7">俄罗斯</option>
                    <option value="966">沙特阿拉伯</option>
                    <option value="46">瑞典</option>
                    <option value="66">泰国</option>
                    <option value="886">中国台湾</option>
                    <option value="1">美国</option>
                    <option value="27">南非</option>
                    <option value="216">突尼斯</option>
                    <option value="967">也门</option>
                    <option value="970">巴勒斯坦</option>
                </select>
                <span class="font26" id="pre-code"
                      style="width: 50%;height: 1rem;margin-top: 0.2rem;border-left: 1px solid #d5d5d5;padding-left: 0.2rem">+86</span>
                <input name="area_code" id="area_code" hidden value="86"/>
            </div>
            <div class="d1">
                <div class="left_img">
                    <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register_img_07.png"
                         alt="">
                </div>
                <input style="width: 80%" id="mobile" type="text" name="phone" placeholder="输入手机号码" class="phone">
            </div>
            <div class="d1">

                <div class="left_img">
                    <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register_img_10.png" alt=""
                         class="yzm">
                </div>
                <input id="sms_code" type="text" name="sms_code" placeholder="输入短信验证码" class="phone">
                <a id="send_register_code" onclick="sendCode(this)" class="send">发送验证码</a>
            </div>
            <div class="d2">
                <div class="d2_img">
                    <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register_img_12.png"
                         alt="">
                    <p class="font28">设置密码</p>
                </div>
                <input id="password" name="password" type="password" placeholder="支持6~12位英文、数字" class="pass">
                <!--<img src="__MOBILE__img/register_img_14.png" alt="" class="eye">-->
            </div>
            <div class="d2">
                <div class="d2_img">
                    <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register_img_18.png"
                         alt="">
                    <p class="font28">确认密码</p>
                </div>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="" class="pass">
            </div>
            <div class="d2 d2_5">
                <div class="d2_img">
                    <img src="https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/XIN/register_img_26.png"
                         alt="">
                    <p class="font28">推荐人</p>
                </div>
                <input id="parent_id" {!! empty($parent_id)?"":"readonly" !!} name="parent_id"
                       value="{!! $parent_id !!}" type="text"
                       placeholder="输入推荐人ID （选填）" class="pass">
            </div>
            <div class="d3">
                <input id="agreement" checked type="checkbox">
                <a href="xin_user_agreementr" target="_blank">阅读并接收《葡萄浏览器用户协议》</a>
            </div>
            <a id="form-submit" class="complete font30" onclick="submitForm()">完成</a>
        </div>
    </form>
    @if (isset($poster))
        <div class="tc_layer">
            <img src="{!! $poster->img_url !!}">
            <a href="#" onclick="hideTcLayer()" class="logister_btn font30">立即注册</a>
        </div>
    @endif
    <div class="send_layer" onclick="hideSendLayer()">
        <div class="img">
            <img src="http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/mobile/32_03.png">
        </div>
    </div>
</div>
</body>
<script src="http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/js/jquery.min.js"></script>
<script src="http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/js/layer.js"></script>
<script src="http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/js/check-browser.js"></script>

<script>
    hasSubmit = 0
    //下载地址
    //是否ios系统browser.versions.ios
    var download = browser.versions.ios ? "{{config('app_download.ios')}}" : "{{config('app_download.android')}}";
    //验证用正则
    var preg = {
        password: /^(\w){6,12}$/,
        real_name: /^[\u4e00-\u9fa5a-zA-Z]{1,10}$/u,
        alipay: /(^1[0-9]{10}$)|(^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$)/
    }

    function toggle(obj) {
        var aaa = obj.options[obj.selectedIndex].text;
        var temp_width = aaa.length * 14;
        $('#area-sel').css("width", (temp_width + 18) + "px");
        $('#pre-code').text('+' + obj.value);
        $('#area_code').val(obj.value);
    }

    function getMobilePreg(area_code) {
        var preg = '';
        switch (area_code) {
            case '852':
                preg = /^\d{8}$/
                break;
            case '86':
                preg = /^\d{11}$/
                break;
            case '886':
                preg = /^\d{10}$/
                break;
            case '853':
                preg = /^\d{8}$/
                break;
            case '55':
                preg = /^\d{8}$/
                break;
            case '49':
                preg = /^\d{7}$/
                break;
            case '7':
                preg = /^\d{10}$/
                break;
            case '33':
                preg = /^\d{9}$/
                break;
            case '63':
                preg = /^(\d{7}|\d{9})$/
                break;
            case '82':
                preg = /^\d{11}$/
                break;
            case '1':
                preg = /^\d{10}$/
                break;
            case '855':
                preg = /^\d{6}$/
                break;
            case '856':
                preg = /^\d{8}$/
                break;
            case '60':
                preg = /^\d{4,18}$/
                break;
            case '95':
                preg = /^(\d{7}|\d{8}|\d{9})$/
                break;
            case '81':
                preg = /^\d{11}$/
                break;
            case '66':
                preg = /^\d{10}$/
                break;
            case '673':
                preg = /^\d{11}$/
                break;
            case '65':
                preg = /^\d{8}$/
                break;
            case '91':
                preg = /^\d{11}$/
                break;
            case '62':
                preg = /^(\d{9}|\d{11})$/
                break;
            case '44':
                preg = /^\d{11}$/
                break;
            case '84':
                preg = /^\d{11}$/
                break;
            default:
                preg = /^\d{4,20}$/
        }
        return preg
    }

    //奖励100的弹层消失
    function hideSendLayer() {
        $('.send_layer').hide();
        layer.confirm('是否前往下载[葡萄浏览器APP]？', {
            btn: ['是', '否'] //按钮
        }, function () {
            window.location.href = download;
        }, function () {
            layer.msg('感谢您的注册使用!');
        });
    }

    function hideTcLayer() {
        $(".tc_layer").hide()
    }

    function sendCode(obj) {
        if ($(obj).hasClass("on")) {
            return false;
        }

        var mobile = $("#mobile").val();
        var area_code = $('#area_code').val();

        //手机号验证
        if (mobile == '') {
            layer.msg("手机号码不能为空！");
            $("#mobile").focus();
            return false;
        }
        //区域码验证
        if (area_code == '') {
            layer.msg("手机前缀号码不能为空！");
            $("#area_code").focus();
            return false;
        }

        var mobile_preg = getMobilePreg(area_code)

        // if (!mobile_preg.test(mobile)) {
        //     layer.msg("请输入正确的手机号！");
        //     $("#mobile").focus();
        //     return false;
        // }
        var self = $(obj);
        self.html("正在发送...").addClass('on');
        $.ajax({
            url: "web_send_sms",
            dataType: "json",
            async: true,
            data: {'phone': mobile, 'area_code': area_code},
            type: "POST",
            timeout: 30000,
            success: function (data) {
                if (data.code == 200) {
                    layer.msg(data.data, {
                        time: 2000 //2秒关闭（如果不配置，默认是3秒）
                    }, function () {
                        var html = "等待(<i>360秒</i>)";
                        self.html(html);
                        regCodeInterval = setInterval(function () {
                            regCodeTimeinterval();
                        }, 1000);
                    });
                } else if (data.code == 4004) {
                    layer.msg(data.msg);
                    self.html("获取验证码").removeClass("on");
                    return false;
                } else if (data.code == 4005) {
                    layer.msg(data.msg);
                    self.html("获取验证码").removeClass("on");
                    return false;
                } else if (data.code == 400) {
                    layer.msg(data.message);
                    self.html("获取验证码").removeClass("on");
                    return false;
                } else if (data.code == 3002) {
                    layer.msg(data.msg);
                    self.html("获取验证码").removeClass("on");
                    return false;
                } else {
                    layer.msg("获取失败");
                    self.html("获取验证码").removeClass("on");
                    return false;
                }
            }
        });
    }

    function submitForm() {
        if (hasSubmit == 1) {
            return false;
        }

        //手机号验证
        var mobile = $("#mobile").val();
        if (mobile == '') {
            layer.msg("手机号码不能为空！");
            $("#mobile").focus();
            return false;
        }

        var area_code = $('#area_code').val();
        var mobile_preg = getMobilePreg(area_code)
        // if (!mobile_preg.test(mobile)) {
        //     layer.msg("请输入正确的手机号！");
        //     $("#mobile").focus();
        //     return false;
        // }
        //验证验证码不能空
        if ($("#sms_code").val() == "") {
            layer.msg("验证码不能为空！");
            $("#sms_code").focus();
            return false;
        }


        //密码验证
        password = $("#password").val();
        confirm_password = $("#confirm_password").val();
        if (password == "") {
            layer.msg("密码不能为空！");
            $("#password").focus();
            return false;
        }
        if (!preg.password.test(password)) {
            layer.msg("密码格式不正确,6-12位的数字,字母！");
            $("#password").focus();
            return false;
        }

        if (password != confirm_password) {
            layer.msg("两次密码输入不一致!");
            $("#confirm_password").focus();
            return false;
        }

        //同意协议
        if ($('#agreement')[0].checked == false) {
            layer.msg("请同意用户协议");
            $("#agreement").focus();
            return false;
        }

        var param = {
            'phone': $("#mobile").val(),
            'sms_code': $("#sms_code").val(),
            'password': $("#password").val(),
            'confirm_password': $("#confirm_password").val(),
            'parent_id': $("#parent_id").val()
        };

        var load_obj = layer.load();
        //表单提交
        $.ajax({
            url: "xin_do_register",
            type: 'post',
            data: param,
            async: true,
            dataType: 'json',
            beforeSend: function () {
                hasSubmit = 1
            },
            success: function (data) {
                if (data.code == 200) {
                    if (data.parent_id > 0) {
                        $(".send_layer").show();
                    } else {
                        layer.confirm('是否前往下载[葡萄浏览器APP]？', {
                            btn: ['是', '否'] //按钮
                        }, function () {
                            window.location.href = download;
                        }, function () {
                            layer.msg('感谢您的注册使用!');
                        });
                    }
                } else {
                    layer.msg(data.msg);
                }
            },
            complete: function () {
                layer.close(load_obj)
                hasSubmit = 0
            }
        });
    }


    function regCodeTimeinterval() {
        var currTime = $('#send_register_code i').html();
        var newTime = parseInt(currTime) - 1;
        if (newTime) {
            $('#send_register_code i').html(newTime);
        } else {
            clearInterval(regCodeInterval);
            $('#send_register_code').html('重新发送').removeClass('on');
        }
    }


</script>
</html>
