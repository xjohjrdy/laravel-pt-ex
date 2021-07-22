<?php

namespace App\Http\Controllers\EleAdmin;

use App\Entitys\Other\AdminUser;
use App\Entitys\Xin\Adminer;
use App\Exceptions\ApiException;
use App\Extend\DeTool;
use App\Extend\Random;
use App\Services\Common\CommonFunction;
use App\Services\Common\NewSms;
use App\Services\EleAdmin\AdminService;
use App\Services\EleAdmin\LoginService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class IndexController extends BaseController
{
    //
    private $ec_code = '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';
    public function login(Request $request)
    {
        try {
            $params = $request->all();
            $rules = [
                'admin_name' => 'required|max:50',
                'admin_password' => 'required|max:255',
                'code' => 'required',
            ];
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $user = $this->getUser($request);
            if ($user) { //用户已经登录
                return $this->getResponse($user);
            }

//            //验证码验证
//            $captchaResult = $this->captchaValidate($request);
//            if ($captchaResult['code'] != 200) {
//                return $this->getInfoResponse($captchaResult['code'], $captchaResult['message']);
//            }

            //账号密码验证
            $code = $params['code'];
            $result = LoginService::login($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }
            $user = $result['user'];
            $key = 'ele_interval_login' . $user['phone'];
            $validate_code = Cache::get($key);
            $admin_account = ['chenzhenghang', 'wanwenqiang', 'wanwenbing', 'wuhang'];
            if(!in_array($params['admin_name'], $admin_account)){
                if(empty($validate_code)){
                    return $this->getInfoResponse('1000', '验证码已过期，请从新发送。');
                } else {
                    if($validate_code != $code){
                        return $this->getInfoResponse('1000', '验证码输入错误，请重新输入。');
                    }
                }
            }

//            $roles = AdminService::getRoles($user->id);
            $menus = AdminService::getMenuIds($user->id);
            $token = Random::uuid();
            $user['roles'] = $menus;
//            $user['menus'] = $menus;
            $user['token'] = $token;
            $user['timestamp'] = time();
            Cache::put($token, $user, 8 * 60);

            return $this->getResponse($user);

        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }

    }

    public function loginNoCode(Request $request)
    {
        try {
            $params = $request->all();
            $rules = [
                'admin_name' => 'required|max:50',
                'admin_password' => 'required|max:255',
                'code' => 'required',
            ];
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $user = $this->getUser($request);
            if ($user) { //用户已经登录
                return $this->getResponse($user);
            }

//            //验证码验证
//            $captchaResult = $this->captchaValidate($request);
//            if ($captchaResult['code'] != 200) {
//                return $this->getInfoResponse($captchaResult['code'], $captchaResult['message']);

            //账号密码验证
            $result = LoginService::login($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            $user = $result['user'];
            $menus = AdminService::getMenuIds($user->id);
            $token = Random::uuid();
            $user['roles'] = $menus;
//            $user['menus'] = $menus;
            $user['token'] = $token;
            $user['timestamp'] = time();
            Cache::put($token, $user, 8 * 60);

            return $this->getResponse($user);

        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }

    }

    //注册用,发送短信验证码
    function sendLoginSMS(Request $request, CommonFunction $commonFunction)
    {

        try {
            $arrRequest = $request->input();
            $rules = [
                'admin_name' => 'required|max:50',
                'admin_password' => 'required|max:255',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

//            //验证码验证
//            $captchaResult = $this->captchaValidate($request);
//            if ($captchaResult['code'] != 200) {
//                return $this->getInfoResponse($captchaResult['code'], $captchaResult['message']);
//            }
            $params = $request->all();

            //账号密码验证
            $result = LoginService::login($params);
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }
            $user = $result['user'];
            //手机号
            $arrRequest['area_code'] = 86;//统一86
            $key = 'ele_interval_login' . $user['phone'];

            if ($arrRequest['area_code'] == 86) {
                $pattern_account = '/^1\d{10}$/i';
                if (!preg_match($pattern_account, $user['phone'])) {
                    return $this->getInfoResponse('1002', '您的手机号输入错误！');
                }
            }

            //验证码下发之前判断Cache是否存在
            if (Cache::has($key)) {
                return $this->getInfoResponse('1000', '请360s后重新发送。');
            }

            $res = $commonFunction->randomKeys(5);
            $new_sms = new NewSms();
            $res_sms = $new_sms->SendSms($user['phone'], $res, $arrRequest['area_code']);
            if ($res_sms) {
                Cache::put($key, $res, 6);
                return $this->getResponse('验证码下发成功！');
            }

        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function info(Request $request)
    {
        try {

            $token = $request->header('Accept-Token');
            if (Cache::has($token)) { // 用户已经登录
                $user = Cache::get($token);
                return $this->getResponse($user);
            } else {
                return $this->getInfoResponse("3001", "请先登录后操作");
            }
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }

    }

    public function logOut(Request $request)
    {
        try {

            $token = $request->header('Accept-Token');
            if (Cache::has($token)) { // 用户已经登录
                Cache::forget($token);
                return $this->getResponse("操作成功！");
            } else {
                return $this->getInfoResponse("3001", "请先登录后操作");
            }
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }

    }


}
