<?php

namespace App\Services\Ali;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AdminBehaviorLog;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\App\UserOrderNew;
use App\Entitys\App\UserOrderTao;
use App\Entitys\Xin\TaobaoData;
use App\Exceptions\ApiException;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\JPush\JPush;
use App\Services\Other\OtherCountService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AliOrderService extends Model
{

    function handleTaoBaoDataV1($taobao_data)
    {
        $taobao_order = @$taobao_data['order_number'];
        $obj_taobao_single = UserOrderTao::whereRaw("order_number='{$taobao_order}'")->first();
        $is_commission = false;
        if (empty($obj_taobao_single)) {
            $obj_taobao_single = UserOrderNew::whereRaw("order_number='{$taobao_order}'")->first();
            $is_commission = true;
        }

        if (empty($obj_taobao_single)) {
            return $this->addTaoBao($taobao_data);
        }

        $to_app_id = $obj_taobao_single->user_id;

        DB::beginTransaction();
        try {
            if (empty($obj_taobao_single->status)) {
                $obj_taobao_single->status = 0;
            }

            if ($obj_taobao_single->status == 0 && in_array($taobao_data['status'], [1, 2])) {
                $obj_taobao_single->confirm_time = time();
            }


            $cashback_percent = 0.41;
            $cashback_amount = 0;


            switch (@$taobao_data['status']) {
                case 1:
                    if ($is_commission) {
                        $obj_taobao_single->status = 3;
                        $order_commission = $this->addOrderCommissionV2($taobao_order, $to_app_id, $taobao_data['commission']);
                        $obj_taobao_single->cashback_amount = $order_commission;
                    } else {
                        $cashback_amount = round($cashback_percent * $taobao_data['commission'], 2);
                        $cashback_amount = $cashback_amount > 0 ? $cashback_amount : 0.01;
                        $obj_taobao_single->status = 3;
                        $obj_taobao_single->cashback_amount = $cashback_amount;
                    }

                    break;
                case 3:
                    $obj_taobao_single->status = 2;
                    $obj_taobao_single->cashback_amount = 0;
                    $obj_taobao_single->reason = '淘宝返回数据,显示订单失效';
                    if ($is_commission) {
                        $this->reduceOrderCommissionV1($taobao_order);
                    }
                    break;
                case 2:
                    if ($is_commission) {
                        $obj_taobao_single->status = 4;
                        $obj_taobao_single->confirm_receipt_time = $taobao_data['taobao_time'];
                        $order_commission = $this->addOrderCommissionV2($taobao_order, $to_app_id, $taobao_data['commission']);
                        $obj_taobao_single->cashback_amount = $order_commission;
                    } else {
                        $cashback_amount = round($cashback_percent * $taobao_data['commission'], 2);
                        $cashback_amount = $cashback_amount > 0 ? $cashback_amount : 0.01;
                        $obj_taobao_single->status = 4;
                        $obj_taobao_single->cashback_amount = $cashback_amount;
                        $obj_taobao_single->confirm_receipt_time = $taobao_data['taobao_time'];
                    }
                    break;
            }

            $obj_taobao_single->save();
            AdminBehaviorLog::insert([
                'admin_id' => $taobao_data['admin_id'],
                'user_id' => $obj_taobao_single->user_id,
                'log' => "修改状态status={$taobao_data['status']};返现cashback_amount={$cashback_amount}",
                'create_time' => time()
            ]);

            if ($obj_taobao_single->status == 0) {
                JPush::push_user('您提交的订单已有审核结果', $obj_taobao_single->user_id, 1);
            }

        } catch (\Throwable $e) {
            echo ':::' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage();

            DB::rollBack();

            return false;
        }
        DB::commit();
        return true;
    }

    function addTaoBao($data)
    {
        $obj_tao_bao = TaobaoData::where('order_number', (string)$data['order_number'])->first();
        if (empty($obj_tao_bao)) {
            TaobaoData::insert($data);
        } else {
            $obj_tao_bao->order_number = $data['order_number'];
            $obj_tao_bao->status = $data['status'];
            $obj_tao_bao->commission = $data['commission'];
            $obj_tao_bao->taobao_time = $data['taobao_time'];
            $obj_tao_bao->create_time = $data['create_time'];
            $obj_tao_bao->admin_id = $data['admin_id'];
            $obj_tao_bao->save();
        }
        return true;
    }


    /*
     * 处理订单数据
     */
    protected function makeOrderData($order_data, $taobao, $cashback_percent)
    {
        $order_data['status'] = empty($order_data['status']) ? 0 : $order_data['status'];
        if ($order_data['status'] == 0 && in_array($taobao['status'], [1, 2])) {
            $order_data['confirm_time'] = time();
        }
        if ($taobao['status'] == 1) {
            $cashback_amount = round($cashback_percent * $taobao['commission'], 2);
            $cashback_amount = $cashback_amount > 0 ? $cashback_amount : 0.01;
            $order_data['status'] = 3;
            $order_data['cashback_amount'] = $cashback_amount;
        }
        if ($taobao['status'] == 3) {
            $order_data['status'] = 2;
            $order_data['cashback_amount'] = 0;
            $order_data['reason'] = '淘宝返回数据,显示订单失效';
        }
        if ($taobao['status'] == 2) {
            $order_data['status'] = 4;
            $cashback_amount = round($cashback_percent * $taobao['commission'], 2);
            $cashback_amount = $cashback_amount > 0 ? $cashback_amount : 0.01;
            $order_data['cashback_amount'] = $cashback_amount;
            $order_data['confirm_receipt_time'] = $taobao['taobao_time'];
        }
        return $order_data;
    }


    /*
    * 根据淘宝来的订单号进行预估分佣操作
    * 可单独使用
    */
    public function addOrderCommissionV2($order_id, $app_id, $commission)
    {

        $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

        if (empty($ad_user_info)) {
            throw new ApiException('分佣失败，该用户不存在于淘宝联盟账号库！！');
        }
        $group_id = $ad_user_info->groupid;

        if (in_array($group_id, [23, 24])) {
            $f_commission = round($commission * 0.645, 2);
        } else {
            $f_commission = round($commission * 0.42, 2);
        }

        $order_commission = $f_commission;

        if (TaobaoMaidOld::where(['trade_id' => (string)$order_id, 'type' => 2])->exists()) {
            return $order_commission;
        }

        TaobaoMaidOld::create([
            'father_id' => 0,
            'order_enter_id' => 0,
            'trade_id' => (string)$order_id,
            'app_id' => $app_id,
            'group_id' => $group_id,
            'maid_money' => $f_commission,
            'type' => 2,
            'real' => 0,
        ]);

        $due_rmb = 0;
        $tmp_next_id = $ad_user_info->pt_pid;
        $parent_info = AdUserInfo::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);
        if (empty($parent_info)) {
            throw new ApiException('分佣失败，上级用户' . $tmp_next_id . '不存在于淘宝联盟账号库！！');
        }
        $p_groupid = $parent_info['groupid'];
        $p_pt_id = $parent_info['pt_id'];
        if ($p_groupid == 23) {
            $due_rmb = round($commission * 0.1, 2);
        } elseif ($p_groupid == 24) {
            $due_rmb = round($commission * 0.1, 2);
        } else {
            $due_rmb = round($commission * 0.05, 2);
        }
        TaobaoMaidOld::create([
            'father_id' => $app_id,
            'order_enter_id' => 0,
            'trade_id' => (string)$order_id,
            'app_id' => $p_pt_id,
            'group_id' => $group_id,
            'maid_money' => $due_rmb,
            'type' => 1,
            'real' => 0,
        ]);

        // 添加金币奖励
        $this->coinAward($app_id);

        return $order_commission;
    }

    public function coinAward($app_id)
    {
        try {
            if (!config('test_appid.debug') || in_array($app_id, config('test_appid.app_ids'))) {
                $coinCommonService = new CoinCommonService($app_id);
                $task_id = 3; #新手任务 首次淘宝报销
                $task_time = time();
                $coinCommonService->successTask($task_id, $task_time);
            }
        } catch (\Throwable $e) {

        }
    }


    /*
     * 根据淘宝订单号进行扣除分佣订单操作
     * 处理已经失效的淘宝订单
     */
    public function reduceOrderCommissionV1($order_id)
    {
        return TaobaoMaidOld::where('trade_id', (string)$order_id)->delete();
    }
}
