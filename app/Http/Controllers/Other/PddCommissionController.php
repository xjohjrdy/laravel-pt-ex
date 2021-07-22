<?php

namespace App\Http\Controllers\Other;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\PddMaidOld;
use App\Entitys\Other\PddMaidOldOther;
use App\Entitys\Other\ThreeUser;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\PddEnterOrdersOut;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PddCommissionController extends Controller
{
    public function vipShopCommission(Request $request)
    {
        try {
            //仅用于测试兼容旧版
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'order_id' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];
            $datum = PddEnterOrdersOut::where(['order_sn' => $order_id])->first();
            if(empty($datum)){
                $this->log(date('Y-m-d H:m:s') . '---' . '无效的订单号：' . $order_id);
                return $this->getResponse('');
            }
            $app_id = $datum->app_id;                     #取得用户appid
            $order_status = $datum->order_status;         #订单状态： -1 未支付; 0-已支付；1-已成团；2-确认收货；3-审核成功；4-审核失败（不可提现）；5-已经结算；8-非多多进宝商品（无佣金订单）
            $promotion_amount = $datum->promotion_amount; #佣金金额(分)
            $order_sn = $datum->order_sn;                 #推广编号id
            $commission = $promotion_amount / 100;#分变元
//            $group_id = $ad_user_info->groupid;
            $parent_info = AdUserInfoOut::where('pt_id', $app_id)->first(['groupid', 'pt_pid', 'pt_id']);
            $tmp_next_id = $parent_info['pt_pid'];
            $count_partner = 0;

            for ($i = 1; $i < 50; $i++) {
                if (empty($tmp_next_id)) {
                    break;
                }
                $parent_info = AdUserInfoOut::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);

                if (empty($parent_info)) {
                    return false;
                }
                $p_groupid = $parent_info['groupid'];
                $p_pt_pid = $parent_info['pt_pid'];
                $p_pt_id = $parent_info['pt_id'];
                if (PddMaidOldOther::where(['trade_id' => (string)$order_sn, 'app_id' => (string)$p_pt_id])->exists()) {
                    break;
                }
                $tmp_next_id = $p_pt_pid;

                if ($i == 1) {
                    if ($p_groupid == 23) {
                        $due_rmb = round($commission * 0.1, 2);
                    } elseif ($p_groupid == 24) {
                        $due_rmb = round($commission * 0.1, 2);
                        $count_partner += 1;
                    } else {
                        $due_rmb = round($commission * 0.05, 2);
                    }
                } else {
                    if ($p_groupid != 24) {
                        continue;
                    }
                    if ($count_partner == 0) {
                        $due_rmb = round($commission * 0.05, 2);
                    } else {
                        $due_rmb = round($commission * 0.025, 2);
                    }
                    $count_partner += 1;
                }

                if (empty($due_rmb) || $i == 1) {
                    continue;
                }
                PddMaidOldOther::create([
                    'father_id' => $app_id,
                    'trade_id' => (string)$order_sn,
                    'app_id' => $p_pt_id,
                    'group_id' => $p_groupid,
                    'maid_money' => $due_rmb,
                    'type' => 1,
                    'real' => 0,
                ]);

                if ($count_partner >= 2) {
                    break;
                }
            }
            return $this->getResponse('请求成功！');
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                $this->log(date('Y-m-d H:m:s') . 'Pdd-vipShopCommission---' . $e->getMessage());
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    public function delCountOrder(Request $request)
    {
        try {
            //仅用于测试兼容旧版
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'order_id' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $trade_id = $arrRequest['order_id'];
            $maidModel = new PddMaidOldOther();

            $maidModel->where('trade_id', $trade_id)
                ->delete();

            return $this->getResponse('请求成功！');
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                $this->log(date('Y-m-d H:m:s') . 'Pdd-delCountOrder---' . $e->getMessage());
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 根据app_id 取该用户部分信息
     */
    public function getParentInfo($ptPid)
    {
        $obj_ad = new AdUserInfoOut();
        $parentInfo = $obj_ad->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if (empty($parentInfo)) {
            return false;
        }
        return $parentInfo->toArray();
    }

    /*
     * 根据app_id 取该用户可提余额
     */
    public function getParentCarryMoney($ptPid)
    {
        $obj_three_user = new ThreeUser();
        $account = $obj_three_user->where(['app_id' => $ptPid])->first();
        if (!$account) {
            $obj_three_user->create([
                'app_id' => $ptPid,
                'money' => 0,
            ]);
            $account = $obj_three_user->where(['app_id' => $ptPid])->first();
        }
        return $account->money;
    }

    /*
     * 记录日志
     */
    private function log($msg)
    {
        Storage::disk('local')->append('callback_document/pdd_commission_other_info.txt', var_export($msg, true));
    }
}
