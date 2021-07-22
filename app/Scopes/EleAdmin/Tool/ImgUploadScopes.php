<?php

namespace App\Scopes\EleAdmin\Tool;

trait ImgUploadScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;

        if ($id) {
            $query->where('id', $id);
        }

        return $query;
    }
}