<?php

namespace App\Services\WuHang;


use App\Entitys\Other\ManagerPretendMaid;
use App\Entitys\OtherOut\AppUserInfoOut;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Maid
{
    //
    /**
     * @param $order_id 订单号，不同模块不同
     * @param $all_profit_value 用户分佣的值，拿来直接*0.015即是给予的值
     * @param $parent_id 用户上级，订单购买者的上级
     * @param $father_id 订单购买者
     * @param $type 类型，详见下面的类型
     * @return int
     */
    public function maid($order_id, $all_profit_value, $parent_id, $father_id, $type)
    {
        /**
         * 不同类型分佣类型不同
         * 2：淘报销
         * 3：京东
         * 4：拼多多
         * 5：美团
         * 6：饿了么
         * 7：信用卡
         */

        $commission = $all_profit_value;
        $ptPid = $parent_id;


        /***********************************/
        //开始处理逻辑问题
        $signBool = false;
        $signOk = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break; #无上级跳过
            }

            //得到上级用户app等级
            $obj_app_user_info_out = new AppUserInfoOut();
            $user_level = $obj_app_user_info_out->where('id', $ptPid)->value('level');
            $parentInfo['pt_id'] = $ptPid;
            $parentInfo['pt_pid'] = $obj_app_user_info_out->where('id', $ptPid)->value('parent_id');


            $ptPid = $parentInfo['pt_pid'];
            //拨出佣金的1.5%给本人往上推的第1~2个经理（每个人1.5%）；
            $commission_percent = 0.015;
            if ($i > 0) {

                if ($user_level != 4) {
                    continue;
                }

                if ($signBool) {
                    $signOk = true;
                } else {
                    $signBool = true;
                }


            } else {
                //直属处理
                if ($user_level != 4) {
                    continue;
                }

                if ($signBool) {
                    $signOk = true;
                } else {
                    $signBool = true;
                }

            }

            $obj_three_up_maid = new ManagerPretendMaid();
            if ($obj_three_up_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Log::warning('真实重复分佣情况！', [$parentInfo['pt_id'] => $order_id]);
                continue;
            }

            $commission_result = $commission * $commission_percent;

            $commission_result = round($commission_result, 2);

            if ($commission_result > 0) {
                //添加分佣记录
                $obj_three_up_maid->addMaidLog([
                    'app_id' => $parentInfo['pt_id'],
                    'order_id' => $order_id,
                    'father_id' => $father_id,
                    'type' => $type,
                    'money' => $commission_result,
                    'status' => 0,
                ]);
            }

            /**
             * 暂时不给用户加管理费以及管理费变化记录
             * 这段代码暂时注释
             */
//                //给用户加可提余额
//                $obj_three_user = new ThreeUser();
//                $obj_three_user->where('app_id', $parentInfo['pt_id'])->update(['money' => DB::raw("money + " . $commission_result)]);
//
//                //记录可提余额变化记录值与变化说明
//                $obj_three_change_user_log = new ThreeChangeUserLog();
//                $later_money = $perentAcount + $commission_result;
//                $obj_three_change_user_log->addLog($parentInfo['pt_id'], $perentAcount, $commission_result, $later_money, 0, 'SPT');

            if ($signOk) {
                break;
            }
        }

        return 1;
    }


    //

    /**
     * 圈子专属分佣，由于圈子分佣规则固定，和其他模块不一致，故松耦合
     * @param $order_id
     * @param $parent_id
     * @param $father_id
     * @return int
     */
    public function circleMaid($order_id, $parent_id, $father_id)
    {


        try {
            /**
             * 不同类型分佣类型不同
             * 2：淘报销
             * 3：京东
             * 4：拼多多
             * 5：美团
             * 6：饿了么
             * 7：信用卡
             */

            $commission = 9.8;
            $ptPid = $parent_id;


            /***********************************/
            //开始处理逻辑问题
            $signBool = false;
            $signOk = false;
            for ($i = 0; $i < 50; $i++) {
                if (empty($ptPid)) {
                    break; #无上级跳过
                }

                //得到上级用户app等级
                $obj_app_user_info_out = new AppUserInfoOut();
                $user_level = $obj_app_user_info_out->where('id', $ptPid)->value('level');
                $parentInfo['pt_id'] = $ptPid;
                $parentInfo['pt_pid'] = $obj_app_user_info_out->where('id', $ptPid)->value('parent_id');


                $ptPid = $parentInfo['pt_pid'];
                //拨出佣金的1.5%给本人往上推的第1~2个经理（每个人1.5%）；
                $commission_percent = 1;
                if ($i > 0) {

                    if ($user_level != 4) {
                        continue;
                    }

                    if ($signBool) {
                        $signOk = true;
                    } else {
                        $signBool = true;
                    }


                } else {
                    //直属处理
                    if ($user_level != 4) {
                        continue;
                    }

                    if ($signBool) {
                        $signOk = true;
                    } else {
                        $signBool = true;
                    }

                }

                $obj_three_up_maid = new ManagerPretendMaid();
                if ($obj_three_up_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                    Log::warning('真实重复分佣情况！', [$parentInfo['pt_id'] => $order_id]);
                    continue;
                }

                $commission_result = $commission * $commission_percent;

                $commission_result = round($commission_result, 2);

                if ($commission_result > 0) {
                    //添加分佣记录
                    $obj_three_up_maid->addMaidLog([
                        'app_id' => $parentInfo['pt_id'],
                        'order_id' => $order_id,
                        'father_id' => $father_id,
                        'type' => 8,
                        'money' => $commission_result,
                        'status' => 0,
                    ]);
                }

                /**
                 * 暂时不给用户加管理费以及管理费变化记录
                 * 这段代码暂时注释
                 */
//                //给用户加可提余额
//                $obj_three_user = new ThreeUser();
//                $obj_three_user->where('app_id', $parentInfo['pt_id'])->update(['money' => DB::raw("money + " . $commission_result)]);
//
//                //记录可提余额变化记录值与变化说明
//                $obj_three_change_user_log = new ThreeChangeUserLog();
//                $later_money = $perentAcount + $commission_result;
//                $obj_three_change_user_log->addLog($parentInfo['pt_id'], $perentAcount, $commission_result, $later_money, 0, 'SPT');

                if ($signOk) {
                    break;
                }
            }

        } catch (\Throwable $e) {

        }

        return 1;
    }
}
