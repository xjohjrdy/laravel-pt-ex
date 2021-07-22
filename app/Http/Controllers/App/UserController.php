<?php

namespace App\Http\Controllers\App;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\SignLog;
use App\Exceptions\ApiException;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\CoinPlate\MainService;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\PutaoRealActive\PutaoRealActive;

class UserController extends Controller
{
    public function sign(Request $request)
    {
        try {
            DB::beginTransaction();

            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];
            $ip2long = sprintf("%u", ip2long($request->ip()));
            $obj_sign = new SignLog();
            if ($obj_sign->isCheck($app_id)) {
                return $this->getInfoResponse('5001', '您今天已经签过到了,请勿重复签到');
            }
            /*if ($obj_sign->isIp($ip2long)) {
                return $this->getInfoResponse('5002', '签到频率过快哦。');
            }*/

            $obj_sign->check($app_id, $ip2long);
            $obj_user = AppUserInfo::find($app_id);
            $obj_user->order_can_apply_amount += 10;
            $obj_user->sign_number += 1;
            $obj_user->save();
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
        DB::commit();

        //签到完成任务
        $coin_number = 0;
        if (!config('test_appid.debug') || in_array($app_id, config('test_appid.app_ids'))) {
            try {
                $MainService = new MainService($app_id, time());
                $coin_number = $MainService->coinSign();
            } catch (\Exception $e) {
                $coin_number = 0;
            }
        }
        switch ($coin_number) {
            case 10:
                $sign_coin_img = 'https://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/%E9%87%91%E5%B8%81%E4%B8%AD%E5%BF%83/10%E9%87%91%E5%B8%81.png';
                break;
            case 20:
                $sign_coin_img = 'https://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/%E9%87%91%E5%B8%81%E4%B8%AD%E5%BF%83/20%E9%87%91%E5%B8%81.png';
                break;
            case 30:
                $sign_coin_img = 'https://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/%E9%87%91%E5%B8%81%E4%B8%AD%E5%BF%83/30%E9%87%91%E5%B8%81.png';
                break;
            default:
                $sign_coin_img = 'http://cdn01.36qq.com/CDN/3RUDdUf8XDK5zFneSSY5pM6V3RwCZP.png';
        }

        return $this->getResponse(['order_can_apply_amount' => $obj_user->order_can_apply_amount, 'sign_coin_img' => $sign_coin_img]);
    }
}
