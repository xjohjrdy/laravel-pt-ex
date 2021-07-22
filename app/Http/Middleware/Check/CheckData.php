<?php

namespace App\Http\Middleware\Check;

use App\Exceptions\ApiException;
use Closure;

class CheckData
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('token') && $request->header('time') && $request->header('sign') && $request->header('url')) {
            $request->token = $request->header('token');
            $request->time = $request->header('time');
            $request->sign = $request->header('sign');
            $request->url = $request->header('url');
        }
        if (!$request->token || !$request->time || !$request->sign || !$request->url) {
            throw new ApiException('参数错误');
        }

        return $next($request);
    }
}
