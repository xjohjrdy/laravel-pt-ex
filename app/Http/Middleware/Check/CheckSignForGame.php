<?php

namespace App\Http\Middleware\Check;

use App\Exceptions\ApiException;
use Closure;

class CheckSignForGame
{
    /**
     * 为游戏项目设立的对称加密
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws ApiException
     */
    public function handle($request, Closure $next)
    {
        $key = "13a4tqcHQdKYrVKTGqL1";
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
