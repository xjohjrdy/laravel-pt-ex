<?php

namespace App\Http\Controllers\Tools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    public function getIp(Request $request)
    {
        $ip = $request->ip();
		return $ip;
    }

        
	public function getIpTest(Request $request)
    {
        @printf('HTTP_X_FORWARDED_FOR -> ');
        @printf($_SERVER['HTTP_X_FORWARDED_FOR']);
        echo "<pre>";

        @printf('HTTP_X_REAL_IP -> ');
        @printf($_SERVER['HTTP_X_REAL_IP']);
        echo "<pre>";

        @printf('REMOTE_ADDR -> ');
        @printf($_SERVER['REMOTE_ADDR']);
        echo "<pre>";

//        var_dump($request->getClientIps());
        $request->setTrustedProxies(['47.240.93.4']);//这个可以放入到中间件中
//        var_dump($request->getClientIps());
        var_dump($request->ip());

    }
}
