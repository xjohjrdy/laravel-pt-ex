<?php

namespace App\Services\UpgradeVip;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\App\GrowthUser;
use App\Entitys\App\GrowthUserValue;

class ChangeVipService
{
    /*
     * 生成代理商购买订单专属 沿用早期订单生成格式
     */
    public function random($length, $numeric = 0)
    {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }

    /**
     * 添加一条代理商订单记录
     * @param $app_id
     * @param int $status 1:未支付， 2:已支付
     * @param $desc
     * @return string
     */
    public function installOrder($app_id, $status = 1, $desc = '通过活跃度达标97.5直接升级超级用户')
    {

        try {
            $order_id = date('YmdHis') . $this->random(18);
            $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();
            $arrOrderParam = array(
                'orderid' => $order_id,
                'status' => $status,
                'uid' => $ad_user_info->uid,
                'groupid' => 23,
                'amount' => 9999,
                'price' => 800,
                'desc' => $desc,
                'submitdate' => time(),
                'confirmdate' => time(),
                'a' => '',
                'b' => '',
                'c' => '',
                'd' => 0,
                'e' => 0,
            );
            RechargeOrder::insert($arrOrderParam);
            return $order_id;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * 升级用户组
     * @param $app_id
     * @param int $groupid
     */
    public function upgradeGroup($app_id, $groupid = 23)
    {

        //需要Eloquent模型save监听
        $m = AdUserInfo::where('pt_id', $app_id)->first();
        $m->setKeyName('uid');
        $m->groupid = $groupid;
        $m->groupexpiry = 4070883661;
        $m->save();
    }


    /**
     * @param $app_id
     * @param $type 成为代理商的方式 1:成长值 2：活跃值
     */
    public function installGrowthOrder($app_id, $type)
    {
        $md_growth_user = new GrowthUser();

        $md_growth_user->updateOrCreate([
            'app_id' => $app_id
        ], [
            'agent_time' => time(),
            'from_agent_type' => $type,
        ]);
    }

    /**
     * @param $app_id
     */
    public function updateGrowthUser($app_id)
    {
        $md_growth_user = new GrowthUserValue();

        $md_growth_user->updateOrCreate([
            'app_id' => $app_id
        ], [
            'is_vip' => 1,
        ]);
    }

}
