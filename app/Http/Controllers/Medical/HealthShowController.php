<?php

namespace App\Http\Controllers\Medical;

use App\Exceptions\ApiException;
use App\Services\ChunYuDoctor\ChunYuDoctor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HealthShowController extends Controller
{
    /*
     * 获取指定链接
     */
    public function getUrl(Request $request, ChunYuDoctor $chunYuDoctor)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];
            $app_id = $chunYuDoctor->encodeId($app_id);
            $res = [
                'url' => 'https://wxauth.yihu.com/apiweb/tp.html?point=jkgjqdb&thirdPartyUserId=' . $app_id . '&appId=9000825&sourceType=1'
            ];

            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
