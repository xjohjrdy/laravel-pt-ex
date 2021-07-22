<?php

namespace App\Http\Controllers\OtherAdmin;

use App\Entitys\Other\AdminUser;
use App\Entitys\Other\ThreeUser;
use App\Entitys\Other\ThreeUserGet;
use App\Exceptions\ApiException;
use App\Extend\Random;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    //
    public function login(Request $request)
    {
        try {
            $adminModel = new AdminUser();
            $arrRequest = $request->input();
            $rules = [
                'phone' => 'required',
                'password' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $token = $request->header('Accept-Token');
            if(Cache::has($token)){ // 用户已经登录
                $user = Cache::get($token);
                return $this->getResponse($user);
            }
            $phone = $arrRequest['phone'];
            $password = $arrRequest['password'];


            $user = $adminModel->checkLogin($phone, $password);
            $user['roles'] = explode(',', $user['roles']);
            $token = Random::uuid();
            $user['token'] = $token;
            $user['timestamp'] = time();
            Cache::put($token, $user, 60);
            return $this->getResponse($user);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }

    }

    public function info(Request $request)
    {
        try {

            $token = $request->header('Accept-Token');
            if(Cache::has($token)){ // 用户已经登录
                $user = Cache::get($token);
                return $this->getResponse($user);
            }else {
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
            if(Cache::has($token)){ // 用户已经登录
                Cache::forget($token);
                return $this->getResponse("操作成功！");
            }else {
                return $this->getInfoResponse("3001", "请先登录后操作");
            }
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }

    }


}
