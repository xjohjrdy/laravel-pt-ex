<?php
/**
 * User: Administrator
 * Date: 2019/9/16/016
 * Time: 11:31
 */

namespace App\Services\JdCommodity;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\JdEnterOrders;
use App\Entitys\App\JdMaidOld;
use App\Exceptions\ApiException;
use App\Services\Other\OtherCountService;

class JdCommandServices
{

    /*
     * 统计订单数量
     */
    public function countJdOrders($start_time)
    {
        //lc_jd_enter_orders

        //TODO 时间测试待定，等正式上线修改回来

        $obj_jd_enter_orders = new JdEnterOrders();

        $number = $obj_jd_enter_orders
            ->where('created_at', '>=', $start_time)
//            ->where('validCode', 17)
            ->count();
        return $number;
    }

    /*
     *
     */
    public function getCutData($page_size, $page, $start_time)
    {
        $obj_jd_enter_orders = new JdEnterOrders();
        $number = $obj_jd_enter_orders
            ->where('created_at', '>=', $start_time)
//            ->where('validCode', 17)//已完成状态
            ->forPage($page, $page_size)
            ->get();
        return $number;
    }

    /*
     * 检测该笔订单是否已经有分佣记录
     */
    public function isDatum($trade_id, $sku_id)
    {
        $obj_jd_maid = new JdMaidOld();

        $is_datum_data = $obj_jd_maid
            ->where('trade_id', $trade_id)
            ->where('sku_id', $sku_id)
            ->exists();
        return $is_datum_data;
    }

    /*
     * 删除异常的分佣订单
     */
    public function delDatum($trade_id, $sku_id)
    {
        $obj_jd_maid = new JdMaidOld();
        try {
            $is_datum_data = $obj_jd_maid
                ->where('trade_id', $trade_id)
                ->where('sku_id', $sku_id)
                ->delete();
        } catch (\Exception $e) {
            return false;
        }
        return $is_datum_data;
    }


    /*
     * 对某笔订单机进行分佣操作
     */
    public function commissionV2($c_params)
    {
        /**
         * @var $app_id string
         * @var $trade_id string
         * @var $sku_id string
         * @var $maid_money string
         */
        extract($c_params);

        $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

        if (empty($ad_user_info)) {
//            throw new ApiException('分佣失败，该用户不存在于淘宝联盟账号库！！');
            return false; //不为某些特定用户中断操作
        }
        $commission = $maid_money;

        $group_id = $ad_user_info->groupid;

        if (in_array($group_id, [23, 24])) {
            $f_commission = round($commission * 0.645, 2);
        } else {
            $f_commission = round($commission * 0.42, 2);
        }

        $order_commission = $f_commission;

        if (!JdMaidOld::where(['trade_id' => (string)$trade_id, 'sku_id' => (string)$sku_id, 'type' => 2])->exists()) {
            JdMaidOld::create([
                'father_id' => 0,
                'trade_id' => (string)$trade_id,
                'sku_id' => (string)$sku_id,
                'app_id' => $app_id,
                'group_id' => $group_id,
                'maid_money' => $f_commission,
                'type' => 2,
                'real' => 0,
            ]);
        }



        $due_rmb = 0;
        $tmp_next_id = $ad_user_info->pt_pid;
        $parent_info = AdUserInfo::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);
        if(empty($parent_info)){
            return false;
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
        if (!JdMaidOld::where(['trade_id' => (string)$trade_id, 'sku_id' => (string)$sku_id, 'app_id' => $p_pt_id, 'type' => 1])->exists()) {
            JdMaidOld::create([
                'father_id' => $app_id,
                'trade_id' => (string)$trade_id,
                'sku_id' => (string)$sku_id,
                'app_id' => $p_pt_id,
                'group_id' => $p_groupid,
                'maid_money' => $due_rmb,
                'type' => 1,
                'real' => 0,
            ]);
        }


    }
}