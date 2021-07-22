<?php

namespace App\Http\Controllers\Other;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\MallUserInfoPassOut;
use App\Entitys\Other\HarryAgreementOut;
use App\Entitys\Other\JdMaidOldOther;
use App\Entitys\Other\MallUserInfoPassOther;
use App\Entitys\Other\ManagerPretendMaid;
use App\Entitys\Other\MtMaidOldOther;
use App\Entitys\Other\PddMaidOldOther;
use App\Entitys\Other\TaobaoMaidOldOther;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeCircleMaid;
use App\Entitys\Other\ThreeEleMaidOld;
use App\Entitys\Other\ThreeUser;
use App\Entitys\Other\ThreeUserGet;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\AppUserInfoOut;
use App\Entitys\OtherOut\WechatInfoOut;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\Qmshida\UserIncomeOther;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IndexController extends Controller
{
    //
    //

    /**
     * 获取经理佣金
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getManagerMoney(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'type' => 'required',
                'status' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type'];
            $status = $arrRequest['status'];
            $model = new ManagerPretendMaid();
            $list = $model->where(['app_id' => $app_id, 'type' => $type, 'status' => $status])->orderByDesc('id')->paginate(10, ['app_id', 'type', 'money', 'order_id', 'created_at']);
            $list = $list->toArray();
            foreach ($list['data'] as $index => $item) {
                $list['data'][$index]['app_id'] = CommonFunction::userAppIdCompatibility($item['app_id']);
            }
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }


    /**
     * 获取管理费详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getOtherMaidMoney(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'timestamp' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $timestamp = $arrRequest['timestamp'];
            $userIncomeService = new UserIncomeOther($app_id, $timestamp);
            $data = $userIncomeService->getCurrentMonthInfo();
            return $this->getResponse($data);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    public function getUserMoney(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $userMoneyModel = new ThreeUser();
            $list = $userMoneyModel->getUserMoney($app_id);
            $list = $userMoneyModel->getUserMoney($app_id);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }


    public function getUserLevel(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $userMoneyModel = new AppUserInfoOut();
            $list = $userMoneyModel->getUserById($app_id);
            return $this->getResponse([
                'level' => $list['level']
            ]);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    public function getUserWithdrawList(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $model = new ThreeUserGet();
            $list = $model->getUserWithDrawList($app_id);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /**
     * 获取提现成功总额
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getWithDrawSuccess(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $model = new ThreeUserGet();
            $list = $model->getSum($app_id, 1);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    public function getUserLogList(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $model = new ThreeChangeUserLog();
            $list = $model->getUserLogList($app_id);
            foreach ($list as $key => $item) {
                $list[$key]['from_type'] = $model->change2[$item['from_info']];
            }
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /**
     * 绑定支付宝和银行卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function bindWithdrawInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'account' => 'required',
                'type' => Rule::in([1, 2]),
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type'];
            $account = $arrRequest['account'];
            $threeUserModel = new ThreeUser();
            if ($type == 1) {
                $threeUserModel->where(['app_id' => $app_id])->update([
                    'alipay' => $account
                ]);
            }
            if ($type == 2) {
                $threeUserModel->where(['app_id' => $app_id])->update([
                    'salary_account' => $account
                ]);
            }
            return $this->getResponse("绑定成功！");
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /**
     * 用户提现，众薪 支付宝，银行卡提现
     * type  1 支付宝 2 银行卡
     * alipay 支付宝账号
     * salary_account 银行卡号
     */
    public function applyWithdrawAlipay(Request $request, MallUserInfoPassOther $mallUserInfoPassOut, ThreeUserGet $threeUserGet, ThreeUser $threeUser)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'money' => 'required',
                'app_id' => 'required',
                'type' => Rule::in([1, 2]),
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $type = $arrRequest['type'];
            $min_money = 10;
            if ($arrRequest['money'] < $min_money) {
                return $this->getInfoResponse('5001', '金额不可小于' . $min_money . '元');
            }
            $app_id = $arrRequest['app_id'];
            $three_user = $threeUser->getUser($arrRequest['app_id']);
            if ($three_user->money < $arrRequest['money']) {
                return $this->getInfoResponse('5002', '提现金额已超出可提现金额');
            }
            if ($type == 1) {
                if (empty($three_user['alipay'])) {
                    return $this->getInfoResponse('5003', '请先绑定支付宝账户！');
                }
                $ali_pay = $three_user['alipay'];
            }
            if ($type == 2) {
                if (empty($three_user['salary_account'])) {
                    return $this->getInfoResponse('5003', '请先绑定银行卡！');
                }
                $ali_pay = $three_user['salary_account'];
            }

            $harryEntityModel = new HarryAgreementOut();
            $harry_info = $harryEntityModel->where(['app_id' => $app_id])->first();
            if (empty($harry_info)) {
                return $this->getInfoResponse('5004', '为查找到您的签约信息，无法申请提现！');
            }
            $int_dispose_apply_cash = $threeUserGet->getDisposeApplyCash($app_id);
            if (!empty($int_dispose_apply_cash)) {
                return $this->getInfoResponse('1002', '当前有正在处理中的提现申请,请勿重复申请');
            }
            $cur_appley_count = $threeUserGet->getMonthApplyCount($app_id);
            if (!empty($cur_appley_count) && $cur_appley_count >= 2) {
                return $this->getInfoResponse('1002', '当月已提现两笔, 请下个月再申请提现');
            }
            if (Cache::has('alimama_get_cash_2_' . $app_id)) {
                return $this->getInfoResponse('1005', '申请频繁，请稍后再试！');
            }
            $threeUser->subMoney($arrRequest['app_id'], $arrRequest['money']);
//            $money = $arrRequest['money'] * config('other.withdraw_rate');
            $manage_fee = 0; // 手续费
            $rate = 0; // 手续费比率
            if ($arrRequest['money'] >= 34) {
                $money = $arrRequest['money'] * 0.97;
            } else {
                $money = $arrRequest['money'] - 1;
            }
            $manage_fee = $arrRequest['money'] - $money;
//            if ($arrRequest['money'] < 2000) {
//                $rate = 0.03;
//            } elseif ($arrRequest['money'] < 5000) {
//                $rate = 0.05;
//            } else {
//                $rate = 0.07;
//            }
//            $manage_fee = $arrRequest['money'] * $rate;
//            $money = $arrRequest['money'] - $manage_fee;

            DB::connection('db001')->beginTransaction();
            $threeUserGet->addLog([
                'app_id' => $arrRequest['app_id'],
                'phone' => $ali_pay,
                'real_name' => $harry_info['name'],
                'money' => $money,
                'manage_fee' => $manage_fee,
                'type' => 0,
                'from_type' => $type,
            ]);

            $changeUserLog = new ThreeChangeUserLog();
            $changeUserLog->create([
                'app_id' => $app_id,
                'before_money' => $three_user->money,
                'before_next_money' => -$arrRequest['money'],
                'after_money' => ($three_user->money - $arrRequest['money']),
                'from_type' => 11,
                'from_info' => 'UWD',
            ]);
            DB::connection('db001')->commit();
            Cache::put('alimama_get_cash_2_' . $app_id, 1, 0.25);
            return $this->getResponse('提现申请成功');
        } catch (\Throwable $e) {
            DB::connection('db001')->rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getMessage(), '500');
        }
    }

    /**
     * 增加日志 工猫提现（银行卡）弃用
     */
    public function addLog(Request $request, MallUserInfoPassOther $mallUserInfoPassOut, ThreeUserGet $threeUserGet, ThreeUser $threeUser)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'money' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $mallInfo = $mallUserInfoPassOut->where(['app_id' => $arrRequest['app_id']])->first();
            if (empty($mallInfo)) {
                return $this->getInfoResponse('4004', '当前账户未绑定银行卡信息，请先电签验证后再申请！');
            }
            $min_money = 10;
            if ($arrRequest['money'] < $min_money) {
                return $this->getInfoResponse('5001', '金额不可小于' . $min_money . '元');
            }
            $app_id = $arrRequest['app_id'];
            $three_user = $threeUser->getUser($arrRequest['app_id']);
            if ($three_user->money < $arrRequest['money']) {
                return $this->getInfoResponse('5002', '提现金额已超出可提现金额');
            }
            $int_dispose_apply_cash = $threeUserGet->getDisposeApplyCash($app_id);
            if (!empty($int_dispose_apply_cash)) {
                return $this->getInfoResponse('1002', '当前有正在处理中的提现申请,请勿重复申请');
            }
            $cur_appley_count = $threeUserGet->getMonthApplyCount($app_id);
            if (!empty($cur_appley_count) && $cur_appley_count >= 2) {
                return $this->getInfoResponse('1002', '当月已提现两笔, 请下个月再申请提现');
            }
            if (Cache::has('alimama_get_cash_2_' . $app_id)) {
                return $this->getInfoResponse('1005', '申请频繁，请稍后再试！');
            }
            Cache::put('alimama_get_cash_2_' . $app_id, 1, 0.25);
            $threeUser->subMoney($arrRequest['app_id'], $arrRequest['money']);
