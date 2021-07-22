layui.use(['form', 'layer', 'laydate', 'table', 'laytpl'], function () {
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery,
        laydate = layui.laydate,
        laytpl = layui.laytpl,
        table = layui.table;


    //商品列表
    var tableIns = table.render({
        elem: '#newsList',
        url: 'agent_json_check_goods',
        response: {
            statusCode: 200
        },
        cellMinWidth: 95,
        page: true,
        height: "full-125",
        limit: 10,
        limits: [10, 15, 20, 25],
        id: "newsListTable",
        cols: [[
            {type: "checkbox", fixed: "left", width: 50},
            {field: 'id', title: 'ID', width: 60, align: "center"},
            {field: 'title', title: '商品名', width: 260, align: "center"},
            {field: 'price', title: '价格', align: 'center'},
            {field: 'zone', title: '区域', align: 'center'},
            {field: 'sale_volume', title: '销量', align: 'center'},
            {field: 'volume', title: '库存', align: 'center'},
            {field: 'click_number', title: '点击数', align: 'center'},
            {field: 'review_status', title: '状态', align: 'center', templet: "#newsStatus"},
            {
                field: 'created_at', title: '发布时间', align: 'center', minWidth: 110, templet: function (d) {
                    return d.created_at.substring(0, 10);
                }
            },
            {
                field: 'header_img', title: '预览图', align: 'center', minWidth: 110, templet: function (d) {
                    var obj_json = JSON.parse(d.header_img);
                    return '<img style="height: 50px" layer-src="' + obj_json['0'] + '" src="' + obj_json['0'] + '/img_h_50" alt="图片名">';
                }
            },
            {title: '操作', width: 120, templet: '#newsListBar', fixed: "right", align: "center"}
        ]]
    });


    //是否置顶
    form.on('switch(newsTop)', function (data) {
        var index = layer.msg('修改中，请稍候', {icon: 16, time: false, shade: 0.8});
        setTimeout(function () {
            layer.close(index);
            if (data.elem.checked) {
                layer.msg("置顶成功！");
            } else {
                layer.msg("取消置顶成功！");
            }
        }, 500);
    })

    //搜索【此功能需要后台配合，所以暂时没有动态效果演示】
    $(".search_btn").on("click", function () {
        if ($(".searchVal").val() != '') {
            table.reload("newsListTable", {
                page: {
                    curr: 1 //重新从第 1 页开始
                },
                where: {
                    key: $(".searchVal").val()  //搜索的关键字
                }
            })
        } else {
            layer.msg("请输入搜索的内容");
        }
    });

    //添加商品
    function addNews(edit) {
        var url = "/agent_center_check_add_html";
        if (edit) {
            url = "/agent_center_check_ed_html?ed_id=" + edit.id;
        }
        var index = layui.layer.open({
            title: "商品管理",
            type: 2,
            content: url,
            success: function (layero, index) {
                var body = layui.layer.getChildFrame('body', index);
                console.log('编辑商品')

                setTimeout(function () {

                    layui.layer.tips('点击此处返回商品列表', '.layui-layer-setwin .layui-layer-close', {
                        tips: 3
                    });
                    console.log(222);
                }, 800)
            }
        })
        layui.layer.full(index);
        //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
        $(window).on("resize", function () {
            layui.layer.full(index);
        })
    }

    $(".addNews_btn").click(function () {
        addNews();
    })

    //批量删除
    $(".delAll_btn").click(function () {
        var checkStatus = table.checkStatus('newsListTable'),
            data = checkStatus.data,
            id = [];
        if (data.length > 0) {
            for (var i in data) {
                id.push(data[i].id);
            }
            layer.confirm('确定删除选中的商品？', {icon: 3, title: '提示信息'}, function (index) {
                console.log(id);
                $.get("/agent_center_check_de_html", {
                    de_id: id  //将需要删除的newsId作为参数传入
                }, function (data) {
                    tableIns.reload();
                    layer.close(index);
                })
            })
        } else {
            layer.msg("请选择需要删除的商品");
        }
    })

    //列表操作
    table.on('tool(newsList)', function (obj) {
        var layEvent = obj.event,
            data = obj.data;

        if (layEvent === 'edit') { //编辑
            addNews(data);
        } else if (layEvent === 'del') { //删除
            layer.confirm('确定删除此商品？', {icon: 3, title: '提示信息'}, function (index) {
                console.log(data.id);
                $.get("/agent_center_check_de_html", {
                    de_id: data.id  //将需要删除的newsId作为参数传入
                }, function (data) {
                    tableIns.reload();
                    layer.close(index);
                })
            });
        } else if (layEvent === 'look') { //预览
            layer.alert("此功能需要前台展示，实际开发中传入对应的必要参数进行商品内容页面访问")
        }
    });

});