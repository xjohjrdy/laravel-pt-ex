<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserAccount extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_common_member_count';
    public $timestamps = false;

    /**
     * 更新我的币金额
     * @param int $value
     * @return int
     */
    public function addPTBMoney($value, $uid)
    {
        return $this->where(['uid' => $uid])->update(['extcredits4' => $value]);
    }

    /**
	 * 起因：
	 * 看到美联航兑换35%加赠得活动，于是开始准备
	 * 满足了加赠活动条件以后，准备在12月15日以前完成兑换
	 * 经过：
	 * 今天兑换得时候，发现提示：兑换失败，不符合校验规则
	 * 结果：
	 * 目前我已经去补申请了480元年费得悦卡白金，但是一定不可能4天以后
	 * 就处理完成，所以我特地打这个电话，要求反馈给我一个解决方案
	 * 解决方案1：等到我悦卡白金卡下卡，兑换以后，兑换订单帮我手动加入到加赠活动里面
	 * 解决方案2：客服后台那边，帮助我兑换一次白金卡比例100：1，让我能及时参与15日以前得活动，如果无法在15日以前获得反馈，尽量帮我申请1号方案
     * 减少我的币金额
     * @param int $value
     * @return int
     */
    public function subtractPTBMoney($value, $uid)
    {
        return $this->where(['uid' => $uid])->update(['extcredits4' => DB::raw("extcredits4 - " . $value)]);
    }

    /**
     * 增加我的币金额
     * @param int $value
     * @return int
     */
    public function addUserPTBMoney($value, $uid)
    {
        return $this->where(['uid' => $uid])->update(['extcredits4' => DB::raw("extcredits4 + " . $value)]);
    }

    /**
     * 更改用户RMB金额
     * @param $value
     * @param $uid
     * @return bool
     */
    public function updateRMBMoney($value, $uid)
    {
        return $this->where(['uid' => $uid])->update(['extcredits3' => $value]);
    }

    /**
     * 减去用户的gra数量
     * @param $value
     * @param $uid
     * @return bool
     */
    public function subtractGRAMoney($value, $uid)
    {
        return $this->where(['uid' => $uid])->update(['extcredits5' => DB::raw("extcredits5 - " . $value)]);
    }

    /**
     * 获取用户账号//兼容旧版，没有钱包数据则自动添加钱包数据
     * @param $uid
     * @return Model|null|static
     */
    public function getUserAccount($uid)
    {
        $account = $this->where(['uid' => $uid])->first(['uid', 'extcredits3', 'extcredits4', 'extcredits5']);
        if (!$account) {
            $this->insert([
                'uid' => $uid,
                'extcredits1' => 0,
                'extcredits2' => 0,
                'extcredits3' => 0,
                'extcredits4' => 0,
                'extcredits5' => 0,
                'extcredits6' => 0,
                'extcredits7' => 0,
                'extcredits8' => 0,
                'friends' => 0,
                'posts' => 0,
                'threads' => 0,
                'digestposts' => 0,
                'doings' => 0,
                'blogs' => 0,
                'albums' => 0,
                'sharings' => 0,
                'attachsize' => 0,
                'views' => 0,
                'oltime' => 0,
                'todayattachs' => 0,
                'todayattachsize' => 0,
                'feeds' => 0,
                'follower' => 0,
                'following' => 0,
                'newfollower' => 0,
                'blacklist' => 0,
            ]);

            $account = $this->where(['uid' => $uid])->first();
        }
        return $account;
    }

    /**
     * 查找所有有存在gra币的用户uid
     */
    public function getAllUserHavingFive()
    {
        $res = $this->where('extcredits5','>','0')->get(['uid']);
        return $res->toArray();
    }


}
