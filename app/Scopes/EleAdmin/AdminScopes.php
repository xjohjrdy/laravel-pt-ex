<?php

namespace App\Scopes\EleAdmin;

trait AdminScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $adminName = $params['admin_name'] ?? '';
        $status = $params['status'] ?? null;

        if ($id) {
            $query->where('id', $id);
        }
        if ($adminName) {
            $query->where('admin_name', 'like', "{$adminName}%");
        }
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }
}