<?php

namespace App\Http\Controllers\Cash;

use App\Entitys\App\CashUser;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    /**
     * 获得当前用户选择的默认提现账号
     */
    public function getUserDefault(Request $request, CashUser $cashUser)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
//                'target' => 'required',
                'type' => 'required',//来源0：银行卡1：支付宝
//                'status' => 'required',
//                'jump' => 'required',//判断是更新类操作还是插入类，0：更新类，1插入类
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $user = $cashUser->where(['app_id' => $arrRequest['app_id'], 'status' => 1, 'type' => $arrRequest['type']])->first();

            return $this->getResponse($user);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 单个获取
     * @param Request $request
     * @param CashUser $cashUser
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getUserAll(Request $request, CashUser $cashUser)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
//                'target' => 'required',
                'type' => 'required',
//                'status' => 'required',
//                'jump' => 'required',//判断是更新类操作还是插入类，0：更新类，1插入类
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $user = $cashUser->where(['app_id' => $arrRequest['app_id']])->get();

            return $this->getResponse($user);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 插入或者更新
     */
    public function addOrChange(Request $request, CashUser $cashUser)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'target' => 'required',
                'type' => 'required',
                'status' => 'required',
//                'jump' => 'required',//判断是更新类操作还是插入类，0：更新类，1插入类
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $count = $cashUser->where(['app_id' => $arrRequest['app_id']])->count();
            if ($count >= 10) {
                return $this->getInfoResponse('5002', '最多只能添加10条，请修改或删除');
            }

            $is_get = $cashUser->where([
                'target' => $arrRequest['target'],
                'type' => $arrRequest['type'],
            ])->first();
            if (!empty($is_get)) {
                if ($is_get->app_id <> $arrRequest['app_id']) {
                    return $this->getInfoResponse('5002', '当前信息已经被其他用户绑定，无法操作！');
                }
            }


            if ($arrRequest['type'] == 0) {
                $bank_check = $cashUser->bank_check($arrRequest['target']);
                if (!$bank_check) {
                    return $this->getInfoResponse('5001', '银行卡号错误！');
                }
            }

            $res = $cashUser->addOrChange($arrRequest['app_id'], $arrRequest['target'], $arrRequest['type'], $arrRequest['status']);

            return $this->getResponse($res);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 删除对应信息
     */
    public function delUser(Request $request, CashUser $cashUser)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'cash_id' => 'required',
                'app_id' => 'required',
//                'type' => 'required',
//                'status' => 'required',
//                'jump' => 'required',//判断是更新类操作还是插入类，0：更新类，1插入类
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $user_c = $cashUser->where(['id' => $arrRequest['cash_id']])->first();

            if ($user_c->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('5002', '非本身用户记录，不允许删除');
            }

            $user = $cashUser->where(['id' => $arrRequest['cash_id']])->forceDelete();

            return $this->getResponse($user);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
