layui.use(['form', 'layer', 'laydate', 'table', 'laytpl'], function () {
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery,
        laydate = layui.laydate,
        laytpl = layui.laytpl,
        table = layui.table;

    //选取时间插件
    layui.use('laydate', function () {
        var laydate = layui.laydate;
        //日期时间选择器
        laydate.render({
            elem: '#test-begin'
            , type: 'datetime'
        });
        //日期时间选择器
        laydate.render({
            elem: '#test-end'
            , type: 'datetime'
        });
    });

    //导出单击事件
    $("#excel-data").on("click", function () {
        excel_data_goods();
    });

    //导出执行的函数
    function excel_data_goods() {
        var begin_time = $("#test-begin").val();
        var end_time = $("#test-end").val();
        var status = $("#status").val();

        var url = "/agent_export_excel_goods";
        location.href = url+"?status=" + status + "&begin_time=" + begin_time + "&end_time=" + end_time;
    }
});