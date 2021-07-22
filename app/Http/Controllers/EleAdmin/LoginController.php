<?php

namespace App\Http\Controllers\EleAdmin;

use App\Extend\Random;
use App\Http\Requests\EleAdmin\StoreLoginPost;
use App\Services\EleAdmin\AdminService;
use App\Services\EleAdmin\LoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoginController extends BaseController
{
    protected $captchaPrefix = 'ele_login_captcha_';

    /**
     * 管理员登录
     * @param Request $request
     * @param StoreLoginPost $adminPost
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request, StoreLoginPost $adminPost)
    {
        try {
            $user = $this->getUser($request);
            if ($user) { //用户已经登录
                return $this->getResponse($user);
            }

            //验证码验证
            $captchaResult = $this->captchaValidate($request);
            if ($captchaResult['code'] != 200) {
                return $this->getInfoResponse($captchaResult['code'], $captchaResult['message']);
            }

            $params = $request->all();

            //账号密码验证
            $result = LoginService::login($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            $user = $result['user'];
            $roles = AdminService::getRoles($user->id);
//            $menus = AdminService::getMenus($user->id);
            $token = Random::uuid();
            $user['roles'] = $roles;
//            $user['menus'] = $menus;
            $user['token'] = $token;
            $user['timestamp'] = time();
            Cache::put($token, $user, 8 * 60);

            return $this->getResponse($user);

        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function captcha()
    {
        $url = app('captcha')->create('default', true);

        Cache::put($this->captchaPrefix . $url['key'], $url['key'], 2 * 60);

        return $this->getResponse($url);
    }

    /**
     * 验证码验证
     * @param $request
     * @return array
     */
    public function captchaValidate($request)
    {
        //验证操作
        if (!captcha_api_check($request->captcha, $request->captcha_key)) {
            return ['code' => 1000, 'message' => '验证码不匹配'];
        }

        $key = $this->captchaPrefix . $request->captcha_key;
        if (!Cache::has($key)) { // 验证码过期
            return ['code' => 1000, 'message' => '验证码已失效'];
        }

        Cache::forget($key);

        return ['code' => 200, 'message' => '验证码匹配成功'];
    }
}