<?php

namespace App\Http\Middleware;

use Closure;

class TrustedProxies
{
    protected $proxies = ['47.240.93.4', '127.0.0.1'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->setTrustedProxies($this->proxies);
        return $next($request);
    }
}
