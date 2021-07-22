<?php

namespace App\Http\Middleware\Check;

use App\Exceptions\ApiException;
use App\Services\Crypt\RsaUtils;
use Closure;

class CheckToken
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $rsa = new RsaUtils();
        $request->data = $rsa->rsaDecode($request->token);
        if (!$request->data)
        {
            throw new ApiException('认证失败');
        }

        return $next($request);
    }
}
