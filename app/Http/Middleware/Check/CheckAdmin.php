<?php

namespace App\Http\Middleware\Check;

use App\Exceptions\ApiException;
use Closure;

class CheckAdmin
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $arr = [
            '39.97.108.135',
            '39.105.155.220',
            '149.129.54.197',
            '127.0.0.1',
            '39.106.6.68',
            '110.87.32.189',
            '123.57.80.156',

        ];
        //后台内网
        if (!in_array($request->ip(), $arr)) {
            throw new ApiException('您不符合我们的安全机制！');
        }

        return $next($request);
    }
}
