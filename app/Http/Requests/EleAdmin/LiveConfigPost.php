<?php

namespace App\Http\Requests\EleAdmin;

use App\Http\Requests\BaseRequest;

class LiveConfigPost extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'user_name' => 'required',
            'head_image' => 'required',
            'image_url' => 'required',
            'back_url' => 'required',
            'live_url' => 'required',
            'desc' => 'required',
            'plan_time' => 'required',
            'good_ids' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息。
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => '直播标题不能为空',
            'user_name.required' => '主播名不能为空',
            'head_image.required' => '主播头像不能为空',
            'image_url.required' => '封面图不能为空',
            'back_url.required' => '预告封面图不能为空',
            'live_url.required' => '背景图不能为空',
            'desc.required' => '介绍不能为空',
            'plan_time.required' => '开播时间不能为空',
            'good_ids.required' => '关联商品不能为空',
        ];
    }
}