<?php

namespace App\Scopes\EleAdmin;

trait AdminRoleScopes
{
    public function scopeOfConditions($query, $params)
    {
        $adminId = $params['admin_id'] ?? null;
        $roleId = $params['role_id'] ?? null;
        $status = $params['status'] ?? null;

        if ($adminId) {
            $query->where('admin_id', $adminId);
        }
        if ($roleId) {
            $query->where('role_id', $roleId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }
}