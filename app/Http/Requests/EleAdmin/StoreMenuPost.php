<?php

namespace App\Http\Requests\EleAdmin;

use App\Http\Requests\BaseRequest;

class StoreMenuPost extends BaseRequest
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
            'title' => 'required|max:100',
            'path' => 'required|max:255',
            'status' => 'required',
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
            'title.required' => '菜单名称不能为空',
            'title.max' => '菜单名称长度异常',
            'path.required' => '菜单路由不能为空',
            'path.max' => '菜单路由长度异常',
            'status.required'  => '状态异常',
        ];
    }
}