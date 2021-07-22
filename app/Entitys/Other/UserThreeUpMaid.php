<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;

class UserThreeUpMaid extends Model
{
    protected $connection = 'db001';
    protected $table = 'lc_user_three_up_maid';

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

    /*
     * 生成商城的分佣记录
     */
    public function addMaidLog($app_id, $order_id, $money, $p_groupid, $from_app_id, $user_level)
    {
        $res = $this->create([
            'app_id' => $app_id,
            'order_id' => $order_id,
            'money' => $money,
            'group_id' => $p_groupid,
            'from_app_id' => $from_app_id,
            'level' => $user_level,
            'dateline' => time(),
        ]);
        return $res;
    }

    /*
     * 根据时间得到商城的预估收入
     */
    public function getEstimatedMoneyByTime($app_id, $date = 0)
    {
        return $this->where('app_id', $app_id)
            ->where('created_at', '>', $date)
            ->sum('money');
    }

    /**
     * 获取所有的分佣记录
     * @param $app_id
     * @param int $is_page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getAllCreditLog($app_id, $is_page = 0)
    {
        if ($is_page) {
            return $this->where(['app_id' => $app_id])->orderByDesc('updated_at')
                ->paginate();
        }
        return $this->where(['app_id' => $app_id])->orderByDesc('updated_at')
            ->get();
    }
}
