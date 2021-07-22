<?php

namespace App\Http\Controllers\EJiaYou;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class EJiaYouController extends Controller
{
    public function getUrl(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'userPhone' => 'required',
                'latitude' => 'required', //围堵
                'longitude' => 'required', //经度
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $user_phone = $arrRequest['userPhone'];
            $latitude = $arrRequest['latitude'];
            $longitude = $arrRequest['longitude'];

//            $url = 'https://api.ejiayou.com/pages/platform/soulList/index.html'; //生产url
            $url = 'https://dev.ejiayou.com/pages/platform/soulList/index.html'; //测试URL
            $plat = 'XZe9X';
            $app_key = 'cesJ5RccqdSRdciz';
            $app_secret = 'uOeNqX7GVreYBzpv';
            $timestamp = time();

            $sign = md5($app_key . "#" . $timestamp . "#" . $app_secret);

            $get_url = $url . "?plat={$plat}&userPhone={$user_phone}&sign={$sign}&timestamp={$timestamp}&latitude={$latitude}&longitude={$longitude}";

            return $this->getResponse($get_url);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }
}
