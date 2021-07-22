<?php

namespace App\Services\EleAdmin;

use App\Services\Service;
use App\Models\EleAdmin\Admin as AdminModel;
use Illuminate\Support\Facades\Hash;

class LoginService extends Service
{
    /**
     * 登录验证
     * @param $params
     * @return array
     */
    public static function login($params)
    {
        $columns = ['id', 'admin_name', 'admin_password', 'real_name', 'remark', 'phone', 'status'];

        $admin = AdminModel::select($columns)->ofConditions($params)->first();

        if (empty($admin) || !Hash::check($params['admin_password'], $admin->admin_password)) {
            return ['code' => 1000, 'message' => '账号或密码错误，请重新输入。'];
        }
        if ($admin->status == AdminModel::ACCOUNT_STATUS_DISABLE) {
            return ['code' => 1000, 'message' => '账号已被封禁，请联系管理员！'];
        }

        $admin->logined_at = time();
        $admin->save();

        unset($admin->admin_password);
        unset($admin->status);

        return  ['code' => 200, 'message' => '验证通过', 'user' => $admin];
    }
}