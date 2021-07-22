<?php

namespace App\Scopes\EleAdmin;

trait RoleMenuScopes
{
    public function scopeOfConditions($query, $params)
    {
        $roleId = $params['role_id'] ?? null;
        $roleIds = $params['role_ids'] ?? null;
        $menuId = $params['menu_id'] ?? null;
        $status = $params['status'] ?? null;

        if ($roleId) {
            $query->where('role_id', $roleId);
        }
        if ($roleIds) {
            $query->whereIn('role_id', $roleIds);
        }
        if ($menuId) {
            $query->where('menu_id', $menuId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }
}