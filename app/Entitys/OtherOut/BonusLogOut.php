<?php

namespace App\Entitys\OtherOut;

use Illuminate\Database\Eloquent\Model;

class BonusLogOut extends Model
{
    //lc_bonus_log

    protected $connection = 'app38_out';
    protected $table = 'lc_bonus_log';
    
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
    /*
     * 全部成功的提现申请金额总和(总收益)
     */
    function getAllBonus($app_id)
    {
        return (float)$this->where(['user_id'=>$app_id])->sum('bonus_amount');
    }
    /*
     * 得到指定用户的分红总数量
     */
    public function getBonusTotalByUserId($app_id)
    {
        return (float)$this->where(['user_id'=>$app_id])->sum('bonus_amount');
    }
    /*
     * 得到用户分红列表
     */
    public function getBonusList($app_id)
    {
        return $this->where('user_id', $app_id)
           ->orderByDesc('id')
            ->paginate(20,['id','bonus_amount','create_time','reason']);
    }
}
