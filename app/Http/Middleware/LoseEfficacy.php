<?php

namespace App\Http\Middleware;

use Closure;

class LoseEfficacy
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
        if (!$request->session()->has('users')) {
            return redirect('agent_404');
        }
        return $next($request);
    }
}
