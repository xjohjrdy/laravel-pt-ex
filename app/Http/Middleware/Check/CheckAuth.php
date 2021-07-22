<?php

namespace App\Http\Middleware\Check;

use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;
use Closure;
use Illuminate\Support\Facades\Redis;

class CheckAuth
{

    const B_HASH_HEAD = 'b:hg:info:';
    const B_DEVICE_WHITE_LIST = 'b:sg:device:w:list'; //多点登录用户白名单

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws ApiException
     */
    public function handle($request, Closure $next)
    {

        $request_version = $request->header('Accept-Version'); //选用接口版本号
        $request_device = $request->header('Accept-Device'); //设备类型
        $request_appversion = $request->header('Accept-Appversion'); //版本号
        $request_sign_id = $request->header('User-Sign-Id'); // 用户app_id
        $request_device_id = $request->header('User-Device-Id'); //用户设备ID

        //无限期关闭该功能
        return $next($request);

        //只测试安卓
        if ($request_device != 'android') {
            return $next($request);
        }

        if ($request_appversion <= 186) {
            return $next($request);
        }

        //针对游客
        if (empty($request_sign_id)) {
            return $next($request);
        }

        $oct = base_convert($request_sign_id, 35, 10);

        $app_id = $oct / 3 - 92;

        //用户有进行伪造数据
        if (!is_int($app_id) || $app_id <= 0) {
            throw new ApiException('认证失败');
        }


        //判断是否有该哟用户的缓存记录
        $is_app_id = Redis::EXISTS(self::B_HASH_HEAD . $app_id);

        if ($is_app_id == 0) {
            //获取用户数据
            $s_user_info = AppUserInfo::find($app_id, ['id', 'status']);


            if (empty($s_user_info)) {
                throw new ApiException('认证失败,用户信息错误！');
            }

            Redis::HMSET(self::B_HASH_HEAD . $app_id, 'app_id', $app_id, 'status', $s_user_info->status, 'device_id', $request_device_id);
        }

        $r_user_info = Redis::HMGET(self::B_HASH_HEAD . $app_id, 'status', 'device_id');

        if ($r_user_info[0] == 2) {
            return response([
                'code' => 44404,
                'msg' => '账号违规，已被封禁！！',
                'time' => time(),
            ]);
        }

        //白名单用户 manage1.36qq.com后台配置白名单用户
        if (Redis::SISMEMBER(self::B_DEVICE_WHITE_LIST, $app_id)) {
            return $next($request);
        }

        if ($request_device_id != $r_user_info[1]) {
            return response([
                'code' => 44401,
                'msg' => '账号在其他设备登录。',
                'time' => time(),
            ]);
        }

        return $next($request);
    }
}
