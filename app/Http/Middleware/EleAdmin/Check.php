<?php

namespace App\Http\Middleware\EleAdmin;

use App\Services\Tools\WebApiRsa;
use Closure;
use Illuminate\Support\Facades\Cache;

class Check
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
        $request_token = $request->header('Accept-Token');
        if(empty($request_token)){
            return response([
                'code' => 500,
                'msg' => '请登录后操作！',
                'time' => time(),
            ]);
        }
        if(!Cache::has($request_token)){
            return response([
                'code' => 500,
                'msg' => 'token已过期，请重新登录!',
                'time' => time(),
            ]);
        }
        $user = Cache::get($request_token);
        $time = time();
        $sub_time = $time - $user['timestamp'];
        if($sub_time > 7 * 60 * 60 && $sub_time < 8 * 60 * 60){
            $user['timestamp'] = $time;
            Cache::put($request_token, $user, 8*60);
        }
        $response = $next($request);
        return $response;
    }
}
