<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\App\CircleRing;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MatchRingController extends Controller
{
    //
    public function matchByUser(Request $request){
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circleRingModel = new CircleRing();
            $circleRing = $circleRingModel->where(['app_id' => $arrRequest['app_id']])->exists();
            $data = $circleRing ? 1 : 0;
            return $this->getResponse($data);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }
}
