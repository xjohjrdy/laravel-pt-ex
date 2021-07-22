<?php

namespace App\Scopes\EleAdmin;

use Illuminate\Support\Facades\DB;

trait LiveInfoScopes
{
    public function scopeOfConditions($query, $params)
    {
        $id = $params['id'] ?? null;
        $userName = $params['user_name'] ?? null;
        $title = $params['title'] ?? '';

        if ($id) {
            $query->where('id', $id);
        }
        if ($userName) {
            $query->where('user_name', 'like', "{$userName}%");
        }
        if ($title) {
            $query->where('title', 'like', "{$title}%");
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (isset($params['live_status']) && $params['live_status'] !== '') {
            switch ($params['live_status']) {
                case 0:
                    $query->where('end_time', 0);
                    $query->where('start_time', '>', time());
                    break;
                case 1:
                    $query->where('end_time', 0);
                    $query->where('start_time', '<=', time());
                    break;
                case 3:
                    $query->where('end_time', '>', 0);
                    break;
                default:
                    break;
            }
        }


        return $query;
    }
}