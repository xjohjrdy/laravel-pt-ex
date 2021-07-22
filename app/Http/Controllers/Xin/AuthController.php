<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\App\AppUserInfo;
use App\Entitys\Xin\Poster;
use App\Entitys\Xin\WebShow;
use App\Exceptions\ApiException;
use App\Services\JPush\JPush;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /*
     * 用户注册
     */
    public function register(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'phone' => 'required',
                'password' => 'required',
                'confirm_password' => 'required',
                'sms_code' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            if (!in_array($arrRequest['phone'], [15736970970, 18650397240, 19959468988, 18459153289, 17689434155])) {
                //return $this->getInfoResponse('3003', '内测期间，注册暂停开放，请耐心等待开放！');
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
            //if ($user_info->status == 2) {
                // return $this->getInfoResponse('4014', '该用户已被注销！');
            // }
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
            $res = $appUserInfo->addWith($arrRequest);
            $obj_user_data_by_id = $appUserInfo->find($res);
            if (!$res) {
                return $this->getInfoResponse('1007', '注册失败！');
            }
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

    /*
     * 用户注册 H5页面用
     */
    public function registerH5(Request $request, AppUserInfo $appUserInfo)
    {
		return $this->getInfoResponse('4014', '该用户已被注销！');
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'phone' => 'required',
                'password' => 'required',
                'confirm_password' => 'required',
                'sms_code' => 'required',
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
            $pattern_account = '/^\d{4,20}$/i';
            if (!preg_match($pattern_account, $phone)) {
                return $this->getInfoResponse('1001', '您的手机号输入错误！');
            }
            $user_info = $appUserInfo->where(['phone' => $phone])->first();
            if (!empty($user_info)) {
                return $this->getInfoResponse('1002', '该手机号已被注册！');
            }
            if ($user_info->status == 2) {
                return $this->getInfoResponse('4014', '该用户已被注销！');
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
            $res = $appUserInfo->addWith($arrRequest);
            $obj_user_data_by_id = $appUserInfo->find($res);
            if (!$res) {
                return $this->getInfoResponse('1007', '注册失败！');
            }
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

    /*
     * 激活用户
     */
    public function activeUser(Request $request, AppUserInfo $appUserInfo)
    {
		return $this->getInfoResponse('1005', '激活失败');
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'phone' => 'required',
                'password' => 'required',
                'sms_code' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $phone = $arrRequest['phone'];
            $password = $arrRequest['password'];
            $sms_code = $arrRequest['sms_code'];
            /***********************************/
            if (!Cache::has($phone)) {
                return $this->getInfoResponse('1001', '手机验证码不存在！');
            }
            $r_code = Cache::get($phone);
            if ($r_code != $sms_code) {
                return $this->getInfoResponse('1002', '验证码错误或过期，请重新获取！');
            }
            $arr_binding_res = $appUserInfo->isBindingByPhone($phone);
            if (empty($arr_binding_res)) {
                return $this->getInfoResponse('1003', '手机号码未绑定任何用户');
            }
            foreach ($arr_binding_res as $user) {
                $arr[$user->id] = $appUserInfo->where(['parent_id' => $user->id])->count('id');
            }
            arsort($arr);
            $key_arr = array_keys($arr);
            $user_id = $key_arr[0];
            $user_model = $arr_binding_res[$user_id];
            if ($user_model->status != 3) {
                return $this->getInfoResponse('1004', '该用户不需要激活');
            }
            $res = $appUserInfo->activeUserById($user_model->id, $password);
            if (empty($res)) {
                return $this->getInfoResponse('1005', '激活失败');
            }
            return $this->getResponse('激活成功');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 分享注册页
     */
    public function shareRegister(Poster $poster)
    {
        return '系统全新升级，暂停注册！预计1号升级完成。';
        $parent_id = \request()->input('id');
        $poster_id = \request()->input('poster_id');
        $obj_poster = $poster->find($poster_id);
        return view('xin.share_register', [
            'parent_id' => $parent_id,
            'poster' => $obj_poster,
        ]);
    }

    /*
   * 分享注册页
   */
    public function shareRegisterNew(Poster $poster, AppUserInfo $appUserInfo)
    {
//        return '系统全新升级，暂停注册！预计1号升级完成。';
        $parent_id = \request()->input('id');
        $poster_id = \request()->input('poster_id');
        $obj_poster = $poster->find($poster_id);

        //33转10进制
        if (strpos($parent_id, 'x') === 0) {
            $parent_id = base_convert(substr($parent_id, 1), 33, 10);
        }

        $user_info = $appUserInfo->getUserById($parent_id);
        if (empty($user_info)) {
            return '该用户不存在！';
        }

        $new_app_id = $parent_id;
        if ($parent_id >= 10000000) {
            $new_app_id = base_convert($parent_id, 10, 33); // 10 转 33
            $new_app_id = 'x' . $new_app_id;
        }

        return view('xin.share_register_new', [
            'parent_id' => $parent_id,
            'poster' => $obj_poster,
            'user_name' => $user_info->user_name,
            'avatar' => $user_info->avatar,
            'show_id' => $new_app_id,
        ]);
    }

    /**
     * 获取用户资料
     * @param Request $request
     * @param Poster $poster
     * @param AppUserInfo $appUserInfo
     * @return \Illuminate\Http\JsonResponse|string
     * @throws ApiException
     */
    public function shareRegisterNewInfo(Request $request, Poster $poster, AppUserInfo $appUserInfo)
    {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }//仅用于测试兼容旧版-----------------线上可删除
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $parent_id = $arrRequest['id'];

        if (empty($arrRequest['poster_id'])) {
            $obj_poster = null;
        } else {
            $obj_poster = $poster->find($arrRequest['poster_id']);
        }


        $user_info = $appUserInfo->getUserById($parent_id);
        if (empty($user_info)) {
            return '该用户不存在！';
        }

        $new_app_id = $parent_id;
        if ($parent_id >= 10000000) {
            $new_app_id = base_convert($parent_id, 10, 33); // 10 转 33
            $new_app_id = 'x' . $new_app_id;
        }

        return $this->getResponse([
            'parent_id' => $parent_id,
            'poster' => $obj_poster,
            'user_name' => $user_info->user_name,
            'avatar' => $user_info->avatar,
            'show_id' => $new_app_id,
        ]);

    }

    /*
     * 展示用户协议
     */
    public function userAgreement(WebShow $webShow)
    {
        $url = 'https://a119112.oss-cn-beijing.aliyuncs.com/%E5%8D%9E%E8%B4%A4%E9%93%83/%E9%9D%99%E6%80%81%E9%A1%B5%E9%9D%A2/pages/xin_user_agreementr.html';
        return "<script>
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";

//        $model = $webShow->find(2);
//        return view('xin.user_agreement', [
//            'data' => $model,
//        ]);
    }

    /*
    * 网页用户注册
    */
    public function doRegister(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = $request->toArray();
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true) ?? $arrRequest;
            $token = request()->header('token');
            if (empty($token)) {
                $phone = $arrRequest['phone'];
                $password = $arrRequest['password'];
                $confirm_password = $arrRequest['confirm_password'];
                $sms_code = $arrRequest['sms_code'];
                $parent_id = $arrRequest['parent_id'];

                //判断输入父级是否为33进制
                if (strpos($parent_id, 'x') === 0) {
                    $parent_id = base_convert(substr($parent_id, 1), 33, 10);
                }
                if (!is_numeric($parent_id)) {
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
            } else {
                $data = $arrRequest;
                sort($data);
                $post_token = md5('pt' . serialize($data) . 'llq');
                if ($token != $post_token) {
                    return json(['code' => 400, 'message' => '注册失败']);
                } else {
                }
            }


            /***********************************/
            if (!empty($parent_id)) {
                $obj_idol = $appUserInfo->find($parent_id);
                if (empty($obj_idol)) {
                    return $this->getInfoResponse('1006', '要创建的偶像不存在！');
                }
                $arrRequest['order_can_apply_amount'] = 100;
            }
            $res = $appUserInfo->addWith($arrRequest);
            if (!$res) {
                return $this->getInfoResponse('1007', '注册失败！');
            }
            $data = [
                'code' => 200,
                'parent_id' => empty($arrRequest['parent_id']) ? 0 : (int)$arrRequest['parent_id'],
                'msg' => '注册成功',
            ];
            if (!empty($arrRequest['parent_id'])) {
                @JPush::push_user('您新增了一个粉丝！', $arrRequest['parent_id'], 1, 1, 3);
            }
            return $data;
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
