<?php

namespace App\Http\Controllers\Wechat;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\WechatInfo;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\Crypt\RsaUtils;
use \App\Services\Wechat\Wechat;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, AppUserInfo $appUserInfo, WechatInfo $wechatInfo, Wechat $wechat, RsaUtils $rsaUtils, Client $client)
    {
        DB::beginTransaction();
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('phone', $arrRequest) || !array_key_exists('last_step_id', $arrRequest) || !array_key_exists('device_id', $arrRequest)) {
                return $this->getInfoResponse(3001, '微信登录升级完毕，请下载新版使用微信登录！');
            }

            $app_user = $appUserInfo->getUserByPhone($arrRequest['phone']);

            $is_bind = 0;
            if ($app_user) {
                $is_bind = $wechatInfo->getAppId($app_user->id);
            }
            if ($is_bind) {
                return $this->getInfoResponse('4009', '该账号已经被其他微信绑定');
            }
            if (!$app_user) {
                $res = $wechat->doRegister($arrRequest['phone'], $client);
                if (!$res) {
                    throw new ApiException('异常情况，请联系客服！', '4004');
                }
                if ($res['code'] == 400) {
                    throw new ApiException($res['message'], '4004');
                }
                $app_user = $appUserInfo->getUserByPhone($arrRequest['phone']);
                $wechat_info = $wechatInfo->updateById($arrRequest['last_step_id'], $app_user->id);
            } else {
                $wechat_info = $wechatInfo->updateById($arrRequest['last_step_id'], $app_user->id);
            }
            if (!empty($arrRequest['user_name'])) {
                $wechatInfo->where(['id' => $arrRequest['last_step_id'], 'app_id' => $app_user->id])->update([
                    'nickname' => $arrRequest['user_name'],
                ]);
            }
            if (!empty($arrRequest['ico_image'])) {
                $wechatInfo->where(['id' => $arrRequest['last_step_id'], 'app_id' => $app_user->id])->update([
                    'headimgurl' => $arrRequest['ico_image']
                ]);
            }


            $res_login = $wechat->loginApp($app_user->phone, '123456', 1, $client, $rsaUtils, $arrRequest['device_id']);
            DB::commit();

            //绑定微信完成任务
            if (!config('test_appid.debug') || in_array($app_user->id, config('test_appid.app_ids'))) {
                try {
                    $coinCommonService = new CoinCommonService($app_user->id);
                    $task_id = 2;#关联微信
                    $task_time = time();
                    $task_coin = $coinCommonService->successTask($task_id, $task_time);
                    $res_login['task_coin'] = $task_coin;
                } catch (\Exception $e) {
                    $res_login['task_coin'] = 0;
                }
            }

            return $this->getResponse($res_login);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     *
     * https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=APPID&grant_type=refresh_token&refresh_token=REFRESH_TOKEN
     *
     *
     * https://api.weixin.qq.com/sns/auth?access_token=ACCESS_TOKEN&openid=OPENID
     *
     *
     * https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID
     * @param Request $request {"code":""}
     * @param Client $client
     * @param Wechat $wechat
     * @param WechatInfo $wechatInfo
     * @param RsaUtils $rsaUtils
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     * @throws \Exception
     */
    public function create(Request $request, Client $client, Wechat $wechat, WechatInfo $wechatInfo, RsaUtils $rsaUtils, AppUserInfo $appUserInfo)
    {
        DB::beginTransaction();
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('code', $arrRequest) || !array_key_exists('device_id', $arrRequest)) {
                return $this->getInfoResponse(3021, '微信登录升级完毕，请下载新版使用微信登录！');
            }
            $is_get = 0;
            $data = $wechat->getOne($arrRequest['code'], $client);
            if (array_key_exists('errcode', $data) || array_key_exists('errmsg', $data)) {
                throw new ApiException('微信接收出现异常！请重试', $data['errcode']);
            }

            if (array_key_exists('unionid', $data)) {
                $is_get = 1;
            }

            $res_login = '';
            if (array_key_exists('access_token', $data) && !$is_get) {
                $res = $wechatInfo->loginInsert($data['access_token'], $data['expires_in'], $data['refresh_token'], $data['openid'], $data['scope']);
                $res_login = $res->id;
            }

            if ($is_get) {
                $wechat_info = $wechatInfo->getByOpenId($data['openid']);
                if (!$wechat_info) {
                    $res = $wechatInfo->loginInsertUnion($data['access_token'], $data['expires_in'], $data['refresh_token'], $data['openid'], $data['scope'], $data['unionid']);
                    $res_login = $res->id;
                    $is_get = 0;
                } else {
                    if ($wechat_info->app_id) {
                        $user = $appUserInfo->getUserById($wechat_info->app_id);
                        $res_login = $wechat->loginApp($user->phone, '123456', 1, $client, $rsaUtils, $arrRequest['device_id']);
                    } else {
                        $res_login = $wechat_info->id;
                        $is_get = 0;
                    }
                }
            }

            if (!empty($data['openid']) && !empty($data['access_token'])) {
                $res_user = $wechat->getUserNameIcoImg($data['access_token'], $data['openid'], $client);
                if (!empty($res_login)) {
                    if ($is_get == 0) {
                        $res_login_can_id = $res_login;
                    } else {
                        $res_login_can_id = $wechat_info->id;
                    }
                    if (!empty($res_user['nickname'])) {
                        $wechatInfo->where(['id' => $res_login_can_id])->update([
                            'nickname' => $res_user['nickname'],
                        ]);
                    }
                    if (!empty($res_user['headimgurl'])) {
                        $wechatInfo->where(['id' => $res_login_can_id])->update([
                            'headimgurl' => $res_user['headimgurl'],
                        ]);
                    }
                    if (!empty($res_user['sex'])) {
                        $wechatInfo->where(['id' => $res_login_can_id])->update([
                            'sex' => $res_user['sex'],
                        ]);
                    }
                }
            }

            DB::commit();
            return $this->getResponse([
                'is_get' => $is_get,
                'login_data' => $res_login,
                'openid' => empty($data['openid']) ? 0 : $data['openid'],
                'access_token' => empty($data['access_token']) ? 0 : $data['access_token'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
//            throw new ApiException('网络开小差了！请稍后再试', '500');
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * 如果需要
     * {"phone":"","code":"","last_step_id":""}
     * @param Request $request
     * @param AppUserInfo $appUserInfo
     * @param WechatInfo $wechatInfo
     * @param Wechat $wechat
     * @param RsaUtils $rsaUtils
     * @param Client $client
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     * @throws \Exception
     */
    public function store(Request $request, AppUserInfo $appUserInfo, WechatInfo $wechatInfo, Wechat $wechat, RsaUtils $rsaUtils, Client $client)
    {

//        return $this->getInfoResponse(3001, '内测期间，注册暂未开放，感谢您的耐心等待！');
        DB::beginTransaction();
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('phone', $arrRequest) || !array_key_exists('code', $arrRequest) || !array_key_exists('last_step_id', $arrRequest) || !array_key_exists('device_id', $arrRequest)) {
                return $this->getInfoResponse(3001, '微信登录升级完毕，请下载新版使用微信登录！');
            }

            if (Cache::has($arrRequest['phone'])) {
                $code = Cache::get($arrRequest['phone']);
                if ($code <> $arrRequest['code']) {
                    return $this->getInfoResponse('4000', '手机验证码错误');
                }
            } else {
                return $this->getInfoResponse('4004', '手机不存在验证码');
            }

            $app_user = $appUserInfo->getUserByPhone($arrRequest['phone']);

            $is_bind = 0;
            if ($app_user) {
                $is_bind = $wechatInfo->getAppId($app_user->id);
            }
            if ($is_bind) {
                return $this->getInfoResponse('4009', '该账号已经被其他微信绑定');
            }
            if (!$app_user) {
                $res = $wechat->doRegister($arrRequest['phone'], $client);
                if (!$res) {
                    throw new ApiException('异常情况，请联系客服！', '4004');
                }
                if ($res['code'] == 400) {
                    throw new ApiException($res['message'], '4004');
                }
                $app_user = $appUserInfo->getUserByPhone($arrRequest['phone']);
                $wechat_info = $wechatInfo->updateById($arrRequest['last_step_id'], $app_user->id);
            } else {
                $wechat_info = $wechatInfo->updateById($arrRequest['last_step_id'], $app_user->id);
            }
            if (!empty($arrRequest['user_name']) && empty($app_user->user_name)) {
                $appUserInfo->updateUserInfoWithIM($app_user->id, $arrRequest['user_name']);
            }
            if (!empty($arrRequest['ico_image']) && empty($app_user->avatar)) {
                $appUserInfo->updateUserInfoAvatar($app_user->id, $arrRequest['ico_image']);
            }
            if (!empty($arrRequest['user_name'])) {
                $wechatInfo->where(['id' => $arrRequest['last_step_id'], 'app_id' => $app_user->id])->update([
                    'nickname' => $arrRequest['user_name'],
                ]);
            }
            if (!empty($arrRequest['ico_image'])) {
                $wechatInfo->where(['id' => $arrRequest['last_step_id'], 'app_id' => $app_user->id])->update([
                    'headimgurl' => $arrRequest['ico_image']
                ]);
            }
            $res_login = $wechat->loginApp($app_user->phone, '123456', 1, $client, $rsaUtils, $arrRequest['device_id']);
            DB::commit();
            return $this->getResponse($res_login);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, AppUserInfo $appUserInfo, WechatInfo $wechatInfo, Wechat $wechat, RsaUtils $rsaUtils, Client $client)
    {
        DB::beginTransaction();
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('phone', $arrRequest) || !array_key_exists('password', $arrRequest) || !array_key_exists('last_step_id', $arrRequest) || !array_key_exists('device_id', $arrRequest)) {
                return $this->getInfoResponse(3001, '微信登录升级完毕，请下载新版使用微信登录！');
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
            if (!password_verify($arrRequest['password'], $app_user->password)) {
                return $this->getInfoResponse('4034', '绑定失败,密码错误');
            }

            $is_bind = 0;
            if ($app_user) {
                $is_bind = $wechatInfo->getAppId($app_user->id);
            }
            if ($is_bind) {
                return $this->getInfoResponse('4009', '该账号已经被其他微信绑定');
            }
            if (!$app_user) {
                throw new ApiException('您从未注册过我们系统！', '4004');
            } else {
                $wechat_info = $wechatInfo->updateById($arrRequest['last_step_id'], $app_user->id);
                if (!empty($arrRequest['user_name']) && empty($app_user->user_name)) {
                    $appUserInfo->updateUserInfoWithIM($app_user->id, $arrRequest['user_name']);
                }
                if (!empty($arrRequest['user_name'])) {
                    $wechatInfo->where(['id' => $arrRequest['last_step_id'], 'app_id' => $app_user->id])->update([
                        'nickname' => $arrRequest['user_name'],
                    ]);
                }
                if (!empty($arrRequest['ico_image'])) {
                    $wechatInfo->where(['id' => $arrRequest['last_step_id'], 'app_id' => $app_user->id])->update([
                        'headimgurl' => $arrRequest['ico_image']
                    ]);
                }
                if (!empty($arrRequest['ico_image']) && empty($app_user->avatar)) {
                    $appUserInfo->updateUserInfoAvatar($app_user->id, $arrRequest['ico_image']);
                }
            }
            $res_login = $wechat->loginApp($app_user->phone, '123456', 1, $client, $rsaUtils, $arrRequest['device_id']);
            DB::commit();
            return $this->getResponse($res_login);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /*
     * H5权限配置验证
     */
    public function rightVerify(Request $request, Wechat $wechat)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'url' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            /***********************************/
            $client = new Client();
            if (!Cache::has('h5_access_token_h5')) {
                $access_token = $wechat->getAccessToken($client);
                if (array_key_exists('access_token', $access_token)) {
                    Cache::put('h5_access_token_h5', @$access_token['access_token'], 120);
                } else {
                    return $this->getInfoResponse('1001', @$access_token['errmsg']);
                }
            }
            $access_token = Cache::get('h5_access_token_h5');
            if (!Cache::has('h5_jsapi_ticket_h5')) {
                $jsapi_ticket = $wechat->getJsapiTicket($access_token, $client);
                if (array_key_exists('ticket', $jsapi_ticket)) {
                    Cache::put('h5_jsapi_ticket_h5', @$jsapi_ticket['ticket'], 120);
                } else {
                    return $this->getInfoResponse('1002', @$jsapi_ticket['errmsg']);
                }
            }
            $jsapi_ticket = Cache::get('h5_jsapi_ticket_h5');
            $noncestr = uniqid();
            $timestamp = time();
            @$arr_parameter = [
                'noncestr' => $noncestr,         #随机字符串
                'jsapi_ticket' => $jsapi_ticket, #有效的jsapi_ticket,
                'timestamp' => $timestamp,       #响应格式 默认json
                'url' => $arrRequest['url'],     #当前网页的URL，不包含#及其后面部分
            ];
            $p = ksort($arr_parameter);
            if ($p) {
                $str = '';
                foreach ($arr_parameter as $k => $val) {
                    $str .= $k . '=' . $val . '&';
                }
            }
            $str1 = trim($str, '&');
            $signature = sha1($str1);

            $data = [
                'timestamp' => $timestamp,
                'nonceStr' => $noncestr,
                'signature' => $signature,
            ];
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
