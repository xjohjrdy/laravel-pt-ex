<?php

namespace App\Scopes\EleAdmin\PutNew;

trait FakerScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $ids = $params['ids'] ?? null;
        $phone = $params['phone'] ?? '';
        $userName = $params['user_name'] ?? '';
        $deletedAt = $params['deleted_at'] ?? '';

        if ($id) {
            $query->where('id', $id);
        }
        if ($ids) {
            $query->whereIn('id', $ids);
        }
        if ($phone) {
            $query->where('phone', 'like', "{$phone}%");
        }
        if ($userName) {
            $query->where('user_name', 'like', "{$userName}%");
        }
        if ($deletedAt == 'is null') {
            $query->whereNull('deleted_at');
        }

        return $query;
    }
}