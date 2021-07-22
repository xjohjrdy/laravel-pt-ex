<?php

namespace App\Scopes\EleAdmin\PutNew;

trait BackgroundConfigScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $deletedAt = $params['deleted_at'] ?? null;

        if ($id) {
            $query->where('id', $id);
        }
        if ($deletedAt == 'is null') {
            $query->whereNull('deleted_at');
        }

        return $query;
    }
}