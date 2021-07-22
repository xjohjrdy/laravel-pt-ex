<?php

namespace App\Http\Controllers\Mini;

use App\Entitys\Ad\AdUserInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /*
     * 得到用户等级身份
     */
    public function getGroupId(Request $request)
    {
        try {
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

            $groupid = AdUserInfo::where(['pt_id' => $app_id])->value('groupid');

            return $this->getResponse((string)$groupid);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
