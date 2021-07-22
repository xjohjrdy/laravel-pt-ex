<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>商品管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="{{ URL::asset('agent/layui/css/layui.css') }}" media="all"/>
    <link rel="stylesheet" href="{{ URL::asset('agent/css/public.css') }}" media="all"/>
    <script src="{{ URL::asset('agent/layui/layui_exts/city-picker/city-picker.data.js') }}"></script>
    <link href="{{ URL::asset('agent/layui/layui_exts/city-picker/city-picker.css') }}" rel="stylesheet"/>
</head>
<body class="childrenBody">
<form class="layui-form" style="width:80%;" id="add-check-goods" action="/agent_center_check_add_html" method="post"
      enctype="multipart/form-data"
      lay-filter="example" autocomplete="off">
    @isset($data->id)
        <input id="header-img" type="hidden" name="id" value="{!! $data->id !!}">
    @endisset()
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">商品名称</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required" name="title" placeholder="请输入商品名称"
                   value="@isset($data->title){!! $data->title !!}@endisset">
        </div>
    </div>
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">平台售价</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required" name="price" placeholder="请输入商品价格"
                   value="@isset($data->price){!! $data->price !!}@endisset">
        </div>
    </div>
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">VIP价</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required" name="vip_price" placeholder="请输入VIP价"
                   value="@isset($data->vip_price){!! $data->vip_price !!}@endisset" readonly="readonly">
        </div>
    </div>
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">供货价</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required" name="tao_jd_price" placeholder="请输入供货价"
                   value="@isset($data->tao_jd_price){!! $data->tao_jd_price !!}@endisset">
        </div>
    </div>
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">预估重量</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required|number" name="real_weight"
                   placeholder="该物品的库存量（单位g）"
                   value="@isset($data->real_weight){!! $data->real_weight !!}@endisset">
        </div>
    </div>

    @if(!isset($data->zone))
        <div class="layui-form-item layui-row layui-col-xs12">
            <label class="layui-form-label">所在地区</label>
            <div class="layui-input-block">
                <input type="text" autocomplete="on" class="layui-input" lay-verify="required" id="city-picker"
                       name="zone" readonly="readonly" data-toggle="city-picker" placeholder="请选择">
            </div>
        </div>
    @endif

    {{--<div class="layui-inline">--}}
    {{--<label class="layui-form-label width_auto text-r" style="margin-top:2px">省市县：</label>--}}
    {{--<div class="layui-input-inline" style="width:400px">--}}
    {{----}}
    {{--</div>--}}
    {{--</div>--}}

    {{--<div class="layui-form-item layui-row layui-col-xs12">--}}
    {{--<label class="layui-form-label">所在地区</label>--}}
    {{--<div class="layui-input-block">--}}
    {{--<input type="text" class="layui-input" lay-verify="required" name="zone" placeholder="请输入所在地区"--}}
    {{--value="@isset($data->zone){!! $data->zone !!}@endisset">--}}
    {{--</div>--}}
    {{--</div>--}}
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">商品原价</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required" name="cost_price" placeholder="该物品的原价"
                   value="@isset($data->cost_price){!! $data->cost_price !!}@endisset">
        </div>
    </div>
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">库存</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required|number" name="volume" placeholder="该物品的库存量"
                   value="@isset($data->volume){!! $data->volume !!}@endisset">
        </div>
    </div>
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">偏远地区邮费</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" lay-verify="required|number|postage" name="express"
                   placeholder="偏远地区快递费用(如果不发货必须大于0.01)"
                   value="@isset($data->express){!! $data->express !!}@endisset">
        </div>
    </div>

    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">偏远地区是否发货</label>
        <div class="layui-input-block isPush" lay-filter="test2">
            <input type="radio" name="is_push" value="1" title="发货"
                   @if(!isset($data->is_push))checked @elseif($data->is_push == 1)checked @endif>
            <input type="radio" name="is_push" value="0" title="不发货"
                   @if(isset($data->is_push)&&$data->is_push == 0)checked @endif>
            {{--<input type="radio" name="is_push" value="1" title="发货" checked="" lay-ignore>发货--}}
            {{--<input type="radio" name="is_push" value="0" title="不发货" lay-ignore>不发货--}}
        </div>
    </div>
    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">偏远地区</label>
        <div class="layui-input-block">
            <input type="checkbox" name="area[]" title="陕西" value="陕西"
                   @if(!isset($data->area))checked @elseif(in_array('陕西',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="甘肃" value="甘肃"
                   @if(!isset($data->area))checked @elseif(in_array('甘肃',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="青海" value="青海"
                   @if(!isset($data->area))checked @elseif(in_array('青海',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="宁夏" value="宁夏"
                   @if(!isset($data->area))checked @elseif(in_array('宁夏',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="新疆" value="新疆"
                   @if(!isset($data->area))checked @elseif(in_array('新疆',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="西藏" value="西藏"
                   @if(!isset($data->area))checked @elseif(in_array('西藏',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="海南" value="海南"
                   @if(!isset($data->area))checked @elseif(in_array('海南',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="吉林" value="吉林"
                   @if(!isset($data->area))checked @elseif(in_array('吉林',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="辽宁" value="辽宁"
                   @if(!isset($data->area))checked @elseif(in_array('辽宁',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="黑龙江" value="黑龙江"
                   @if(!isset($data->area))checked @elseif(in_array('黑龙江',$data->area))checked @endif>
            <input type="checkbox" name="area[]" title="内蒙古" value="内蒙古"
                   @if(!isset($data->area))checked @elseif(in_array('内蒙古',$data->area))checked @endif>
        </div>
    </div>

    {{--<div class="layui-form-item layui-row layui-col-xs12">--}}
        {{--<label class="layui-form-label">商品参数</label>--}}

        {{--<div class="layui-input-block" id="info-list-001">--}}

            {{--@isset($data->parameter)--}}
                {{--@foreach ($data->parameter as $item)--}}

                    {{--<div class="input-item">--}}
                        {{--<div class="layui-col-md9">--}}
                            {{--<input type="text" class="layui-input" lay-verify="param_parameter" name="parameter[]"--}}
                                   {{--placeholder="格式为 参数名:参数值" value="{!! $item !!}">--}}
                        {{--</div>--}}

                        {{--<div class="layui-col-md3">--}}
                            {{--<div class="layui-btn-group">--}}
                                {{--<button class="layui-btn add-item-001" lay-filter="test">增加一行</button>--}}
                                {{--<button class="layui-btn subtract-item">删除该行</button>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--@endforeach--}}
            {{--@else--}}
                {{--<div class="input-item">--}}
                    {{--<div class="layui-col-md9">--}}
                        {{--<input type="text" class="layui-input" lay-verify="param_parameter" name="parameter[]"--}}
                               {{--placeholder="格式为 参数名:参数值">--}}
                    {{--</div>--}}

                    {{--<div class="layui-col-md3">--}}
                        {{--<div class="layui-btn-group">--}}
                            {{--<button class="layui-btn add-item-001" lay-filter="test">增加一行</button>--}}
                            {{--<button class="layui-btn subtract-item">删除该行</button>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--@endisset--}}


        {{--</div>--}}
    {{--</div>--}}

    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">商品自定义参数</label>
        <div class="layui-input-block" id="info-list-002">

            @isset($data->custom)
                @foreach ($data->custom as $item)
                    <div class="input-item">
                        <div class="layui-col-md9">
                            <input type="text" class="layui-input" lay-verify="param_custom" name="custom[]"
                                   placeholder="格式为 参数名:参数值1,参数值2,参数值3..." value="{!! $item !!}">
                        </div>

                        <div class="layui-col-md3">
                            <div class="layui-btn-group">
                                <button class="layui-btn add-item-002">增加一行</button>
                                <button class="layui-btn subtract-item">删除该行</button>
                            </div>
                        </div>
                    </div>

                @endforeach
            @else
                <div class="input-item">
                    <div class="layui-col-md9">
                        <input type="text" class="layui-input" lay-verify="param_custom" name="custom[]"
                               placeholder="格式为 参数名:参数值1,参数值2,参数值3...">
                    </div>

                    <div class="layui-col-md3">
                        <div class="layui-btn-group">
                            <button class="layui-btn add-item-002">增加一行</button>
                            <button class="layui-btn subtract-item">删除该行</button>
                        </div>
                    </div>
                </div>
            @endisset


        </div>
    </div>

    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">头图集合<br/>(最多五张)</label>

        <input id="header-img" type="hidden" value="2">
        <div class="layui-input-block" id="info-list-003">

            <div class="input-item">
                <div class="layui-col-md6 layui-col-md-offset1">
                    <div class="layui-upload">
                        {{--<button type="button" class="layui-btn layui-btn-normal upload-file"><i class="layui-icon"></i>选择图片文件--}}
                        {{--</button>--}}
                        <input type="file" class="layui-btn layui-btn-normal upload-file" name="header_img_1"
                               value="选择图片文件"/>
                    </div>
                </div>

                <div class="layui-col-md3 layui-col-md-offset2">
                    <div class="layui-btn-group">
                        <button class="layui-btn add-item-003">增加一行</button>
                        <button class="layui-btn subtract-item">删除该行</button>
                    </div>
                </div>
            </div>

        </div>


    </div>


    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">图片详情<br/>(最多十张)</label>
        <input id="detail_img" type="hidden" value="2">
        <div class="layui-input-block" id="info-list-004">

            <div class="input-item">
                <div class="layui-col-md6 layui-col-md-offset1">
                    <div class="layui-upload">
                        {{--<button type="button" class="layui-btn layui-btn-normal upload-file"><i class="layui-icon"></i>选择图片文件--}}
                        {{--</button>--}}
                        <input type="file" class="layui-btn layui-btn-normal upload-file" name="detail_img_1"
                               value="选择图片文件"/>
                    </div>
                </div>

                <div class="layui-col-md3 layui-col-md-offset2">
                    <div class="layui-btn-group">
                        <button class="layui-btn add-item-004">增加一行</button>
                        <button class="layui-btn subtract-item">删除该行</button>
                    </div>
                </div>
            </div>


        </div>
    </div>


    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">分享图片<br/>(最好九张)</label>
        <input id="share_img" type="hidden" value="2">
        <div class="layui-input-block" id="info-list-005">


            <div class="input-item">
                <div class="layui-col-md6 layui-col-md-offset1">
                    <div class="layui-upload">
                        {{--<button type="button" class="layui-btn layui-btn-normal upload-file"><i class="layui-icon"></i>选择图片文件--}}
                        {{--</button>--}}
                        <input type="file" class="layui-btn layui-btn-normal upload-file" name="share_img_1"
                               value="选择图片文件"/>
                    </div>
                </div>

                <div class="layui-col-md3 layui-col-md-offset2">
                    <div class="layui-btn-group">
                        <button class="layui-btn add-item-005">增加一行</button>
                        <button class="layui-btn subtract-item">删除该行</button>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">视频分享</label>
        <input id="video_url" type="hidden" value="2">
        <div class="layui-input-block" id="info-list-006">

            <div class="input-item">
                <div class="layui-col-md6 layui-col-md-offset1">
                    <div class="layui-upload">
                        {{--<button type="button" class="layui-btn layui-btn-normal upload-file"><i class="layui-icon"></i>选择视频文件--}}
                        {{--</button>--}}
                        <input type="file" class="layui-btn layui-btn-normal upload-file" name="video_url_1"
                               value="选择图片文件"/>
                    </div>
                </div>

                <div class="layui-col-md3 layui-col-md-offset2">
                    <div class="layui-btn-group">
                        <button class="layui-btn add-item-006">增加一行</button>
                        <button class="layui-btn subtract-item">删除该行</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">上传横图</label>
        <div class="layui-input-block">
            <div class="layui-upload layui-col-md-offset1">
                {{--<button type="button" class="layui-btn layui-btn-normal upload-file"><i class="layui-icon"></i>选择图片文件--}}
                {{--</button>--}}
                <input type="file" class="layui-btn layui-btn-normal upload-file" name="sidle_img" value="选择图片文件"/>
            </div>

        </div>
    </div>


    <div class="layui-form-item layui-row layui-col-xs12">
        <label class="layui-form-label">详情文字</label>
        <div class="layui-input-block">
            <textarea name="detail_desc" placeholder="请输入对该产品的文字描述"
                      class="layui-textarea">@isset($data->detail_desc){!! $data->detail_desc !!}@endisset</textarea>
        </div>
    </div>
    <div class="layui-form-item layui-row layui-col-xs12">
        <div class="layui-input-block">
            {{--<button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="addUser" id="upLoad_t">立即添加</button>--}}
            <input type="submit" class="layui-btn layui-btn-sm" lay-submit="detail_desc" lay-filter="addUser"
                   value="立即添加"/>
            <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
        </div>
    </div>
</form>
<script type="text/javascript" src="{{ URL::asset('agent/layui/layui.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('agent/js/center/checkAdd.js') }}"></script>
</body>
</html>