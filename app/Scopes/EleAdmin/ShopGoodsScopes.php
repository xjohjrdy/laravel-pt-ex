<?php

namespace App\Scopes\EleAdmin;

trait ShopGoodsScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $title = $params['title'] ?? null;
        $deletedAt = $params['deleted_at'] ?? '';

        if ($id) {
            $query->where('id', $id);
        }
        if ($title) {
            $query->where('title', "{$title}%");
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if ($deletedAt === 'is null') {
            $query->whereNull('deleted_at');
        }

        return $query;
    }
}