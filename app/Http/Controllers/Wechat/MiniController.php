<?php


namespace App\Http\Controllers\Wechat;


use App\Entitys\App\AppUserInfo;
use App\Entitys\App\MiniWechatInfo;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\JPush\JPush;
use App\Services\Wechat\MiniAuth;
use App\Services\Wechat\MiniQrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class MiniController extends Controller
{
    public function code2UserInfo(Request $request, AppUserInfo $appUserInfo, MiniWechatInfo $miniWechatInfo, MiniAuth $miniAuth)
    {
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'code' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $result = $miniAuth->authCode2Session($arrRequest['code']);
//            $result = [ 'openid' => 'oePV45F-qrkSP-AYjL9CZhWmvmvA'];
            if (!empty($result['openid'])) {
                $mini_model = [
                    'openid' => $result['openid'],
//                    'session_key' => @$result['session_key'],
                    'unionid' => @$result['unionid'],
                    'app_id' => 0
                ];
                $mini_info = $miniAuth->checkAppIdAuth($mini_model); // 判断是否已经绑定过，如果绑定直接返回appId
                if ($mini_info['app_id'] != 0) {
                    $app_user = $appUserInfo->getUserById($mini_info['app_id']);
                    $data = [
                        'id' => $app_user->id,
                        'user_name' => $app_user->user_name,
                        'real_name' => $app_user->real_name,
                        'avatar' => $app_user->avatar,
                        'phone' => $app_user->phone,
                        'alipay' => $app_user->alipay,
                        'parent_id' => $app_user->parent_id,
                    ];
                    $mini_info['user_info'] = $data;
                }
                return $this->getResponse($mini_info);
            } else {
                return $this->getInfoResponse($result['errcode'], $result['errmsg']);
            }
        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }
    }

    /*
     * 用户注册
     */
    public function miniRegister(Request $request, AppUserInfo $appUserInfo, MiniWechatInfo $miniWechatInfo)
    {
//        return $this->getInfoResponse('1001', '系统全新升级，暂停注册！预计1号升级完成。');
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'phone' => 'required',
                'password' => 'required',
                'confirm_password' => 'required',
                'sms_code' => 'required',
                'open_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $phone = $arrRequest['phone'];
            $password = $arrRequest['password'];
            $confirm_password = $arrRequest['confirm_password'];
            $sms_code = $arrRequest['sms_code'];
            $arrRequest['parent_id'] = empty($arrRequest['parent_id']) ? 0 : $arrRequest['parent_id'];
            /***********************************/
            //判断输入父级是否为33进制
            if (strpos($arrRequest['parent_id'], 'x') === 0) {
                $arrRequest['parent_id'] = base_convert(substr($arrRequest['parent_id'], 1), 33, 10);
            }
            if (!is_numeric($arrRequest['parent_id'])) {
                return $this->getInfoResponse('1008', '偶像id输入错误！');
            }

            $pattern_account = '/^\d{4,20}$/i';

            if (!preg_match($pattern_account, $phone)) {
                return $this->getInfoResponse('1001', '您的手机号输入错误！');
            }
            $user_info = $appUserInfo->where(['phone' => $phone])->first();
            if (!empty($user_info)) {
                return $this->getInfoResponse('1002', '该手机号已被注册！');
            }
            if (!($password == $confirm_password)) {
                return $this->getInfoResponse('1003', '两次输入的密码不一致！');
            }
            if (!Cache::has($phone)) {
                return $this->getInfoResponse('1004', '手机验证码不存在！');
            }
            $r_code = Cache::get($phone);
            if ($r_code != $sms_code) {
                return $this->getInfoResponse('1005', '验证码错误或过期，请重新获取！');
            }
            if (!empty($arrRequest['parent_id'])) {
                $obj_idol = $appUserInfo->find($arrRequest['parent_id']);
                if (empty($obj_idol)) {
                    return $this->getInfoResponse('1006', '要创建的偶像不存在！');
                }
                $arrRequest['order_can_apply_amount'] = 100;
            }

            $mini = $miniWechatInfo->validOpenId($arrRequest['open_id']);
            if (empty($mini)) {
                return $this->getInfoResponse('1008', "无效的openId");
            }

            $res = $appUserInfo->addWith($arrRequest);
            $obj_user_data_by_id = $appUserInfo->find($res);
            if (!$res) {
                return $this->getInfoResponse('1007', '注册失败！');
            }
            $miniWechatInfo->relateAuthInfo($arrRequest['open_id'], $res); # 关联app_id 和小程序openId
            $data = [
                'id' => $obj_user_data_by_id->id,
                'user_name' => empty($obj_user_data_by_id->user_name) ? '' : $obj_user_data_by_id->user_name,
                'real_name' => empty($obj_user_data_by_id->real_name) ? '' : $obj_user_data_by_id->real_name,
                'avatar' => $obj_user_data_by_id->avatar,
                'phone' => $obj_user_data_by_id->phone,
                'alipay' => empty($obj_user_data_by_id->alipay) ? '' : $obj_user_data_by_id->alipay,
                'parent_id' => $obj_user_data_by_id->parent_id,
            ];
            if (!empty($arrRequest['parent_id'])) {
                JPush::push_user('您新增了一个粉丝！', $arrRequest['parent_id'], 1, 1, 3);
            }
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * 小程序登录
     * @param Request $request
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function login(Request $request, AppUserInfo $appUserInfo, MiniWechatInfo $miniWechatInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'phone' => 'required',
                'password' => 'required',
                'open_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $mini = $miniWechatInfo->validOpenId($arrRequest['open_id']);
            if (empty($mini)) {
                return $this->getInfoResponse('1008', "无效的openId");
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
            $miniWechatInfo->relateAuthInfo($arrRequest['open_id'], $app_user->id);
            $data = [
                'id' => $app_user->id,
                'user_name' => $app_user->user_name,
                'real_name' => $app_user->real_name,
                'avatar' => $app_user->avatar,
                'phone' => $app_user->phone,
                'alipay' => $app_user->alipay,
                'parent_id' => $app_user->parent_id,
            ];

//            $appUserInfo->updateUserLogin($app_user->id, $arrRequest['device_id']);

            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getMessage() . ',行' . $e->getLine(), '500');
        }
    }


    public function qrCode(Request $request, MiniQrCode $miniQrCode)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'page' => 'required',
                'scene' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $page = $arrRequest['page'];
            $scene = $arrRequest['scene'];
            $width = empty($arrRequest['width']) ? '300px' : $arrRequest['width'];

            $res = $miniQrCode->getQrCode($scene, $page, $width);

            $arr_res = json_decode($res, true);

            if (isset($arr_res['errcode'])) {
                return $this->getInfoResponse($arr_res['errcode'], $arr_res['errmsg']);
            }

            return $this->getResponse('data:image/png;base64,' . base64_encode($res));


        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }
    }
}