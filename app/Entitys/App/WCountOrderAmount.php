<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WCountOrderAmount extends Model
{

    protected $connection = 'app38';
    protected $table = 'lc_w_count_order_amount';
    use SoftDeletes;

    /**
     * app_id唯一索引
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

    public function addInfo($app_id, $maid)
    {
        return $this->create([
            'app_id' => $app_id,
            'maid_money' => $maid,
        ]);
    }

    public function realDelete($app_id)
    {
        return $this->where(['app_id' => $app_id])->forceDelete();
    }

    public function isInfo($app_id, $maid)
    {
        $info = $this->where(['app_id' => $app_id])->first();
        if (empty($info)) {
            return $this->addInfo($app_id, $maid);
        } else {
            $mall = $maid + $info->maid_money;
            return $this->where(['app_id' => $app_id])->update([
                'maid_money' => $mall,
            ]);
        }

    }

    /*
    * 上月报销记录
    */
    public function lastAllApplyData()
    {
        $data = $this
            ->count();
        return $data;
    }
}
