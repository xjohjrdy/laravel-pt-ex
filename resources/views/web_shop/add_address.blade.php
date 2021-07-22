<!DOCTYPE html>
<html>
<head>
    <title>添加常用联系人</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="telephone=no" name="format-detection">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <!--库-->
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.min.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/jquery.Spinner.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/amazeui.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/honeySwitch.js"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/iscroll.min.js"  charset="utf-8"></script>
    <script type="text/javascript" src="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/js/address.js"  charset="utf-8"></script>

    <!--核心样式-->
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/amazeui.min.css" />
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/amazeui.address.css" />
    <link rel="stylesheet" type="text/css" href="http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/css/style.css">
    <script type="text/javascript">
            //提交表单
            function submitForm() {
                console.log(123);
                var collection = $("input[name='collection']").val();
                var phone = $("input[name='phone']").val();
                var zone = $("input[name='zone']").val();
                var detail = $("textarea[name='detail']").val();

                console.log(collection);
                console.log(phone);
                console.log(zone);
                console.log(detail);

                if(!/^[\u4e00-\u9fa5]+$/.test(collection)){
                    alert('收货人姓名只能为中文');
                    return false;
                }

                if(!/^1\d{10}$/.test(phone)){
                    alert('手机号格式错误');
                    return false;
                }

                if(!/^[\u4e00-\u9fa5]+$/.test(zone)){
                    alert('地区输入不能为空');
                    return false;
                }

                if(!/^.{8,}$/.test(detail)){
                    alert('详细地址内容过少');
                    return false;
                }

                var param = {
                    'collection': collection,
                    'phone': phone,
                    'zone': zone,
                    'detail': detail,
                };

                $.ajax({
                    url: "/add_address/{{$good_id}}/{{$invite_app_id}}/{{$order_id}}",
                    type: 'post',
                    data: param,
                    async: true,
                    dataType: 'json',
                    success: function (data) {
                        if (data.code == 200) {
                            //添加成功后跳转到某地
                            window.location.href = "/get_order_info/{{$good_id}}/{{$invite_app_id}}/{{$order_id}}";
                        } else {
                            //如果失败后弹出的提示框
                            alert('添加地址失败');
                        }
                    },
                });
            }

    </script>
	</head>
<body>
<!-----------公共版头----------->
<div id="header">
    <a class="return" href="sure_order.html"><i class="am-icon-angle-left"></i></a>
    <span>新增收货地址</span>
</div>
<!-----------常用联系人----------->
<div id="set_data">
    <div class="fill_word">
        <ul>
            <li>
                <p>收货人</p>
                <div class="am-form-group">
                    <input type="text/css" placeholder="请输入收货人姓名" name="collection" />
                </div>
                <div class="clear"></div>
            </li>
            <li>
                <p>联系电话</p>
                <div class="am-form-group">
                    <input type="text/css" placeholder="请输入联系电话" name="phone"/>
                </div>
                <div class="clear"></div>
            </li>
            <li>
                <p>所在地区</p>
                <div class="am-form-group" data-am-address="{prov:'上海',scrollToCenter:true}">
                    <input readonly type="text/css"  placeholder="请选择地址" required="" value="" name="zone"/><span style="position: absolute; right: 10px;">请选择 <i class="am-icon-chevron-right"></i></span>
                </div>                
                <div class="clear"></div>
            </li>
            <li style="height: 76px;">
                <p>详细地址</p>
                <div class="am-form-group">
                    <textarea style="border:none; margin-left: 14px; margin-top: 14px; width: 78%; line-height: 20px;height: auto;" placeholder="请输入详细地址信息，如道路、门牌号、小区、楼栋号、单元室等" name="detail"></textarea>
                </div>
                <div class="clear"></div>
            </li>
            <li>
                <p>设置默认地址</p>
                <div class="am-form-group">
                    
                    <div class="common-row">
						<div class="cell-left"></div>
						<div class="cell-right" style="float: right;"><span class="switch-on" id="wifi"></span></div>
					</div>
                </div>                
                <div class="clear"></div>
            </li>
        </ul>
        <button class="yes" value="" style="background: #dc141f; width: 90%; margin-left: 5%; border-radius: 50px;" onclick="submitForm()" >保 存</button>
    </div>
</div>
</body>
</html>