<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;

class UserCreditLog extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_common_credit_log';
    public $timestamps = false;
    public $change = [
        'logs_credit_update_TRC' => '任务奖励积分',
        'logs_credit_update_RTC' => '发表悬赏主题扣除积分',
        'logs_credit_update_RAC' => '最佳答案获取悬赏积分',
        'logs_credit_update_MRC' => '道具随即获取积分',
        'logs_credit_update_TFR' => '葡萄币提现',
        'logs_credit_update_RCV' => '积分转账接收',
        'logs_credit_update_CEC' => '积分兑换',
        'logs_credit_update_ECU' => '通过 UCenter 兑换积分支出',
        'logs_credit_update_SAC' => '出售附件获得积分',
        'logs_credit_update_BAC' => '购买附件支出积分',
        'logs_credit_update_PRC' => '帖子被评分所得积分',
        'logs_credit_update_RSC' => '评分帖子扣除自己的积分',
        'logs_credit_update_STC' => '出售主题获得积分',
        'logs_credit_update_BTC' => '购买主题支出积分',
        'logs_credit_update_AFD' => '购买积分即积分充值',
        'logs_credit_update_UGP' => '购买扩展用户组',
        'logs_credit_update_RPC' => '举报功能中的奖惩',
        'logs_credit_update_ACC' => '参与活动扣除积分',
        'logs_credit_update_RCT' => '回帖奖励',
        'logs_credit_update_RCA' => '回帖中奖',
        'logs_credit_update_RCB' => '返还回帖奖励积分',
        'logs_credit_update_CDC' => '卡密充值',
        'logs_credit_update_BMC' => '购买道具',
        'logs_credit_update_AGC' => '获得红包',
        'logs_credit_update_BGC' => '埋下红包',
        'logs_credit_update_RGC' => '回收红包',
        'logs_credit_update_RKC' => '竞价排名',
        'logs_credit_update_BME' => '购买勋章',
        'logs_credit_update_RPR' => '系统操作',
        'logs_credit_update_RPZ' => '后台积分奖惩清零',
        'logs_credit_update_WPZ' => '话费充值佣金奖励',
        'logs_credit_update_WPJ' => '话费充值',
        'logs_credit_update_BGR' => '创建群组',
        'logs_credit_update_QHB' => '葡萄红包收入',
        'logs_credit_update_FHB' => '葡萄红包支出',
        'logs_credit_update_YSF' => '葡萄币与充值金额互相转换',
        'logs_credit_update_PTG' => '头条收益',
        'logs_credit_update_BBC' => '回购扣除',
        'logs_credit_update_' => '旧版头条收益',
        'logs_credit_update_APT' => '报销分红提现到葡萄币',
        'logs_credit_update_FPT' => '商城购物佣金奖励',
        'logs_credit_update_SPT' => '商城购物佣金奖励',
        'logs_credit_update_SH1' => '商城购物返现',
        'logs_credit_update_SH2' => '商城2',
        'logs_credit_update_SHX' => '商城购物葡萄币支付',
        'logs_credit_update_GRE' => '游戏充值葡萄币支付',
        'logs_credit_update_SAP' => '商品退货退款',
        'logs_credit_update_SAS' => '商品系统退款',
        'logs_credit_update_TXB' => '通讯话费购买支付',
        'logs_credit_update_TXG' => '葡萄通讯佣金奖励',
        'logs_credit_update_APU' => '直推10个VIP的奖励',
        'logs_credit_update_CCP' => '圈子购买',
        'logs_credit_update_CFP' => '购买圈子获得津贴',
        'logs_credit_update_CBP' => '竞价圈子获得津贴',
        'logs_credit_update_CRP' => '圈子被抢购获得葡萄币',
        'logs_credit_update_RSP' => '发红包扣款',
        'logs_credit_update_RLP' => '领取红包',
        'logs_credit_update_CAP' => '加入圈子花费',
        'logs_credit_update_CAG' => '加入圈子获得（圈主/城主专属）',
        'logs_credit_update_CAF' => '用户加入圈子分佣',
        'logs_credit_update_NNN' => '头条奖励多余葡萄币扣除',
        'logs_credit_update_AAA' => '邮费退款',
        'logs_credit_update_APS' => '商城支付宝支付',
        'logs_credit_update_APC' => '圈子加入支付宝支付',
        'logs_credit_update_APR' => '圈子红包支付宝支付',
        'logs_credit_update_APG' => '圈子竞价/购买支付宝支付',
        'logs_credit_update_APP' => '通讯支付宝支付',
        'logs_credit_update_JDF' => '京东白条活动激活奖励',
        'logs_credit_update_JDQ' => '京东白条活动直推奖励',
        'logs_credit_update_JDX' => '京东1分购活动奖励',
        'logs_credit_update_ZKP' => '医疗板块支付',
        'logs_credit_update_ZKA' => '医疗板块退款',
        'logs_credit_update_CYP' => '医疗问答支付',
        'logs_credit_update_SFT' => '1月份奖励',
        'logs_credit_update_SHT' => '2月份奖励',
        'logs_credit_update_SIT' => '3月份奖励',
        'logs_credit_update_SJT' => '4月份奖励',
        'logs_credit_update_SKT' => '5月份奖励',
        'logs_credit_update_SLT' => '6月份奖励',
        'logs_credit_update_SGT' => '7月份奖励',
        'logs_credit_update_SAT' => '8月份奖励',
        'logs_credit_update_SBT' => '9月份奖励',
        'logs_credit_update_SCT' => '10月份奖励',
        'logs_credit_update_SDT' => '11月份奖励',
        'logs_credit_update_SET' => '12月份奖励',
        'logs_credit_update_VGA' => '用户点击视频广告奖励',
        'logs_credit_update_JQI' => '信用卡办卡佣金',
        'logs_credit_update_FPP' => '经理分佣',
        'logs_credit_update_ADP' => '用户购买广告包',

    ];

    /**
     *  此方法一定要搭配变化前后值进行，否则变更前后值会变得不准确
     * @param $uid  用户的uid
     * @param $operation  行为编号（三位大写英文）
     * @param $arr  （需要变化的值，以及对应变化的整型值）[extcredits1=>123(整型)]
     * @return int
     */
    public function addLog($uid, $operation, $arr, $easy = 1)
    {
        $time = time();
        if ($operation == 'PTG') {
            $time = strtotime(date("Y-m-d"), time());
        }

        $change_arr = [
            'uid' => intval($uid),
            'relatedid' => intval($uid),
            'operation' => $operation,
            'extcredits1' => 0,
            'extcredits2' => 0,
            'extcredits3' => 0,
            'extcredits4' => 0,
            'extcredits5' => 0,
            'extcredits6' => 0,
            'extcredits7' => 0,
            'extcredits8' => 0,
            'dateline' => $time,
        ];
        $change_arr = array_merge($change_arr, $arr);

        $insert_id = $this->insertGetId($change_arr);

        return $insert_id;
    }

    /**
     * 获得最近用户操作行为记录，时间最近的
     * @param $uid
     * @param $operation
     * @return Model|null|static
     */
    public function getLastLog($uid, $operation)
    {
        $log = $this->where(['uid' => $uid, 'operation' => $operation])->orderBy("dateline", "desc")->first();
        return $log;
    }

    /**
     * 获取某一个用户某种类型所有记录
     * @param $uid
     * @param $operation
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getTypeLog($uid, $operation)
    {
        $log = $this->where(['uid' => $uid, 'operation' => $operation])->orderBy("dateline", "desc")->get(['dateline', 'uid', 'extcredits4']);
        return $log;
    }

    /**
     *
     * 获取某一个用户某种类型所有记录总数
     * @param $uid
     * @param $operation
     * @return mixed
     */
    public function getTypeLogCount($uid, $operation)
    {
        $count = $this->where(['uid' => $uid, 'operation' => $operation])->sum('extcredits4');
        return $count;
    }

    /**
     * 获得用户操作记录总数
     * @param $uid
     * @param $operation
     * @param $dateline
     * @return int
     */
    public function getCountLog($uid, $operation, $dateline)
    {
        $log = $this->where(['uid' => $uid, 'operation' => $operation, 'dateline' => $dateline])->count();
        return $log;
    }

    /**
     *
     * 获得用户所有操作记录(时间降序)
     * @param $uid
     * @param int $limit
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllCreditLog($uid, $limit = 20)
    {
        $log = $this
            ->where(['uid' => $uid])
            ->Where('extcredits4', '<>', '0')
            ->Where('dateline', '>', '1577808000')
            ->select('uid', 'operation', 'relatedid', 'dateline', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8')
            ->orderByDesc('dateline')->paginate($limit);
        return $log;
    }
}
