<?php

namespace App\Http\Controllers\Jd;

use App\Entitys\App\JdPhonePutIn;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PhoneController extends Controller
{
    /**
     * 拿到电话
     */
    public function getPhone(Request $request, JdPhonePutIn $jdPhonePutIn)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id_info = $jdPhonePutIn->getInfo($arrRequest['app_id']);

            if (!empty($app_id_info)) {
                return $this->getResponse($app_id_info);
            }

            return $this->getResponse(0);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * 填写电话
     */
    public function pushPhone(Request $request, JdPhonePutIn $jdPhonePutIn)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'phone' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if (strlen($arrRequest['phone']) <> 11) {
                return $this->getInfoResponse('4004', '您填写的手机号位数错误！');
            }

            $app_id_info = $jdPhonePutIn->getInfo($arrRequest['app_id']);
            $phone_info = $jdPhonePutIn->getInfoByPhone($arrRequest['phone']);
            if (!empty($app_id_info)) {
                return $this->getInfoResponse('5004', '您已经填写过手机号！');
            }
            if (!empty($phone_info)) {
                return $this->getInfoResponse('5003', '您填写的手机号重复！');
            }

            $jdPhonePutIn->pushIn([
                'app_id' => $arrRequest['app_id'],
                'phone' => $arrRequest['phone'],
            ]);

            return $this->getResponse('填写成功');

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }
}
