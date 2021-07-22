<?php

namespace App\Http\Requests\EleAdmin;

use App\Http\Requests\BaseRequest;

class StoreAdminPost extends BaseRequest
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
            'admin_name' => 'required|max:50',
            'admin_password' => 'required|max:255',
            'real_name' => '',
            'phone' => '',
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
            'admin_name.required' => '账号不能为空',
            'admin_name.max' => '账号长度异常',
            'admin_password.required' => '密码不能为空',
            'admin_password.max' => '密码长度异常',
            'status.required'  => '状态异常',
        ];
    }
}