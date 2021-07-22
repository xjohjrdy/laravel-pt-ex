<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThreeChangeUserLog extends Model
{
    public $change = [
        '0' => '淘宝报销',
        '1' => '提现到微信',
        '2' => '分红到账',
        '3' => '旧版报销转移',
        '4' => '旧版分红转移',
        '5' => '提现至支付宝扣除',
        '6' => '提现至银行卡失败退回',
        '7' => '提现至支付宝失败退回',
        '8' => '京东报销',
        '9' => '拼多多报销',
        '10' => '爆款商城商品退货到账',
        '11' => '提现至银行卡扣除',
        '41' => '经理统一分佣',
        '50' => '商城购VIP商品物奖励奖励',
        '51' => '商城收益',
        '52' => '加入圈子获得（圈主/城主专属）',
        '53' => '粉丝加入圈子收益',
        '54' => '圈子领取红包',
        '55' => '圈子被竞价收益',
        '56' => '粉丝购买圈子获得收益',
        '57' => '竞价圈子获得收益',
        '58' => '信用卡收益',
        '59' => '通讯分佣',
        '60' => '购买广告包分佣',
        '61' => '文章广告收益',
        '62' => '葡萄币转余额',
        '70' => '饿了么报销',
        '71' => '提现到银行卡',
    ];
    public $change2 = [
        'FPT' => '商城奖励', //VIP
        'REFUND' => '商品退款扣除奖励',
        'SPT' => '商城奖励',
        'ELE' => '饿了么奖励',
        'CAF' => '圈子奖励',
        'CFP' => '圈子奖励',
        'CBP' => '圈子奖励',
        'FPDD' => '拼多多奖励',
        'FTB' => '淘宝奖励',
        'FJD' => '京东奖励',
        'UWD' => '申请提现扣除',
        'FUWD' => '提现失败增加',
        'JQI' => '信用卡奖励',
        'MTI' => '美团奖励',
        'WHE' => '公司奖励',
    ];
    // lc_test_jd_wh
    protected $connection = 'db001';
    protected $table = 'lc_three_change_user_log';
    use SoftDeletes;

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 分佣变化记录
     * from_type 11 提现申请
     */
    public function addLog($p_pt_id, $perentAcount, $due_ptb, $later_money, $from_type, $from_info)
    {
        return $this->create([
            'app_id' => $p_pt_id,
            'before_money' => $perentAcount,
            'before_next_money' => $due_ptb,
            'after_money' => $later_money,
            'from_type' => $from_type,
            'from_info' => $from_info,
        ]);
    }

    /**
     * 获取用户账户变更记录列表
     * @param $app_id
     * @return ThreeUserGet|Model|null
     */
    public function getUserLogList($app_id)
    {
        $list = $this->where(['app_id' => $app_id])->orderByDesc('created_at')->paginate(20);
        return $list;
    }
}
