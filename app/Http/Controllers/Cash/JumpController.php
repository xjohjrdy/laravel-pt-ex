<?php

namespace App\Http\Controllers\Cash;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CashUser;
use App\Entitys\App\CashUserGet;
use App\Entitys\App\HarryAgreement;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use App\Entitys\App\TaobaoUserGet;
use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JumpController extends Controller
{

    /**
     * 增加日志
     */
    public function addLog(Request $request, WechatInfo $wechatInfo, CashUser $cashUser, TaobaoUserGet $taobaoUserGet, TaobaoUser $taobaoUser, AppUserInfo $appUserInfo, HarryAgreement $harryAgreement)
    {

        DB::beginTransaction();
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'money' => 'required',
                'app_id' => 'required',
//                'target' => 'required',
                'from_type' => 'required',//2：银行卡打款，3：众薪支付宝
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if ($arrRequest['from_type'] == 2) {
                throw new ApiException('因银行卡通道升级维护，请使用支付宝通道进行提现。', 3002);
            }

            if (!empty($arrRequest['from_to'])) {
                if ($arrRequest['from_to'] == 1) {
                    return $this->getInfoResponse('5001', '暂不支持支付宝提现！');
                }
            }

//            $wechat_user_info = $wechatInfo->getAppId($arrRequest['app_id']);
//            if (empty($wechat_user_info)) {
//                return $this->getInfoResponse('4004', '即日起提现需要绑定微信，以微信入账的形式支付，请前往绑定微信');
//            }
            if ($arrRequest['money'] < 5) {
                return $this->getInfoResponse('5001', '金额不可小于5元');
            }
            if ($arrRequest['money'] > 5000) {
                return $this->getInfoResponse('5001', '金额不可大于5000元');
            }
            $app_id = $arrRequest['app_id'];
            $taobao_user = $taobaoUser->getUser($arrRequest['app_id']);
            $user = $appUserInfo->getUserById($arrRequest['app_id']);
            if ($taobao_user->money < $arrRequest['money']) {
                return $this->getInfoResponse('5002', '提现金额已超出可提现金额');
            }
            if (empty($user->alipay) || empty($user->real_name)) {
                return $this->getInfoResponse('5003', '没有输入支付宝账号和支付宝姓名，请先去填写');
            }
            $int_dispose_apply_cash = $taobaoUserGet->getDisposeApplyCash($app_id);
            if (!empty($int_dispose_apply_cash)) {
                return $this->getInfoResponse('1002', '当前有正在处理中的提现申请,请勿重复申请');
            }


            $real_name = $harryAgreement->where(['app_id' => $arrRequest['app_id']])->value('name');
            if (empty($real_name)) {
                return $this->getInfoResponse('5003', '未认证真实姓名！请先认证');
            }

            if (Cache::has('alimama_get_cash_' . $app_id)) {
                return $this->getInfoResponse('1005', '申请频繁，请稍后再试！');
            }
            Cache::put('alimama_get_cash_' . $app_id, 1, 0.25);
            $taobaoUser->subMoney($arrRequest['app_id'], $arrRequest['money']);
//            if ($arrRequest['money'] > 100) {
//                $money = $arrRequest['money'] * 0.99;
//            } else {
//                $money = $arrRequest['money'] - 1;
//            }
            if ($arrRequest['money'] >= 34) {
                $money = $arrRequest['money'] * 0.97;
            } else {
                $money = $arrRequest['money'] - 1;
            }

            $fee = $arrRequest['money'] - $money;


            $target = 0;
            if ($arrRequest['from_type'] == 2) {
                $target = $cashUser->where([
                    'app_id' => $app_id,
                    'status' => 1,
                    'type' => 0,
                ])->value('target');
            }

            if ($arrRequest['from_type'] == 3) {
                $target = $cashUser->where([
                    'app_id' => $app_id,
                    'status' => 1,
                    'type' => 1,
                ])->value('target');
            }


            $taobaoUserGet->addLog([
                'app_id' => $arrRequest['app_id'],
                'phone' => empty($arrRequest['target']) ? $target : $arrRequest['target'],
                'real_name' => $real_name,
                'money' => $money,
                'fee' => $fee,
                'type' => 0,
                'from_type' => $arrRequest['from_type']
            ]);

            $from_type = 1;
            if ($arrRequest['from_type'] == 2) {
                $from_type = 441;
            }

            if ($arrRequest['from_type'] == 3) {
                $from_type = 442;
            }

            $changeUserLog = new TaobaoChangeUserLog();
            $changeUserLog->create([
                'app_id' => $app_id,
                'before_money' => $taobao_user->money,
                'before_next_money' => -$arrRequest['money'],
                'before_last_money' => 0,
                'after_money' => ($taobao_user->money - $arrRequest['money']),
                'after_next_money' => 0,
                'after_last_money' => 0,
                'from_type' => $from_type,
            ]);

            DB::commit();
            return $this->getResponse('提现成功');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
