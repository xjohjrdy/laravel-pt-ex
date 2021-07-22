<?php

namespace App\Http\Requests\EleAdmin;

use App\Http\Requests\BaseRequest;

class StoreRolePost extends BaseRequest
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
            'role_name' => 'required|max:50',
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
            'role_name.required' => '角色名称不能为空',
            'role_name.max' => '角色名称长度异常',
            'status.required'  => '状态异常',
        ];
    }
}