<?php

namespace App\Http\Middleware;

use App\Services\Tools\WebApiRsa;
use Closure;

class CheckWeb
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

        $request_uri = $request->getRequestUri();
        $request_timestamp = $request->header('Accept-Timestamp');
        $request_token = $request->header('Accept-Token');

        if ($request->isMethod('get')) {
            $request_content = $request->header('Accept-Sign');
        } else {
            $request_content = $request->getContent();
        }

        $tool_rsa = new WebApiRsa();

        if (abs(time() - $request_timestamp) > 60) {
			
			return response(([
                'code' => 525,
                'msg' => '接口请求超时！！！',
                'time' => time(),
            ]));
			
        }

        $this_token = hash('sha256', $request_uri . $request_timestamp . $request_content);

        if (strcasecmp($request_token, $this_token)) {
			
			return response(([
                'code' => 500,
                'msg' => '异常操作！！',
                'time' => time(),
            ]));
			
			
        }

        $request->data = $tool_rsa->private_decrypt($request_content);

        $response = $next($request);
        return response($response->getContent());
    }
}
