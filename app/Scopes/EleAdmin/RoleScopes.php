<?php

namespace App\Scopes\EleAdmin;

trait RoleScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $ids = $params['ids'] ?? null;
        $roleName = $params['role_name'] ?? '';
        $status = $params['status'] ?? null;

        if ($id) {
            $query->where('id', $id);
        }
        if ($ids) {
            $query->whereIn('id', $ids);
        }
        if ($roleName) {
            $query->where('role_name', 'like', "{$roleName}%");
        }
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }
}