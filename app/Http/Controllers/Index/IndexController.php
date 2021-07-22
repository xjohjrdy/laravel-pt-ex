<?php

namespace App\Http\Controllers\Index;

use App\Entitys\App\AppUserInfo;
use App\Entitys\UserIndex;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\Crypt\RsaUtils;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{

    const B_HASH_HEAD = 'b:hg:info:';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserIndex $userIndex)
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * app登录
     * @param Request $request
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'phone' => 'required',
                'password' => 'required',
                'device_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_user = $appUserInfo->getUserByPhone($arrRequest['phone']);
            if (empty($app_user)) {
                return $this->getInfoResponse('4004', '很抱歉~该手机号尚未注册，请点立即注册按钮进行注册！');
            }

            if ($app_user->status == 2) {
                // return $this->getInfoResponse('4014', '该用户已被管理后台停用');
                return $this->getInfoResponse('4014', '该账号已被注销');
            }

            if ($app_user->status == 3) {
                return $this->getInfoResponse('440', '该用户未激活');
            }


            if (!array_key_exists('is_wechat_login_wuhang', $arrRequest)) {
                if (!password_verify($arrRequest['password'], $app_user->password)) {
                    return $this->getInfoResponse('4034', '登录失败,密码错误');
                }
            }

            // todo 更替新的设备码
            $request_device_id = $request->header('User-Device-Id');
            if (!empty($request_device_id)) {
                $app_id = $app_user->id;
                Redis::HMSET(self::B_HASH_HEAD . $app_id, 'app_id', $app_id, 'status', $app_user->status, 'device_id', $request_device_id);
            }

            //10000000
            $new_app_id = $app_user->id;
            if ($app_user->id >= 10000000) {
                $new_app_id = base_convert($app_user->id, 10, 33); // 10 转 33
                $new_app_id = 'x' . $new_app_id;
            }

            $data = [
                'id' => $app_user->id,
                'show_id' => $new_app_id,
                'user_name' => $app_user->user_name,
                'real_name' => $app_user->real_name,
                'avatar' => $app_user->avatar,
                'phone' => $app_user->phone,
                'alipay' => $app_user->alipay,
                'parent_id' => $app_user->parent_id,
            ];

            $appUserInfo->updateUserLogin($app_user->id, $arrRequest['device_id']);

            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getFile() . ',行' . $e->getLine(), '500');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * 更新登录状态
     * @param Request $request
     * @param $id
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, AppUserInfo $appUserInfo)
    {

        return $this->getResponse('o1k');
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'device_id' => 'required',
            ];
            if (empty($arrRequest['device_id'])) {
                return $this->getInfoResponse('4415', '0');
            }
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_user = $appUserInfo->getUserById($arrRequest['app_id']);
            if (empty($app_user->is_online)) {
                return $this->getInfoResponse('4414', '由于您长时间未登录操作，已临时下线，请您重新登陆操作。');
            }

            if (time() > ($app_user->login_time + 172800)) {
                return $this->getInfoResponse('4415', '0');
            }

            if ($app_user->device <> $arrRequest['device_id']) {
                return $this->getInfoResponse('4416', '您的账号在其他地方登录，请您重新登录，若不是您本人登陆，建议修改密码。');
            }

            if (time() > ($app_user->login_time + 86400)) {
                $appUserInfo->updateUserLogin($app_user->id, $arrRequest['device_id']);
            }

            return $this->getResponse('ok');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 退出登录
     * @param $id
     * @param Request $request
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function destroy($id, Request $request, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            return $this->getResponse('ok');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
