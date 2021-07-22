<?php


namespace App\Http\Controllers\Other;


use App\Entitys\App\MallUserInfoPass;
use App\Entitys\Other\HarryAgreementOut;
use App\Entitys\Other\MallUserInfoPassOther;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeUser;
use App\Entitys\Other\ThreeUserGet;
use App\Exceptions\ApiException;
use App\Extend\Random;
use App\Http\Controllers\Controller;
use App\Services\HarryPayOut\Harry;
use App\Services\HarryPayOut\RsaHarry;
use App\Services\Qmshida\GongMallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GongMallController extends Controller
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
            $evService = new GongMallService();
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
                "requestId" => Random::alnum(32),//Random::alnum(32), // 取记录的唯一ID
//                "mobile" => "15980271371",
//                "name" => "陈政航",
//                "identity" => "350322199210085213",
//                "amount" => 0.01,
//                "bankAccount" => "6212261402043016440",
//                "remark" => "提现测试",
//                "requestId" => 4
            ];
            $evService = new GongMallService();
            $res = $evService->doWithdraw($params);
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
                "requestId" => 'VqUgasdvw1BiFjlxJW3X5CxxQ4erc'//Random::alnum(32), // 取记录的唯一ID
            ];
            $evService = new GongMallService();
            $res = $evService->getTaxInfo($params);
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
            $evService = new GongMallService();
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
                "requestId" => '55', // 取记录的唯一ID
            ];
            $evService = new GongMallService();
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

        $evService = new GongMallService();
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
     * 获取电签的银行卡号
     */
    public function getIdentifyInfo(Request $request)
    {
        try {
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

            $evService = new GongMallService();
            $mallOtherModel = new MallUserInfoPassOther();
            $mallInfo = $mallOtherModel->where(['app_id' => $workNumber])->first();
            if (empty($mallInfo)) {
                $res = $evService->getContractStatus($workNumber);
                $res = json_decode($res, true);
                if ($res['success']) {
                    $params = $res['data'];
                    $extraPram = '';
                    if (!empty($params['extraParam'])) {
                        $extraPram = $params['extraParam'];
                    }
                    $mallOtherModel->create([
                        'app_id' => $params['workNumber'],
                        'name' => $params['name'],
                        'mobile' => $params['mobile'],
                        'identity' => $params['identity'],
                        'status' => $params['status'],
                        'workNumber' => $params['workNumber'], // 0
                        'extraParam' => $extraPram, // 0
                        'salaryAccount' => $params['bankAccount'],
                        'bankName' => $params['bankName'],
                    ]);
                    $mallInfo = $mallOtherModel->where(['app_id' => $workNumber])->first();
                }
            }
            return $this->getResponse($mallInfo);


        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
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
            $mallModel = new MallUserInfoPassOther();
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
     * 提现结果回调 工猫（已废除）
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawCallback(Request $request)
    {
        try {
            DB::connection('db001')->beginTransaction();
            $params = Input::all();
            $threeUserGet = new ThreeUserGet();
            $threeUserModel = new ThreeUser();
            $changeUserLog = new ThreeChangeUserLog();
            $this->log($params);
//            $request_id = '1-20200309142651';
            $id = $params['requestId'];
            $apply_cash = $threeUserGet->where([
                'id' => $id
            ])->first();
            if(empty($apply_cash)){
                $this->log('未查找到提现记录:' . $id);
            }

            if($params['status'] == 1){ // 提现成功
                $apply_cash->update([
                    'type' => 1,
                    'update_time' => $params['timestamp'],
                    'error_info' => '',
                    'reason' => ''
                ]);
            } else { // 提现失败
                $this->log('提现失败:' . $id);
                $apply_cash->update([
                    'type' => 2,
                    'update_time' => $params['timestamp'],
                    'error_info' => $params['failReason'],
                    'reason' => '系统升级！3个工作日后可重新发起提现申请！'
                ]);
                $three_user = $threeUserModel->getUser($apply_cash['app_id']);
                $money = $apply_cash['money'] + $apply_cash['manage_fee'];
                $changeUserLog->create([
                    'app_id' => $apply_cash['app_id'],
                    'before_money' => $three_user->money,
                    'before_next_money' => $money,
                    'after_money' => ($three_user->money + $money),
                    'from_type' => 6,
                    'from_info' => 'FUWD',
                ]);
                $threeUserModel->addMoney($apply_cash['app_id'], $money);
            }

//            $this->log($params);
            DB::connection('db001')->commit();
            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            DB::connection('db001')->rollBack();
            $this->log($e->getMessage());
            return response()->json([
                'success' => false,
            ]);
        }
    }


    /**
     * 众薪 提现 支付宝
     * @param Request $request
     */
    public function harryWithdraw(Request $request)
    {
        try {
            $params = $request->input();
            $app_id = $params['app_id'];
            $ali_account = $params['account'];
            $money = 1; // 单位分
            $out_order_id = 'harry_' . time();
            $harryService = new Harry();
            $harryEntityModel = new HarryAgreementOut();
            $harry_info = $harryEntityModel->where(['app_id' => $app_id])->first();
            $name = $harry_info['name'];
            $mobile = $harry_info['phone'];
            $certificate = $harry_info['identityId'];
            $res = $harryService->push($name, $mobile, $certificate, $out_order_id, $money, $ali_account);
            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }


    /**
     * 众薪 提现银行卡
     * @param Request $request
     */
    public function harryWithdraw2Bank(Request $request)
    {
        try {
            $params = $request->input();
            $app_id = $params['app_id'];
            $ali_account = $params['account'];
            $money = 1; // 单位分
            $out_order_id = 'harry_' . time();
            $harryService = new Harry();
            $harryEntityModel = new HarryAgreementOut();
            $harry_info = $harryEntityModel->where(['app_id' => $app_id])->first();
            $name = $harry_info['name'];
            $mobile = $harry_info['phone'];
            $certificate = $harry_info['identityId'];
            $res = $harryService->cardPush($name, $mobile, $certificate, $out_order_id, $money, $ali_account);
            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /**
     * 众薪，提现结果回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function harryWithdrawCallback(Request $request)
    {
        try {
//            DB::connection('db001')->beginTransaction();
            $params = Input::all();
            $threeUserGet = new ThreeUserGet();
            $threeUserModel = new ThreeUser();
            $changeUserLog = new ThreeChangeUserLog();
            $web_rsa = new RsaHarry();
            $res = $web_rsa->private_decrypt($params['sign']);
            $res = json_decode($res, true);
            $this->harryLog($res);
            /**
             * {
                "actualAmount": 1,
                "additionalCharge": 0,
                "cardAttribute": "C",
                "cardType": "DC",
                "certificateNo": "352227198704131818",
                "charset": "UTF-8",
                "companyCharge": 0,
                "companyServiceFee": 0,
                "companyTax": 0,
                "createTime": "2020-03-19 15:15:17",
                "endTime": "2020-03-19 15:20:11.0",
                "mobile": "18695790413",
                "name": "卞贤铃",
                "noticeNum": "0",
                "notifyUrl": "http://pt.qmshidai.com/api/harry_withdraw_callback",
                "orderNo": "20200319151516995308178083840",
                "outMemberNo": "1239451943400300544",
                "outerOrderNo": "harry_1584602116",
                "payAccount": "6217001820028709537",
                "payType": "1",
                "personServiceFee": 0,
                "predictAmount": 1,
                "projectName": "余额提现（银行卡）",
                "salaryType": "0",
                "service": "bpotop.zx.pay.order",
                "serviceCharge": 0,
                "signType": "RSA",
                "status": "1",
                "taxFee": 0,
                "version": "1.1"
            }
             *
             *
             * {
                "actualAmount": 1,
                "additionalCharge": 0,
                "cardAttribute": "C",
                "cardType": "DC",
                "certificateNo": "352227198704131818",
                "charset": "UTF-8",
                "companyCharge": 0,
                "companyServiceFee": 0,
                "companyTax": 0,
                "createTime": "2020-03-19 15:03:40",
                "endTime": "2020-03-19 15:07:01.0",
                "errorCode": "F9999",
                "errorMsg": "收款行联行号不正确",
                "mobile": "18695790413",
                "name": "卞贤铃",
                "noticeNum": "0",
                "notifyUrl": "http://pt.qmshidai.com/api/harry_withdraw_callback",
                "orderNo": "20200319150340004384785940481",
                "outMemberNo": "1239451943400300544",
                "outerOrderNo": "harry_1584601419",
                "payAccount": "352227198704131818",
                "payType": "1",
                "personServiceFee": 0,
                "predictAmount": 1,
                "projectName": "余额提现（银行卡）",
                "salaryType": "0",
                "service": "bpotop.zx.pay.order",
                "serviceCharge": 0,
                "signType": "RSA",
                "status": "2",
                "taxFee": 0,
                "version": "1.1"
                }
             */
            if(!empty($res['status'])){
                $msg = '';
                $status = $res['status'];
                $order_id = $res['outerOrderNo'];
                $apply_cash = $threeUserGet->where([
                    'order_id' => $order_id
                ])->whereIn('type', [0, 4])->first();
                if(empty($apply_cash)){
                    return 'success';
                }
                $id = $apply_cash['id'];
                DB::connection('db001')->beginTransaction();
                #todo 交易成功
                if($status == 1) {
                    $apply_cash->where(['id' => $id])->update([
                        'type' => 1,
                        'update_time' => time(),
                        'error_info' => '',
                        'reason' => ''
                    ]);
                }
                #todo 交易失败
                if($status == 2) {
                    $errorMsg = $res['errorMsg'];
                    $reason = '';
                    if($apply_cash['from_type'] == '1'){
                        $reason = '请核实支付宝账号与收款人姓名是否一致，如果确认一致，可能是因为收款的支付宝账户号不是注册支付宝账号时绑定的手机号或者邮箱号，建议尝试更换一下收款支付宝账号（更换为手机号或者邮箱号）！或重新申请银行卡提现。';
                    }
                    if($apply_cash['from_type'] == '2'){
                        $reason = '请核实收款银行卡号是否正确，收款银行卡号是否是本人的银行卡号，或尝试使用支付宝提现。';
                    }
                    if($errorMsg == '验签失败，余额不足'){
                        $reason = '提现升级，请重新申请提现，审核通过后3个工作日内到账';
                    }
                    $apply_cash->where(['id' => $id])->update([
                        'type' => 2,
                        'update_time' => time(),
                        'error_info' => $errorMsg,
                        'reason' => $reason
                    ]);
                    $three_user = $threeUserModel->getUser($apply_cash['app_id']);
                    $money = $apply_cash['money'] + $apply_cash['manage_fee'];
                    $changeUserLog->create([
                        'app_id' => $apply_cash['app_id'],
                        'before_money' => $three_user->money,
                        'before_next_money' => $money,
                        'after_money' => ($three_user->money + $money),
                        'from_type' => 6,
                        'from_info' => 'FUWD',
                    ]);
                    $threeUserModel->addMoney($apply_cash['app_id'], $money);
                }
            }

            DB::connection('db001')->commit();
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            DB::connection('db001')->rollBack();
            $this->harryLog($e->getMessage());
        }
        return 'success';
    }
    /**
     * 支付记录日志
     */
    private function log($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/EVisa/w' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }

    /**
     * 支付记录日志
     */
    private function harryLog($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/harry/' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }
}
