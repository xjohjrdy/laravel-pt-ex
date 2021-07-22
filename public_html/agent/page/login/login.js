layui.use(['form', 'layer', 'jquery'], function () {
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer
    $ = layui.jquery;


    //登录按钮
    form.on("submit(login)", function (data) {
        $(this).text("登录中...").attr("disabled", "disabled").addClass("layui-disabled");
        var phone = $('#userPhone').val();
        var password = $('#password').val();
        var code = $('#code').val();
        var post_data = {'phone': phone, 'password': password, 'code': code};

        $.ajax({
            url: "/agent_login",
            dataType: "json",
            async: true,
            data: post_data,
            type: "POST",
            timeout: 30000,
            success: function (data) {
                if (data.code == 200) {
                    layer.msg(data.data, {
                        time: 2000 //2秒关闭（如果不配置，默认是3秒）
                    }, function () {
                        window.location.href = "/agent_admin";
                    });

                } else {
                    layer.msg(data.msg);
                    return false;
                }
            }
        });

        return false;
    })

    //表单输入效果
    $(".loginBody .input-item").click(function (e) {
        e.stopPropagation();
        $(this).addClass("layui-input-focus").find(".layui-input").focus();
    })
    $(".loginBody .layui-form-item .layui-input").focus(function () {
        $(this).parent().addClass("layui-input-focus");
    })
    $(".loginBody .layui-form-item .layui-input").blur(function () {
        $(this).parent().removeClass("layui-input-focus");
        if ($(this).val() != '') {
            $(this).parent().addClass("layui-input-active");
        } else {
            $(this).parent().removeClass("layui-input-active");
        }
    })

    //获取验证码
    $("#getVerifications").click(function () {
        var self = $(this);

        if (self.hasClass("on")) {
            layer.msg('已经发送过，六分钟内有效，请勿频繁点击。');
            return false;
        }
        var mobile = $('#userPhone').val();
        if (!/\d{11}/.test(mobile)) {
            layer.msg("请输入正确的手机号！");
            $('#userPhone').focus();
            return false;
        }

        self.html('正在发送...').addClass('on');

        $.ajax({
            url: "/agent_send_sms",
            dataType: "json",
            async: true,
            data: {'phone': mobile},
            type: "POST",
            timeout: 30000,
            success: function (data) {
                if (data.code == 200) {
                    layer.msg(data.data, {
                        time: 2000 //2秒关闭（如果不配置，默认是3秒）
                    }, function () {
                        var html = "(倒计时<i>360</i>)";
                        self.html(html);
                        regCodeInterval = setInterval(function () {
                            regCodeTimeinterval();
                        }, 1000);
                    });
                } else if (data.code == 2001) {
                    layer.msg(data.msg);
                    self.html("重新发送").removeClass("on");
                    return false;
                }
            }
        });
    })

    function regCodeTimeinterval() {
        var currTime = $('#getVerifications i').html();
        var newTime = parseInt(currTime) - 1;
        if (newTime) {
            $('#getVerifications i').html(newTime);
        } else {
            clearInterval(regCodeInterval);
            $('#getVerifications').html('重新发送').removeClass('on');
        }
    }
})
