<?php

namespace App\Http\Controllers\EVisa;

use App\Entitys\App\MallUserInfoPass;
use App\Entitys\Other\ThreeUser;
use App\Entitys\Other\ThreeUserGet;
use App\Exceptions\ApiException;
use App\Extend\Random;
use App\Services\EVisa\EVisaServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    /**
     * 根据信息获取电签H5地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateIdentify(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'name' => 'required',
                'mobile' => 'required',
                'idNumber' => 'required',
                'bankNum' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $name = $arrRequest['name'];
            $mobile = $arrRequest['mobile'];
            $certificateType = "1"; // 验证类型
            $idNumber = $arrRequest['idNumber'];
            $bankNum = $arrRequest['bankNum'];
            $workNumber = $arrRequest['app_id'];
            $evService = new EVisaServices();
            $res = $evService->getEncryptionUrl($workNumber, $name, $mobile, $certificateType, $idNumber, $bankNum);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 提现
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function doWithdraw(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
//                'name' => 'required',
//                'amount' => 'required',
//                'bankAccount' => 'required',
//                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
//            $name = empty($arrRequest['name']) ? '' : $arrRequest['name'];
//            $remark = empty($arrRequest['remark']) ? '' : $arrRequest['remark'];
//            $mobile = empty($arrRequest['mobile']) ? '' : $arrRequest['mobile'];
//            $mobile = $arrRequest['mobile'];
//            $identity = $arrRequest['idNumber'];
//            $amount = $arrRequest['amount'];
//            $bankAccount = $arrRequest['bankAccount'];
            $params = [
                "mobile" => '15980271371',
                "name" => "陈政航",
                "identity" => "350322199210085213",
                "amount" => 0.01,
                "bankAccount" => "6212261402043016440",
                "remark" => '提现测试',
                "requestId" => 'VqUgSOkz2vw1BiFjlxJW3X5CyIaQ4erb'//Random::alnum(32), // 取记录的唯一ID
            ];
            $evService = new EVisaServices();
            $res = $evService->doWithdraw($params);
            $this->log($res);
            $res = json_decode($res, true);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * 提现
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaxInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
//                'name' => 'required',
//                'amount' => 'required',
//                'bankAccount' => 'required',
//                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
//            $name = empty($arrRequest['name']) ? '' : $arrRequest['name'];
//            $remark = empty($arrRequest['remark']) ? '' : $arrRequest['remark'];
//            $mobile = empty($arrRequest['mobile']) ? '' : $arrRequest['mobile'];
//            $mobile = $arrRequest['mobile'];
//            $identity = $arrRequest['idNumber'];
//            $amount = $arrRequest['amount'];
//            $bankAccount = $arrRequest['bankAccount'];
            $params = [
                "mobile" => '15980271371',
                "name" => "陈政航",
                "identity" => "350322199210085213",
                "amount" => 0.01,
                "bankAccount" => "6212261402043016440",
                "remark" => '提现测试',
                "requestId" => 'VqUgSOkz2vw1BiFjlxJW3X5CyIaQ4erb'//Random::alnum(32), // 取记录的唯一ID
            ];
            $evService = new EVisaServices();
            $res = $evService->getTaxInfo($params);
            $this->log($res);
            $res = json_decode($res, true);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * 查询企业可提金额
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyBalance(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
//                'name' => 'required',
//                'amount' => 'required',
//                'bankAccount' => 'required',
//                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
//            $name = empty($arrRequest['name']) ? '' : $arrRequest['name'];
//            $remark = empty($arrRequest['remark']) ? '' : $arrRequest['remark'];
//            $mobile = empty($arrRequest['mobile']) ? '' : $arrRequest['mobile'];
//            $mobile = $arrRequest['mobile'];
//            $identity = $arrRequest['idNumber'];
//            $amount = $arrRequest['amount'];
//            $bankAccount = $arrRequest['bankAccount'];
//            $params = [
//                "mobile" => '15980271371',
//                "name" => "陈政航",
//                "identity" => "350322199210085213",
//                "amount" => 0.01,
//                "bankAccount" => "6212261402043016440",
//                "remark" => '提现测试',
//                "requestId" => 'VqUgSOkz2vw1BiFjlxJW3X5CyIaQ4erb'//Random::alnum(32), // 取记录的唯一ID
//            ];
            $evService = new EVisaServices();
            $res = $evService->checkCompanyMoney([]);
            $res = json_decode($res, true);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 查询提现记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWithDrawList(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
//                'name' => 'required',
//                'amount' => 'required',
//                'bankAccount' => 'required',
//                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
//            $name = empty($arrRequest['name']) ? '' : $arrRequest['name'];
//            $remark = empty($arrRequest['remark']) ? '' : $arrRequest['remark'];
//            $mobile = empty($arrRequest['mobile']) ? '' : $arrRequest['mobile'];
//            $mobile = $arrRequest['mobile'];
//            $identity = $arrRequest['idNumber'];
//            $amount = $arrRequest['amount'];
//            $bankAccount = $arrRequest['bankAccount'];
            $params = [
//                "mobile" => '15980271371',
//                "name" => "陈政航",
//                "identity" => "350322199210085213",
//                "amount" => 0.01,
//                "bankAccount" => "6212261402043016440",
//                "remark" => '提现测试',
//                "requestId" => Random::alnum(32), // 取记录的唯一ID
                "requestId" => 'VqUgSOkz2vw1BiFjlxJW3X5CyIaQ4erb', // 取记录的唯一ID
            ];
            $evService = new EVisaServices();
            $res = $evService->getWithdrawResult($params);
//            $this->log($res);
            $res = json_decode($res, true);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 查询用户是否已经电签过
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkValidate(Request $request)
    {
//        try {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }//仅用于测试兼容旧版-----------------线上可删除
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $workNumber = $arrRequest['app_id'];

        $evService = new EVisaServices();
        $res = $evService->getContractStatus($workNumber);
        $res = json_decode($res, true);
        if ($res['success']) {
            return $this->getResponse($res['data']);
        } else {
            return $this->getInfoResponse(4461, $res['errorMsg']);
        }
//        } catch (\Throwable $e) {
//            //判断是否正常抛出异常
//            if (!empty($e->getCode())) {
//                throw new ApiException($e->getMessage(), $e->getCode());
//            }
//            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
//        }
    }


    /**
     * 电签回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        try {
            $params = Input::all();
            $mallModel = new MallUserInfoPass();
            $mall = $mallModel->where(['identity' => $params['identity']]);
            $extraPram = '';
            if (!empty($params['extraParam'])) {
                $extraPram = $params['extraParam'];
            }
            if ($mall->exists()) {
                $mallModel->where(['identity' => $params['identity']])->update([
                    'app_id' => $params['workNumber'],
                    'name' => $params['name'],
                    'mobile' => $params['mobile'],
                    'identity' => $params['identity'],
                    'status' => $params['status'],
                    'workNumber' => $params['workNumber'], // 0
                    'extraParam' => $extraPram, // 0
                    'salaryAccount' => $params['salaryAccount'],
                    'bankName' => $params['bankName'],
                ]);
            } else {
                $mallModel->create([
                    'app_id' => $params['workNumber'],
                    'name' => $params['name'],
                    'mobile' => $params['mobile'],
                    'identity' => $params['identity'],
                    'status' => $params['status'],
                    'workNumber' => $params['workNumber'], // 0
                    'extraParam' => $extraPram, // 0
                    'salaryAccount' => $params['salaryAccount'],
                    'bankName' => $params['bankName'],
                ]);
            }
//            $this->log($params);
            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            $this->log($e->getMessage());
            return response()->json([
                'success' => false,
            ]);
        }
    }

    /**
     * 提现结果回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawCallback(Request $request)
    {
        try {
            $params = Input::all();
            $threeUserGet = new ThreeUserGet();
            $threeUserModel = new ThreeUser();

            $this->log("回调成功");
            $this->log($params);
            $request_id = '1-20200309142651';
            $id = explode('-', $request_id)[0];
            $apply_cash = $threeUserGet->where([
                'id' => $id
            ])->first();
            if(empty($apply_cash)){
                $this->log('未查找到提现记录');
            }
            if($params['status'] == 1){ // 提现成功


            }

//            $this->log($params);

            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            $this->log($e->getMessage());
            return response()->json([
                'success' => false,
            ]);
        }
    }

    /**
     * 支付记录日志
     */
    private function log($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/EVisa/' . $date . '.txt', date('h:m:s') . '#### ' . var_export($msg , true). ' ####');
    }
}
