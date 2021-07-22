<?php

namespace App\Http\Controllers\Other;

use App\Entitys\Other\CardMaid;
use App\Entitys\Other\JdMaidOldOther;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeUser;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\CardEnterOrdersOut;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CardCommissionController extends Controller
{
    protected $rateGroup = [ // 定义分佣比率数组
        'current' => [ // 下单用户
            'default' => 0.2, // 默认分佣比率
            'vip' => 0.4, // 比率
            'partner' => 0.4 // 比率
        ],
        'redirect_parent' => [ // 直接上级
            'default' => 0.2,
            'vip' => 0.4,
            'partner' => 0.4
        ],
        'first_parent' => [ // 第一合伙人
            'default' => 0.1,
            'vip' => 0.1,
            'partner' => 0.1
        ],
        'second_parent' => [ // 第二合伙人
            'default' => 0.05,
            'vip' => 0.05,
            'partner' => 0.05
        ]
    ];

    public function otherCardCommission(Request $request)
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
            $cardEnterModel = new CardEnterOrdersOut();
            $order = $cardEnterModel->where(['record_id' => $order_id])->first();
            if (empty($order)) {
                $this->log(date('Y-m-d H:m:s') . '---' . '无效的订单号'  . $order_id);
                return $this->getInfoResponse(500, '无效的订单号');
            }
            $money = $order['orgBonus'];
            $app_id = $order['app_id'];
            $this->subCommission($app_id, $money, $order_id);


            return $this->getResponse("success");
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                $this->log(date('Y-m-d H:m:s') . '---' . $e->getMessage());
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
    public function getUserMoney($ptPid)
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

    /**
     * 分佣逻辑计算
     * @param $userId 葡萄用户ID
     * @param $money 拿去分佣的金额
     * @param $orderId 分佣订单ID
     */
    public function subCommission($userId, $money, $orderId)
    {
        $user_queue = []; // 定义循环发送葡萄币的用户序列
        $userInfo = $this->getParentInfo($userId);
        $direct_parent_user = $this->getParentInfo($userInfo['pt_pid']); // 查找父级
        if (!empty($direct_parent_user)) {
//            $user_queue[] = [
//                'uid' => $direct_parent_user['uid'],
//                'pt_id' => $direct_parent_user['pt_id'],
//                'groupid' => $direct_parent_user['groupid'],
//                'pt_pid' => $direct_parent_user['pt_pid'],
//                'identify' => 'redirect_parent']; // 直接上级
            $direct_group_id = $direct_parent_user['groupid'];
            $need_level = 2; // 默认找两个直接合伙人
            $identify = 'first_parent'; // 默认开始找第一合伙人
            if ($direct_group_id == 24) { // 如果直接上级是合伙人，则去除一个合伙人 分佣位置 找一个合伙人就够了
                $need_level = 1;
                $identify = 'second_parent'; // 设置第一合伙人的分佣为第二合伙人的分佣
            }
            $select_user = $direct_parent_user['pt_pid'];
            while (true) {
                $level = count($user_queue);
                if ($level == $need_level) { // 分佣用户已添加完毕
                    break;
                }
//                $this->userModel->where(['pt_id' => $select_user])->first(['uid', 'pt_id', 'pt_pid', 'groupid']);
                $parent_user = $this->getParentInfo($select_user);
                if (!empty($parent_user)) {
                    if ($parent_user['groupid'] == 24) { // 如果上级是合伙人的身份 加入分佣用户数组
                        $user_queue[] = [
                            'uid' => $parent_user['uid'],
                            'pt_id' => $parent_user['pt_id'],
                            'groupid' => $parent_user['groupid'],
                            'pt_pid' => $parent_user['pt_id'],
                            'identify' => $identify
                        ];
                        $identify = 'second_parent';
                    }
                    if ($parent_user['pt_pid'] == 0) { // 无上级，结束
                        break;
                    } else {
                        $select_user = $parent_user['pt_pid']; // $parent_user['pt_pid']
                    }
                } else {
                    break;
                }
            }
        }
        try {
            DB::connection('db001')->beginTransaction();
            foreach ($user_queue as $user) {
                $this->calCommissionByUser($user, $money, $userInfo['pt_id'], $orderId);
            }
            DB::connection('db001')->commit();
        } catch (\Throwable $e) {
            DB::connection('db001')->rollBack();
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }

    /**
     * 根据用户groupid判断是否是vip或者合伙人调整分佣百分比
     * @param $user 计算分佣的用户信息
     * @param $money 用来分佣的金额
     * @param $from 办卡人
     * @param $orderId 分佣信用卡订单ID
     */
    public function calCommissionByUser($user, $money, $from, $orderId)
    {
        $rate_key = 'default'; // 默认普通用户  --- default：普通用户  --- vip：vip用户 --- partner： 合伙人
        if ($user['groupid'] == 23) { // vip用户
            $rate_key = 'vip';
        }
        if ($user['groupid'] == 24) { // 合伙人
            $rate_key = 'partner';
        }
        if (CardMaid::where(['record_id' => (string)$orderId, 'app_id' => $user['pt_id']])->exists()) {
            return true;
        }
        $real_rate = $this->rateGroup[$user['identify']][$rate_key]; // 获取最终要分佣的金额比率
        $due_ptb = $money * $real_rate * 10; // 余额
        $commissionRMB = $money * $real_rate;
        $card_maid = [ // 信用卡分佣记录入库
            'from_app_id' => $from,
            'record_id' => $orderId,
            'group_id' => $user['groupid'],
            'maid_ptb' => $due_ptb,
            'type' => $from == $user['pt_id'] ? 1 : 2,
            'app_id' => $user['pt_id'],
        ];
        $perentAcount = $this->getUserMoney($user['pt_id']);
        //给用户加可提余额

        $obj_three_user = new ThreeUser();
        $obj_three_user->where('app_id', $user['pt_id'])->update(['money' => DB::raw("money + " . $commissionRMB)]);

        //记录可提余额变化记录值与变化说明
        $obj_three_change_user_log = new ThreeChangeUserLog();
        $later_money = $perentAcount + $commissionRMB;
        $obj_three_change_user_log->addLog($user['pt_id'], $perentAcount, $commissionRMB, $later_money, 58, 'JQI');
        $cardMaidModel = new CardMaid();
        $cardMaidModel->create($card_maid);
    }

    /*
 * 记录日志
 */
    private function log($msg)
    {
        Storage::disk('local')->append('callback_document/card_commission_other_info.txt', var_export($msg, true));
    }
}
