<?php


namespace App\Services\Common;


use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use Illuminate\Support\Facades\DB;

class UserMoney
{

    /**
     * 给用户加余额并添加变化记录
     *
     * '0' => '报销增加',
     * '1' => '提现到微信',
     * '2' => '分红增加',
     * '3' => '旧版报销转移',
     * '4' => '旧版分红转移',
     * '5' => '支付宝提现扣除',
     * '6' => '微信提现失败增加',
     * '7' => '支付宝提现失败增加',
     * '8' => '京东订单报销增加',
     * '9' => '拼多多订单报销增加',
     * '10' => '商品退货退款',
     * '50' => '商城购物VIP商品佣金奖励',
     * '51' => '爆款商城佣金奖励',
     * '52' => '加入圈子获得（圈主/城主专属）',
     * '53' => '用户加入圈子分佣',
     * '54' => '圈子领取红包',
     * '55' => '圈子被抢购获得我的币',
     * '56' => '购买圈子获得津贴',
     * '57' => '竞价圈子获得津贴',
     * '58' => '信用卡分佣',
     * '59' => '通讯分佣',
     * '60' => '购买广告包分佣',
     * '61' => '文章点击获得',
     * '62' => '我的币转余额',
     * '70' => '饿了么报销',
     * '71' => '圈子发红包扣除',
     * '72' => '购买圈子扣除',
     * '20010' => '购买广告包扣除',
     * '20011' => '加入圈子扣除',
     * @param $app_id
     * @param $cny
     * @param $from_type
     * @param string $from_info
     * @return bool
     * @throws \Exception
     */
    public function plusCnyAndLog($app_id, $cny, $from_type, $from_info = '')
    {

        try {
            DB::beginTransaction();
            if ($cny <= 0) throw new \Exception('cny value error');
            $taobao_user = new TaobaoUser();//用户真实分佣表
            $taobao_change_user_log = new TaobaoChangeUserLog();//记录日志表
            $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();
            if (empty($obj_taobao_user)) {
                $obj_taobao_user = $taobao_user->create([
                    'app_id' => $app_id,
                    'money' => $cny,
                    'next_money' => 0,
                    'last_money' => 0,
                ]);
            } else {
                $obj_taobao_user->money = $obj_taobao_user->money + $cny;
                $obj_taobao_user->save();
            }
            $taobao_change_user_log->create([
                'app_id' => $app_id,
                'before_money' => $obj_taobao_user->money - $cny, //变化前
                'before_next_money' => $cny,  //变化的值
                'before_last_money' => 0,
                'after_money' => $obj_taobao_user->money,   //变化后
                'after_next_money' => 0,
                'after_last_money' => 0,
                'from_type' => $from_type,
                'from_info' => $from_info,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        DB::commit();
        return true;
    }

    /**
     * 给用户加余额并添加变化记录
     * @param $app_id
     * @param $cny
     * @param $from_type
     * @param string $from_info
     * @return bool
     * @throws \Exception
     */
    public function minusCnyAndLog($app_id, $cny, $from_type, $from_info = '')
    {
        try {
            DB::beginTransaction();
            if ($cny <= 0) throw new \Exception('cny value error');
            $taobao_user = new TaobaoUser();//用户真实分佣表
            $taobao_change_user_log = new TaobaoChangeUserLog();//记录日志表
            $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();
            if (empty($obj_taobao_user)) throw new \Exception('user account error');

            $obj_taobao_user->money = $obj_taobao_user->money - $cny; //极端情况下允许为负数
            $obj_taobao_user->save();

            $taobao_change_user_log->create([
                'app_id' => $app_id,
                'before_money' => $obj_taobao_user->money + $cny, //变化前
                'before_next_money' => -$cny,  //变化的值
                'before_last_money' => 0,
                'after_money' => $obj_taobao_user->money,   //变化后
                'after_next_money' => 0,
                'after_last_money' => 0,
                'from_type' => $from_type,
                'from_info' => $from_info,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        DB::commit();
        return true;
    }

    /**
     * 无事务，外部统一新增
     * @param $app_id
     * @param $cny
     * @param $from_type
     * @param string $from_info
     * @return bool
     * @throws \Exception
     */
    public function plusCnyAndLogNoTrans($app_id, $cny, $from_type, $from_info = '')
    {
        if ($cny <= 0) return false;
        $taobao_user = new TaobaoUser();//用户真实分佣表
        $taobao_change_user_log = new TaobaoChangeUserLog();//记录日志表
        $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();
        if (empty($obj_taobao_user)) {
            $obj_taobao_user = $taobao_user->create([
                'app_id' => $app_id,
                'money' => $cny,
                'next_money' => 0,
                'last_money' => 0,
            ]);
        } else {
            $obj_taobao_user->money = $obj_taobao_user->money + $cny;
            $obj_taobao_user->save();
        }
        $taobao_change_user_log->create([
            'app_id' => $app_id,
            'before_money' => $obj_taobao_user->money - $cny, //变化前
            'before_next_money' => $cny,  //变化的值
            'before_last_money' => 0,
            'after_money' => $obj_taobao_user->money,   //变化后
            'after_next_money' => 0,
            'after_last_money' => 0,
            'from_type' => $from_type,
            'from_info' => $from_info,
        ]);
        return true;
    }

    /**
     * 无事务，外部统一新增
     * @param $app_id
     * @param $cny
     * @param $from_type
     * @param string $from_info
     * @return bool
     * @throws \Exception
     */
    public function minusCnyAndLogNoTrans($app_id, $cny, $from_type, $from_info = '')
    {
        if ($cny <= 0) return false;
        $taobao_user = new TaobaoUser();//用户真实分佣表
        $taobao_change_user_log = new TaobaoChangeUserLog();//记录日志表
        $obj_taobao_user = $taobao_user->where('app_id', $app_id)->first();
        if (empty($obj_taobao_user)) throw new \Exception('user account error');

        $obj_taobao_user->money = $obj_taobao_user->money - $cny; //极端情况下允许为负数
        $obj_taobao_user->save();

        $taobao_change_user_log->create([
            'app_id' => $app_id,
            'before_money' => $obj_taobao_user->money + $cny, //变化前
            'before_next_money' => -$cny,  //变化的值
            'before_last_money' => 0,
            'after_money' => $obj_taobao_user->money,   //变化后
            'after_next_money' => 0,
            'after_last_money' => 0,
            'from_type' => $from_type,
            'from_info' => $from_info,
        ]);
        return true;
    }
}