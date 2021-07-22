<?php

namespace App\Scopes\EleAdmin\PutNew;

trait RewardScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $deletedAt = $params['deleted_at'] ?? '';

        if ($id) {
            $query->where('id', $id);
        }
        if ($deletedAt == 'is null') {
            $query->whereNull('deleted_at');
        }

        return $query;
    }
}