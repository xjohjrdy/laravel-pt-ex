<?php

namespace App\Http\Controllers\Other;

use App\Entitys\Other\ThreeAljbgpCreditLog;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeEleMaid;
use App\Entitys\Other\ThreeEleMaidOld;
use App\Entitys\Other\ThreeUser;
use App\Entitys\Other\UserThreeUpMaid;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\AppUserInfoOut;
use App\Entitys\OtherOut\EleMaidOldOut;
use App\Entitys\OtherOut\ShopOrdersOneOut;
use App\Entitys\OtherOut\ShopOrdersOut;
use App\Entitys\OtherOut\ShopOrdersPretendMaidOut;
use App\Entitys\OtherOut\ShopVipBuyOut;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ShopCommissionController extends Controller
{
    public function vipShopCommission(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'order_id' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];

            if (Cache::has('vip_shop_commission_' . $order_id)) {
                return $this->getInfoResponse('1001', '该订单已被确认！');
            }
            Cache::put('vip_shop_commission_' . $order_id, 1, 10);
            /***********************************/
            //开始处理逻辑问题
            //取出vip商品拨出值
            $obj_shop_orsers = new ShopOrdersOut();
            $obj_shop_orsers_one = new ShopOrdersOneOut();
            $obj_shop_vip_buy = new ShopVipBuyOut();
            $order = $obj_shop_orsers->where('order_id', $order_id)->first();
            $order_one = $obj_shop_orsers_one->where('order_id', $order->id)->first();
            $maid = $obj_shop_vip_buy->where('vip_id', $order_one->good_id)->value('maid');

            //用户根据id 得到uid
            $obj_ad = new AdUserInfoOut();
            $user_uid = $obj_ad->getUidById($order_one->app_id);
            $uid = $user_uid;

            //根据uid得到 用户信息
            $obj_ad_info = AdUserInfoOut::where(['uid' => $uid])->first();
            if (empty($obj_ad_info)) {
                return false;
            }

            //记录第几个合伙人
            $count_partner = empty($arrRequest['count_partner']) ? 0 : 1;
            $tmp_next_id = $obj_ad_info->pt_pid; #用户父id

            for ($i = 0; $i < 50; $i++) {
                if (empty($tmp_next_id)) {
                    return false; #无上级跳过
                }

                //得到上级用户app等级
                $obj_app_user_info_out = new AppUserInfoOut();
                $user_level = $obj_app_user_info_out->where('id', $tmp_next_id)->value('level');

                //得到上级信息
                $parent_info = $this->getParentInfo($tmp_next_id);
                if (empty($parent_info)) {
                    return false; #上级不存在跳过
                }

                $p_uid = $parent_info['uid'];
                $p_groupid = $parent_info['groupid'];
                $p_pt_pid = $parent_info['pt_pid'];
                $p_pt_id = $parent_info['pt_id'];
                $tmp_next_id = $p_pt_pid;

                if ($i == 0) {
                    continue;//直属跳过
                } else {
                    if ($p_groupid != 24) {
                        continue;
                    }
                    if ($count_partner == 0) {
                        $due_ptb = 0.3 * $maid;
                    } else {
                        $due_ptb = 0.11 * $maid;
                    }
                }

                if (empty($due_ptb)) {
                    continue;
                }
                if ($p_groupid == 24) {
                    $count_partner += 1;#合伙人次数+1
                }

                $obj_Three_aljbgp_credit_log = new ThreeAljbgpCreditLog();
                if ($obj_Three_aljbgp_credit_log->where(['uid' => $p_uid, 'orderid' => $order_id])->exists()) {
                    Storage::disk('local')->append('callback_document/again_log.txt', var_export(var_export($p_uid . '--' . $order_id, true), true));
                    continue;
                }
                $obj_Three_aljbgp_credit_log->insert([
                    'uid' => $p_uid,
                    'orderid' => $order_id,
                    'money' => $due_ptb,
                    'dateline' => time()
                ]);

                $due_ptb = round($due_ptb, 2);

                //添加分佣记录
                $obj_three_up_maid = new UserThreeUpMaid();
                $obj_three_up_maid->addMaidLog($p_pt_id, $order_id, $due_ptb, $p_groupid, $order_one->app_id, $user_level);

                //根据父id 获取父级当前的可提余额
                $perentAcount = $this->getParentCarryMoney($p_pt_id);

                //给用户加可提余额
                $obj_three_user = new ThreeUser();
                $obj_three_user->where('app_id', $p_pt_id)->update(['money' => DB::raw("money + " . $due_ptb)]);

                //记录可提余额变化记录值与变化说明
                $obj_three_change_user_log = new ThreeChangeUserLog();
                $later_money = $perentAcount + $due_ptb;
                $obj_three_change_user_log->addLog($p_pt_id, $perentAcount, $due_ptb, $later_money, 0, 'FPT');

                if ($count_partner >= 2) {
                    break;
                }
            }
            return $this->getResponse('请求成功！');
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
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

    public function generalShopCommission(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'order_id' => 'required',    //必须有数据
                'all_profit_value' => 'required',    //必须有数据
                'parent_id' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];
            $commission = $arrRequest['all_profit_value'];
            $ptPid = $arrRequest['parent_id'];

            if (Cache::has('general_shop_commission_' . $order_id)) {
                return $this->getInfoResponse('1001', '该订单已被确认！');
            }
            Cache::put('general_shop_commission_' . $order_id, 1, 10);
            /***********************************/
            //开始处理逻辑问题
            $commission = $commission * 0.41;
            $signBool = empty($arrRequest['count_partner']) ? false : true;
            $signOk = false;
            $specialBool = empty($arrRequest['count_partner']) ? false : true;
            for ($i = 0; $i < 50; $i++) {
                if (empty($ptPid)) {
                    break; #无上级跳过
                }

                //得到上级用户app等级
                $obj_app_user_info_out = new AppUserInfoOut();
                $user_level = $obj_app_user_info_out->where('id', $ptPid)->value('level');

                //得到上级信息
                $obj_ad = new AdUserInfoOut();
                $parentInfo = $obj_ad->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
                if (empty($parentInfo)) {
                    break;#无上级信息跳过
                }
                $parentInfo = $parentInfo->toArray();
                $ptPid = $parentInfo['pt_pid'];
                $commission_percent = 6;

                if ($i > 0) {
                    $commission_percent *= 0.5;
                    if ($parentInfo['groupid'] != 24) {
                        continue;
                    }

                    if ($signBool) {
                        $commission_percent *= 0.5;
                        $signOk = true;
                    } else {
                        $signBool = true;
                    }

                    if ($specialBool) {
                        $commission_percent *= 0.5;
                    }

                } else {
                    continue;#直属分佣跳过
                }

                $obj_three_up_maid = new UserThreeUpMaid();
                if ($obj_three_up_maid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                    Log::warning('真实重复分佣情况！', [$parentInfo['pt_id'] => $order_id]);
                    continue;
                }

                $commission_result = $commission * $commission_percent;
                if ($commission_result > 0 && $commission_result < 1) {
                    $commission_result = 1;
                }
                $commission_result = $commission_result / 10; #葡萄币转金额

                //得到分佣订单所属用户id
                $obj_shop_orsers = new ShopOrdersOut();
                $order = $obj_shop_orsers->where('order_id', $order_id)->first();

                $commission_result = round($commission_result, 2);

                //添加分佣记录
                $obj_three_up_maid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result, $parentInfo['groupid'], $order->app_id, $user_level);

                //更新假订单状态
                $obj_shop_orders_pretend_maid_out = new ShopOrdersPretendMaidOut();
                $obj_shop_orders_pretend_maid_out->updateStatus($parentInfo['pt_id'], $order_id);

                //根据父id 获取父级当前的可提余额
                $perentAcount = $this->getParentCarryMoney($parentInfo['pt_id']);

                //给用户加可提余额
                $obj_three_user = new ThreeUser();
                $obj_three_user->where('app_id', $parentInfo['pt_id'])->update(['money' => DB::raw("money + " . $commission_result)]);

                //记录可提余额变化记录值与变化说明
                $obj_three_change_user_log = new ThreeChangeUserLog();
                $later_money = $perentAcount + $commission_result;
                $obj_three_change_user_log->addLog($parentInfo['pt_id'], $perentAcount, $commission_result, $later_money, 0, 'SPT');

                if ($signOk) {
                    break;
                }
            }
            return $this->getResponse('请求成功！');
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 饿了么假多分逻辑
     */
    public function eleMoreCommission(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'order_id' => 'required',    //必须有数据
                'app_id' => 'required',    //必须有数据
                'commission' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $order_id = $arrRequest['order_id'];
            $app_id = $arrRequest['app_id'];
            $commission = $arrRequest['commission'];

            /***********************************/
            //开始处理逻辑问题
            //判断是否是有效订单
            if ($app_id == 1 && $commission == 1) {//无效删除订单分佣记录
                return ThreeEleMaidOld::where('trade_id', (string)$order_id)->delete();
            }

            //有效订单往下执行
            $ad_user_info = AdUserInfoOut::where(['pt_id' => $app_id])->first();
            if (empty($ad_user_info)) {
                return false;
            }
            $group_id = $ad_user_info->groupid;
            $count_partner = 0;
            $tmp_next_id = $ad_user_info->pt_pid;

            for ($i = 1; $i < 50; $i++) {
                if (empty($tmp_next_id)) {
                    break;
                }

                $parent_info = AdUserInfoOut::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);

                if (empty($parent_info)) {
                    break;
                }
                $p_groupid = $parent_info['groupid'];
                $p_pt_pid = $parent_info['pt_pid'];
                $p_pt_id = $parent_info['pt_id'];

                $tmp_next_id = $p_pt_pid;

                if ($i == 1) {
                    if ($p_groupid == 24) {
                        $count_partner += 1;
                    }
                    continue;
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

                if (empty($due_rmb)) {
                    continue;
                }

                if (ThreeEleMaidOld::where(['trade_id' => (string)$order_id, 'type' => 1, 'app_id' => $p_pt_id])->exists()) {
                    break;
                }
                ThreeEleMaidOld::create([
                    'father_id' => $app_id,
                    'order_enter_id' => 0,
                    'trade_id' => (string)$order_id,
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
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 饿了么假分佣变真分佣 (废弃 改为脚本处理)
     */
    public function eleMoreCommissionAddMoney(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'last_month_time' => 'required',    //必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $last_month_time = $arrRequest['last_month_time'];

            /***********************************/
            //开始处理逻辑问题
            //得到上月全部多级假表报销记录
            $obj_ele_maid_old = new ThreeEleMaidOld();
            $num_ele_maid_old = $obj_ele_maid_old->lastAllApplyData($last_month_time);

            //总页数
            $all_page = ceil($num_ele_maid_old / 10000);
            $page = 1;

            while ($page <= $all_page) {

                $ele_maid_old_datas = $obj_ele_maid_old
                    ->whereBetween('created_at', $last_month_time)
                    ->where(['real' => 0])
                    ->forPage(1, 10000)
                    ->get(); //取 上个月 的一万条数据， real 为 0 的

                if (empty($ele_maid_old_datas)) {
                    break;
                }

                foreach ($ele_maid_old_datas as $ele_maid_old_data) {

                    if ($ele_maid_old_data->real == 1) { //后续调试使用
                        continue;
                    }

                    //修改假表real
                    $ele_maid_old_data->update(['real' => 1]);  //单条遍历处理
                    //取假表所有字段数据
                    $father_id = $ele_maid_old_data->father_id;
                    $order_enter_id = $ele_maid_old_data->order_enter_id;
                    $trade_id = $ele_maid_old_data->trade_id;
                    $app_id = $ele_maid_old_data->app_id;
                    $group_id = $ele_maid_old_data->group_id;
                    $maid_money = $ele_maid_old_data->maid_money;
                    $type = $ele_maid_old_data->type;

                    //饿了么分佣真表
                    $obj_ele_maid = new ThreeEleMaid();
                    //判断真表里面是否有数据
                    $bor_ele_maid = $obj_ele_maid->where(['trade_id' => $trade_id, 'app_id' => $app_id])->exists();

                    if ($bor_ele_maid) {
                        continue;
                    }

                    $obj_ele_maid->create([
                        'father_id' => $father_id,
                        'order_enter_id' => $order_enter_id,
                        'trade_id' => $trade_id,
                        'app_id' => $app_id,
                        'group_id' => $group_id,
                        'maid_money' => $maid_money,
                        'type' => $type,
                    ]);

                    //根据父id 获取父级当前的可提余额
                    $perentAcount = $this->getParentCarryMoney($app_id);

                    //给用户添加分佣的钱
                    $taobao_user = new ThreeUser();
                    $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();

                    if (empty($obj_taobao_user)) {
                        $taobao_user->create([
                            'app_id' => $app_id,
                            'money' => $maid_money,
                        ]);
                    } else {
                        $obj_taobao_user->money = $obj_taobao_user->money + $maid_money;
                        $obj_taobao_user->save();
                    }

                    //记录可提余额变化记录值与变化说明
                    $obj_three_change_user_log = new ThreeChangeUserLog();
                    $later_money = $perentAcount + $maid_money;
                    $obj_three_change_user_log->addLog($app_id, $perentAcount, $maid_money, $later_money, 0, 'ELE');

                }
                $page++;
            }

            return $this->getResponse('请求成功！');
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
