<?php

namespace App\Scopes\EleAdmin;

trait MenuScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $ids = $params['ids'] ?? null;
        $title = $params['title'] ?? '';
        $status = $params['status'] ?? null;
        $level = $params['level'] ?? null;
        $parentId = $params['parent_id'] ?? null;

        if ($id) {
            $query->where('id', $id);
        }
        if ($ids) {
            $query->whereIn('id', $ids);
        }
        if ($title) {
            $query->where('title', $title);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($level) {
            $query->where('level', $level);
        }
        if ($parentId) {
            $query->where('parent_id', $parentId);
        }

        return $query;
    }
}