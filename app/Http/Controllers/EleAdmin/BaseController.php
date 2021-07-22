<?php

namespace App\Http\Controllers\EleAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BaseController extends Controller
{
    /**
     * 获取用户登录信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getUser(Request $request)
    {
        $token = $request->header('Accept-Token');
        if (Cache::has($token)) {
            $user = Cache::get($token);

            return $user;
        }

        return null;
    }

    public function paginate($query, Request $request = null)
    {
        $request = $request ?: request();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $offset = ($page - 1) * $limit;

        $countQuery = clone $query;
        $count = $countQuery->count();
        $records = new Collection([]);
        if ($count > 0) {
            $records = $query->offset($offset)->limit($limit)->get();
        }

        $pagination = [
            'page' => $page,
            'count' => $count,
            'page_count' => ceil($count/$limit),
            'limit' => $limit
        ];

        return [$records, $pagination];
    }
}