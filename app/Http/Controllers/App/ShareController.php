<?php

namespace App\Http\Controllers\App;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\MaterialTeacherLibrary;
use App\Entitys\App\UserWechatShow;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ShareController extends Controller
{

    public function getUserShareInfo(Request $request, MaterialTeacherLibrary $materialTeacherLibrary, AppUserInfo $appUserInfo, AdUserInfo $adUserInfo, UserWechatShow $userWechatShow)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'share_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $info_p = $materialTeacherLibrary->getOne($arrRequest['share_id']);
            $app_user = $appUserInfo->getUserById($arrRequest['app_id']);
            $ad_user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            $wecaht_info = $userWechatShow->getInfo($arrRequest['app_id']);
            if (empty($info_p)) {
                return $this->getInfoResponse('4004', '文章不存在！');
            }
            if (empty($app_user)) {
                return $this->getInfoResponse('4004', '用户不存在！');
            }
            if (empty($ad_user)) {
                return $this->getInfoResponse('4004', '用户不存在！！');
            }
            if (empty($wecaht_info)) {
                return $this->getInfoResponse('4004', '用户不存在！！！');
            }
            return $this->getResponse([
                'user_info' => [
                    'img' => $app_user->avatar,
                    'phone' => $app_user->phone,
                    'wechat' => $wecaht_info->wechat_info,
                    'name' => $app_user->real_name,
                    'level' => $ad_user->groupid,
                ],
                'article' => $info_p,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
