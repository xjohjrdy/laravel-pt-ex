<?php

namespace App\Http\Middleware\Check;

use App\Exceptions\ApiException;
use Closure;

class CheckUrl
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->url() != $request->url)
        {
            throw new ApiException('非法请求');
        }
        return $next($request);
    }
}
