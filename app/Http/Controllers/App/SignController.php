<?php

namespace App\Http\Controllers\App;

use App\Entitys\App\AdvertisementClickOnly;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\SignLog;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SignController extends Controller
{
    /**
     * Interceptor
     */
    public function checkSign(Request $request, AdvertisementClickOnly $advertisementClickOnly)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'mac_ip' => 'required',
                'ip' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $now_time = strtotime(date('Y-m-d', time()));

            $obj_sign = new SignLog();
            if ($obj_sign->isCheck($arrRequest['app_id'])) {
                return $this->getInfoResponse('5003', '今天已签到，已获得10元报销额度！');
            }

            $is_mac = $advertisementClickOnly->where([
                'mac_ip' => $arrRequest['mac_ip'],
                'time' => $now_time,
            ])->count();

            if ($is_mac >= 200) {
//                return $this->getInfoResponse('5441', '当前机器签到次数过多，已拦截！');
                return $this->getResponse([
                    'sign_status' => 0,
//                    'sign_img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/kaifazhong/app-h5/images/-s-%E7%BB%844@2x.png',
                    'sign_img' => 'http://cdn01.36qq.com/CDN/3RUDdUf8XDK5zFneSSY5pM6V3RwCZP.png',
                ]);
            }

            $is_f = $advertisementClickOnly->where([
                'app_id' => $arrRequest['app_id'],
                'time' => $now_time,
            ])->first();

            if (empty($is_f)) {
                $advertisementClickOnly->addRecord([
                    'app_id' => $arrRequest['app_id'],
                    'mac_ip' => $arrRequest['mac_ip'],
                    'ip' => $arrRequest['ip'],
                    'time' => $now_time,
                ]);
            }

            return $this->getResponse([
                'sign_status' => 1,
//                'sign_img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/kaifazhong/app-h5/images/-s-%E7%BB%844@2x.png',
                'sign_img' => 'http://cdn01.36qq.com/CDN/3RUDdUf8XDK5zFneSSY5pM6V3RwCZP.png',
            ]);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 注销用户
     */
    public function deleteUser(Request $request, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'phone' => 'required',
                'code' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $phone = $arrRequest['phone'];
            $code = $arrRequest['code'];
            if (!Cache::has($phone)) {
                return $this->getInfoResponse('4004', '手机不存在验证码！');
            }
            $r_code = Cache::get($arrRequest['phone']);
            if ($r_code != $code) {
                return $this->getInfoResponse('4000', '验证码错误或过期，请重新获取！');
            }

            $user_info = $appUserInfo->getUserInfo($arrRequest['app_id']);

            $user_next = $appUserInfo->where(['parent_id' => $arrRequest['app_id']])->first();

            if (empty($user_info->parent_id) && empty($user_next)) {
                $appUserInfo->where(['id' => $arrRequest['app_id']])->delete();
            } else {
                $appUserInfo->where(['id' => $arrRequest['app_id']])->update(['status' => 2]);
            }


            return $this->getResponse('您的账号已成功注销了，没我的日子您要照顾好自己，有缘再会咯！！');

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
