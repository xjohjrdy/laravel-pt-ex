<?php

namespace App\Http\Controllers\Growth;


use App\Exceptions\ApiException;
use App\Services\Growth\UserIncome;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserInComeController extends Controller
{
    //
    public function userMaidDetail(Request $request)
    {
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'timestamp' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $userMoneyService = new UserIncome($arrRequest['app_id'], $arrRequest['timestamp']);
            return $this->getResponse($userMoneyService->getCurrentMonthInfo());
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }


}
