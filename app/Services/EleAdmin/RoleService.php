<?php

namespace App\Services\EleAdmin;

use App\Services\Service;
use App\Models\EleAdmin\Admin as AdminModel;
use App\Models\EleAdmin\Role as RoleModel;
use App\Models\EleAdmin\AdminRole as AdminRoleModel;
use App\Tools\ObjectDataHandle;
use Illuminate\Support\Facades\Hash;

class RoleService extends Service
{
    /**
     * 新增
     * @param $params
     * @return array
     */
    public static function add($params, $opId = 0)
    {
        $roleName = $params['role_name'] ?? null;
        $desc = $params['desc'] ?? '';
        $status = $params['status'] ?? null;

        if (!$roleName) {
            return ['code' => 1000, 'message' => '角色名称不能为空'];
        }
        if (!$status || !in_array($status, array_keys(AdminModel::$statusList))) {
            return ['code' => 1000, 'message' => '状态异常'];
        }
        if (self::isExisted($roleName)) {
            return ['code' => 1000, 'message' => '角色名称已存在'];
        }

        $time = time();
        $save['role_name'] = $roleName;
        $save['desc'] = $desc;
        $save['status'] = $status;
        $save['op_id'] = $opId;
        $save['created_at'] = $time;
        $save['updated_at'] = $time;

        $id = RoleModel::insertGetId($save);
        if (!$id) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        return ['code' => 200, 'message' => '操作成功！'];
    }

    /**
     * 编辑
     * @param $params
     * @return array
     */
    public static function edit($params)
    {
        $id = $params['id'] ?? null;
        $roleName = $params['role_name'] ?? null;
        $desc = $params['desc'] ?? '';
        $status = $params['status'] ?? null;

        if (!$id) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $role = RoleModel::find($id);
        if (empty($role)) {
            return ['code' => 1000, 'message' => '查询不到角色信息'];
        }

        if (!$roleName) {
            return ['code' => 1000, 'message' => '角色名称不能为空'];
        }
        if (!$status || !in_array($status, array_keys(AdminModel::$statusList))) {
            return ['code' => 1000, 'message' => '状态异常'];
        }
        if ($roleName != $role->role_name && self::isExisted($roleName)) {
            return ['code' => 1000, 'message' => '角色名称已存在'];
        }

        $role->role_name = $roleName;
        $role->desc = $desc;
        $role->status = $status;
        $role->updated_at = time();

        $result = $role->save();
        if ($result ===false) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        return ['code' => 200, 'message' => '操作成功！'];
    }

    protected static function isExisted($roleName)
    {
        $params['role_name'] = $roleName;
        $role = RoleModel::ofConditions($params)->first();

        if ($role) {
            return true;
        }

        return false;
    }
}