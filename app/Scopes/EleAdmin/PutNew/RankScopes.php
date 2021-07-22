<?php

namespace App\Scopes\EleAdmin\PutNew;

trait RankScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $ids = $params['ids'] ?? null;
        $showInfo = $params['show_info'] ?? '';
        $deletedAt = $params['deleted_at'] ?? '';

        if ($id) {
            $query->where('id', $id);
        }
        if ($ids) {
            $query->whereIn('id', $ids);
        }
        if ($showInfo) {
            $query->where('show_info', 'like', "{$showInfo}%");
        }
        if ($deletedAt == 'is null') {
            $query->whereNull('deleted_at');
        }
        if (isset($params['change']) && $params['change'] !== '') {
            $query->where('change', $params['change']);
        }

        return $query;
    }
}