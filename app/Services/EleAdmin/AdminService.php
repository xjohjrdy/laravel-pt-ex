<?php

namespace App\Services\EleAdmin;

use App\Services\Service;
use App\Models\EleAdmin\Admin as AdminModel;
use App\Models\EleAdmin\Role as RoleModel;
use App\Models\EleAdmin\AdminRole as AdminRoleModel;
use App\Tools\ObjectDataHandle;
use Illuminate\Support\Facades\Hash;

class AdminService extends Service
{
    public static function getMenus($userId)
    {
        $isMaster = self::isMaster($userId);
        if ($isMaster) {
            return MenuService::getMenus();
        }

        $roles = self::getRoles($userId);
        if (empty($roles)) {
            return [];
        }

        $menus = MenuService::getMenus($roles);

        return $menus;
    }

    public static function getRoles($userId)
    {
        $params['admin_id'] = $userId;
        $params['status'] = AdminRoleModel::ACCOUNT_STATUS_EFFECTIVE;

        $query = AdminRoleModel::ofConditions($params)->pluck('role_id');
        $roles = ObjectDataHandle::handle($query);

        $params = [];
        $params['ids'] = $roles;
        $params['status'] = RoleModel::ACCOUNT_STATUS_DISPLAY;

        $query = RoleModel::ofConditions($params)->pluck('id');
        $roles = ObjectDataHandle::handle($query);

        return $roles;
    }

    public static function getMenuIds($userId)
    {
        $isMaster = self::isMaster($userId);
        if ($isMaster) {
            return MenuService::getMenuIds();
        }

        $roles = self::getRoles($userId);
        if (empty($roles)) {
            return [];
        }

        return MenuService::getMenuIds($roles);
    }

    /**
     * 是否是主管理员
     * @param $userId
     * @return bool
     */
    public static function isMaster($userId)
    {
        $params['id'] = $userId;

        $columns = ['id', 'is_master', 'status'];

        $admin = AdminModel::select($columns)->ofConditions($params)->first();

        if (empty($admin)) {
            return false;
        }
        if ($admin->is_master == AdminModel::IS_MASTER) {
            return true;
        }

        return false;
    }

    /**
     * 新增
     * @param $params
     * @return array
     */
    public static function add($params, $opId = 0)
    {
        $adminName = $params['admin_name'] ?? null;
        $adminPassword = $params['admin_password'] ?? null;
        $realName = $params['real_name'] ?? '';
        $phone = $params['phone'] ?? '';
        $status = $params['status'] ?? null;
        $remark = $params['remark'] ?? '';
        $roleIds = $params['role_ids'] ?? [];

        if (!$adminName) {
            return ['code' => 1000, 'message' => '用户名不能为空'];
        }
        if (!$adminPassword) {
            return ['code' => 1000, 'message' => '密码不能为空'];
        }
        if (!$status || !in_array($status, array_keys(AdminModel::$statusList))) {
            return ['code' => 1000, 'message' => '状态异常'];
        }
        if (!$roleIds) {
            return ['code' => 1000, 'message' => '角色不能为空'];
        }
        if (self::isExisted($adminName)) {
            return ['code' => 1000, 'message' => '用户名已存在'];
        }

        $time = time();
        $adminPassword = Hash::make($adminPassword);
        $save['admin_name'] = $adminName;
        $save['admin_password'] = $adminPassword;
        $save['real_name'] = $realName;
        $save['phone'] = $phone;
        $save['status'] = $status;
        $save['remark'] = $remark;
        $save['is_master'] = 2;
        $save['op_id'] = $opId;
        $save['created_at'] = $time;
        $save['updated_at'] = $time;

        $id = AdminModel::insertGetId($save);
        if (!$id) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        self::setRoles($id, $roleIds);

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
        $adminName = $params['admin_name'] ?? null;
        $adminPassword = $params['admin_password'] ?? null;
        $realName = $params['real_name'] ?? '';
        $phone = $params['phone'] ?? '';
        $status = $params['status'] ?? null;
        $remark = $params['remark'] ?? '';
        $roleIds = $params['role_ids'] ?? [];

        if (!$id) {
            return ['code' => 1000, 'message' => '参数异常'];
        }

        $admin = AdminModel::find($id);
        if (empty($admin)) {
            return ['code' => 1000, 'message' => '查询不到账号信息'];
        }

        if (!$adminName) {
            return ['code' => 1000, 'message' => '用户名不能为空'];
        }
        if (!$status || !in_array($status, array_keys(AdminModel::$statusList))) {
            return ['code' => 1000, 'message' => '状态异常'];
        }
        if ($adminName != $admin->admin_name && self::isExisted($adminName)) {
            return ['code' => 1000, 'message' => '用户名已存在'];
        }
        if ($adminPassword && $adminPassword != $admin->admin_password) {
            $admin->admin_password = Hash::make($adminPassword);
        }

        $admin->admin_name = $adminName;
        $admin->real_name = $realName;
        $admin->phone = $phone;
        $admin->status = $status;
        $admin->remark = $remark;
        $admin->updated_at = time();

        $result = $admin->save();
        if ($result ===false) {
            return ['code' => 1000, 'message' => '操作失败！'];
        }

        self::setRoles($id, $roleIds);

        return ['code' => 200, 'message' => '操作成功！'];
    }

    public static function setRoles($userId, $roleIds = [])
    {
        if (empty($roleIds)) {
            return false;
        }

        $save = [];
        foreach ($roleIds as $roleId) {
            $save[] = [
                'admin_id' => $userId,
                'role_id' => $roleId,
            ];
        }

        $params['admin_id'] = $userId;

        $result = AdminRoleModel::ofConditions($params)->delete();
        if ($result === false) {
            return  false;
        }

        AdminRoleModel::insert($save);

        return true;
    }

    protected static function isExisted($adminName)
    {
        $params['admin_name'] = $adminName;
        $admin = AdminModel::ofConditions($params)->first();

        if ($admin) {
            return true;
        }

        return false;
    }
}