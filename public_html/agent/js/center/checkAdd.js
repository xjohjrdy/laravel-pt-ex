layui.config({
    base: '/agent/' //静态资源所在路径
}).extend({
     citypicker: '{/}/agent/layui/layui_exts/city-picker/city-picker' // {/}的意思即代表采用自有路径，即不跟随 base 路径
}).use(['jquery',  'table', 'form', 'citypicker', 'layer'], function () {
    var form = layui.form;
    layer = parent.layer === undefined ? layui.layer : top.layer, table = layui.table,
        $ = layui.$
        , cityPicker = layui.citypicker;

    var currentPicker = new cityPicker("#city-picker", {
        provincename: "provinceId",
        cityname: "cityId",
        districtname: "districtId",
        level: 'cityId',// 级别
    });

    form.verify({
        param_parameter: [
            /^[^\s:]+:[^\s:]+$/
            , '商品参数格式不正确，请检查！'
        ],
        param_custom: [
            /^[^\s:]+:[^\s:,，]+(,[^\s:,，]+)*$/
            , '自定义参数格式不正确，请检查！'
        ],
        postage: function (value, item) { //value：表单的值、item：表单的DOM对象
            if (value <= 0) {
                return '如果不发货请填写0.01';
            }
        }
    });


    form.on("submit(addUser)", function (data) {

        //弹出loading
        var index = layui.layer.msg('数据提交中，请勿重复刷新！！', {icon: 16, time: false, shade: 0.8});
        return true;
    });


    $('input[name="price"]').keyup(function (event) {

        var price = parseFloat($(this).val());
        var vip_price = price * 0.965;
        $('input[name="vip_price"]').val(vip_price.toFixed(2));

        return true;
    });


    // $('input[name="price"]').keyup(function (event) {
    //
    //     console.log($(this).val());
    //     return true;
    // });


    $(document).on('click', '.subtract-item', function () {
        $(this).parents('.input-item').remove();
        console.log("被点击了。");
        return false;
    });

    // $(document).on('click', '.add-item-001', function () {
    //     $('#info-list-001').append($(this).parents('.input-item').clone());
    //     console.log("被点击了。");
    //     return false;
    // });

    $(document).on('click', '.add-item-002', function () {
        $('#info-list-002').append($(this).parents('.input-item').clone());
        console.log("被点击了。");
        return false;
    });

    $(document).on('click', '.add-item-003', function () {
        var id = $('#header-img').val();
        $('#header-img').val(parseInt(id) + 1);
        console.log(id);
        var clone_node = $(this).parents('.input-item').clone();
        clone_node.find('.upload-file').attr('name', 'header_img_' + id);
        $('#info-list-003').append(clone_node);
        return false;
    });

    $(document).on('click', '.add-item-004', function () {
        var id = $('#detail_img').val();
        $('#detail_img').val(parseInt(id) + 1);
        console.log(id);
        var clone_node = $(this).parents('.input-item').clone();
        clone_node.find('.upload-file').attr('name', 'detail_img_' + id);
        $('#info-list-004').append(clone_node);
        return false;
    });

    $(document).on('click', '.add-item-005', function () {
        var id = $('#share_img').val();
        $('#share_img').val(parseInt(id) + 1);
        console.log(id);
        var clone_node = $(this).parents('.input-item').clone();
        clone_node.find('.upload-file').attr('name', 'share_img_' + id);
        $('#info-list-005').append(clone_node);
        return false;
    });

    $(document).on('click', '.add-item-006', function () {
        var id = $('#video_url').val();
        $('#video_url').val(parseInt(id) + 1);
        console.log(id);
        var clone_node = $(this).parents('.input-item').clone();
        clone_node.find('.upload-file').attr('name', 'video_url_' + id);
        $('#info-list-006').append(clone_node);
        return false;
    });

});