//            $money = $arrRequest['money'] * config('other.withdraw_rate');
            $manage_fee = 0; // 手续费
            $rate = 0; // 手续费比率
            if ($arrRequest['money'] >= 34) {
                $money = $arrRequest['money'] * 0.97;
            } else {
                $money = $arrRequest['money'] - 1;
            }
            $manage_fee = $arrRequest['money'] - $money;

//            if ($arrRequest['money'] <= 2000) {
//                $rate = 0.03;
//            } elseif ($arrRequest['money'] <= 5000) {
//                $rate = 0.05;
//            } else {
//                $rate = 0.05;
//            }
//            $manage_fee = $arrRequest['money'] * $rate;
//            $money = $arrRequest['money'] - $manage_fee;

            DB::connection('db001')->beginTransaction();
            $threeUserGet->addLog([
                'app_id' => $arrRequest['app_id'],
                'phone' => $mallInfo['mobile'],
                'real_name' => $mallInfo['name'],
                'money' => $money,
                'manage_fee' => $manage_fee,
                'type' => 0,
            ]);

            $changeUserLog = new ThreeChangeUserLog();
            $changeUserLog->create([
                'app_id' => $app_id,
                'before_money' => $three_user->money,
                'before_next_money' => -$arrRequest['money'],
                'after_money' => ($three_user->money - $arrRequest['money']),
                'from_type' => 11,
                'from_info' => 'UWD',
            ]);
            DB::connection('db001')->commit();
            return $this->getResponse('提现申请成功');
        } catch (\Throwable $e) {
            DB::connection('db001')->rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getMessage(), '500');
        }
    }
}
