<?php

namespace App\Http\Controllers\Other;

use App\Entitys\App\JdMaidOld;
use App\Entitys\Other\JdMaidOldOther;
use App\Entitys\Other\PddMaidOldOther;
use App\Entitys\Other\ThreeUser;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\JdEnterOrdersOut;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class JdCommissionController extends Controller
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
                'sku_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];
            $sku_id =  $arrRequest['sku_id'];
            $jdModel = new JdEnterOrdersOut();
            $order = $jdModel->where(['orderId' => $order_id, 'sku_id' => $sku_id])->first();
            $trade_id = $order->orderId;   //得到订单id
            $sku_id = $order->skuId;   //得到sku_id 商品id
            $app_id = $order->app_id;  //取得用户appid
            $maid_money = $order->actualFee;    //实际佣金，实际获得佣金
            if(empty($order)){
                $this->log(date('Y-m-d H:m:s') . '---' . '订单为空'  . $order_id);
                return $this->getResponse('');
            }
            $ad_user_info = AdUserInfoOut::where(['pt_id' => $app_id])->first();

            if (empty($ad_user_info)) {
//            throw new ApiException('分佣失败，该用户不存在于淘宝联盟账号库！！');
                return false; //不为某些特定用户中断操作
            }
            $commission = $maid_money;

            $due_rmb = 0;
            $count_partner = 0;
            $tmp_next_id = $ad_user_info->pt_pid;
            for ($i = 1; $i < 50; $i++) {
                if (empty($tmp_next_id)) {
                    break;
                }
                $parent_info = AdUserInfoOut::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);

                if (empty($parent_info)) {
                    $this->log($tmp_next_id . '用户不存在!');
//                throw new ApiException('分佣失败，上级用户' . $tmp_next_id . '不存在于淘宝联盟账号库！！');
                    return false;
                }

                $p_groupid = $parent_info['groupid'];
                $p_pt_pid = $parent_info['pt_pid'];
                $p_pt_id = $parent_info['pt_id'];
                if (JdMaidOldOther::where(['trade_id' => (string)$order_id, 'app_id' => (string)$p_pt_id])->exists()) {
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

                JdMaidOldOther::create([
                    'father_id' => $app_id,
                    'trade_id' => (string)$trade_id,
                    'sku_id' => (string)$sku_id,
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
                $this->log(date('Y-m-d H:m:s') . 'JD-vipShopCommission---' . $e->getMessage());
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
                'sku_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $trade_id = $arrRequest['order_id'];
            $sku_id =  $arrRequest['sku_id'];
            $obj_jd_maid = new JdMaidOldOther();

            $obj_jd_maid
                ->where('trade_id', $trade_id)
                ->where('sku_id', $sku_id)
                ->delete();

            return $this->getResponse('请求成功！');
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                $this->log(date('Y-m-d H:m:s') . 'JD-delCountOrder---' . $e->getMessage());
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
        Storage::disk('local')->append('callback_document/jd_commission_other_info.txt', var_export($msg, true));
    }
}
