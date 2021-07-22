<?php

namespace App\Http\Middleware\Check;

use App\Exceptions\ApiException;
use Closure;

class CheckSign
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key = "62h3svBYRsPUaZPXNRU9";
        $key_sub = mb_substr($key,0,5);
        $key_sub2 = mb_substr($key,5,5);
        $key_sub3 = mb_substr($key,10,5);
        $key_sub4 = mb_substr($key,15,5);
        $need_sign = $key_sub.$request->token.$key_sub2.$request->time.$key_sub3.$request->url.$key_sub4;
        $sign = hash("sha512",$need_sign);
        if ($request->sign != $sign)
        {
            throw new ApiException('非法签名');
        }
        return $next($request);
    }
}
