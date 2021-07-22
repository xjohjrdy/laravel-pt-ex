<?php

namespace App\Http\Controllers\StartPage;

use App\Entitys\App\CircleRing;
use App\Entitys\App\StartPageConfig;
use App\Entitys\App\StartPageDown;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class indexController extends Controller
{
    public function config(Request $request)
    {
        try {
            $model = new StartPageConfig();
            $data = $model->where(['id' => 1])->first(['is_open', 'url']);
            $request_device = $request->header('Accept-Device'); //设备类型
            if ($request_device == 'ios') {
                $adv_keys = [101334];
            } else {
                $adv_keys = [101333, 101147];
            }
            $data->adv_key = $adv_keys[array_rand($adv_keys)];
            $data->show_time = 3000; // 展示时间
            return $this->getResponse($data);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }


    public function down(Request $request)
    {
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'type' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $model = new StartPageDown();
            $model->addDown($arrRequest['type']);
            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }
}
