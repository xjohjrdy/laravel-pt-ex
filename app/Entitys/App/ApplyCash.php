<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class ApplyCash extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_apply_cash';
    public $timestamps = false;
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 得到用户已经在处理申请的信息
     */
    public function getDisposeApplyCash($app_id)
    {
        return $this->where(['user_id'=>$app_id,'status'=>0])->count('id');
    }
    /*
     * 得到全部成功的分红总和
     */
    public function getTotalBonusAmount($app_id)
    {
        return (float)$this->where(['user_id'=>$app_id,'status'=>1])->sum('bonus_amount');
    }
    /*
     * 分红提现申请记录列表
     */
    public function getApplyBonusList($app_id)
    {
        return $this->where(['user_id'=>$app_id, 'status'=>1])
            ->where('bonus_amount','<>',0)
            ->orderByDesc('create_time')
            ->paginate('20',[ 'id', 'alipay', 'real_name', 'bonus_amount', 'status', 'reason', 'create_time']);
    }
    /*
     * 全部提现金额总和
     */
    public function getTotalCashAmount($app_id)
    {
        return (float)$this->where(['user_id'=>$app_id,'status'=>1])->sum('cash_amount');
    }
    /*
     * 提现申请记录表
     */
    public function getApplyCashList($app_id)
    {
        return $this->where('user_id' , $app_id)
            ->orderByDesc('create_time')
            ->paginate(20,[
                'id',
                'alipay',
                'real_name',
                'cash_amount',
                'bonus_amount',
                'order_amount',
                'status',
                'reason',
                'create_time'
            ]);
    }
    /*
     * 提现订单报销总和
     */
    public function getTotalOrderAmount($app_id)
    {
        return (float)$this->where(['user_id'=>$app_id,'status'=>1])->sum('order_amount');
    }
    /*
     * 提现报销列表
     */
    public function getApplyOrderList($app_id)
    {
        return $this->where('user_id' , $app_id)
            ->where('order_amount','<>',0)
            ->orderByDesc('create_time')
            ->paginate(20,[
                'id',
                'alipay',
                'real_name',
                'order_amount',
                'status',
                'reason',
                'create_time'
            ]);
    }
    /*
     * 提现记录
     */
    public function isWithdraw($app_id)
    {
        return $this->where(['user_id'=>$app_id, 'status'=>0])->value('id');
    }
}
