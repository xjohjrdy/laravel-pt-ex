<?php

namespace App\Scopes\EleAdmin;

trait LiveShopGoodsScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $goodId = $params['good_id'] ?? null;
        $liveId = $params['live_id'] ?? '';
        $deletedAt = $params['deleted_at'] ?? '';

        if ($id) {
            $query->where('id', $id);
        }
        if ($goodId) {
            $query->where('good_id', $goodId);
        }
        if ($liveId) {
            $query->where('live_id', $liveId);
        }
        if (isset($params['read_is']) && $params['read_is'] !== '') {
            $query->where('read_is', $params['read_is']);
        }
        if ($deletedAt === 'is null') {
            $query->whereNull('deleted_at');
        }

        return $query;
    }
}