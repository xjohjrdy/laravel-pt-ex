<?php

namespace App\Http\Middleware\Check;

use App\Exceptions\ApiException;
use Closure;

class CheckTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $time_check = time() - $request->time;
        if ($time_check > 60) {
            throw new ApiException('请将您的手机时间设置成北京时间后重试！');
        }

        return $next($request);
    }
}
