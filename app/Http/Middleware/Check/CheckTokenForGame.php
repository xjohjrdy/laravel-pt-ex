<?php

namespace App\Http\Middleware\Check;

use App\Exceptions\ApiException;
use App\Services\Crypt\RsaUtils;
use Closure;

class CheckTokenForGame
{
    /**
     * 为游戏详细设立的非对称匹配
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws ApiException
     */
    public function handle($request, Closure $next)
    {
        $rsa = new RsaUtils();
        $request->data = $rsa->rsaOutlineDecode($request->token);
        if (!$request->data)
        {
            throw new ApiException('认证失败');
        }

        return $next($request);
    }
}
