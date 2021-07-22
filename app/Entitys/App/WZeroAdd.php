<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WZeroAdd extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_w_zero_add';
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
     * 123
     */
    public function isInfo($user_device_id)
    {
        $is_in = $this->where(['app_version' => $user_device_id])->first();
        if (empty($is_in)) {
            $this->create([
                'app_version' => $user_device_id,
                'time_number' => 0,
                'last_time' => time(),
            ]);
        } else {
            $now_time = date('Y-m-d', $is_in->last_time);
            $is_now_time = date('Y-m-d', time());
            if ($now_time == $is_now_time) {
                $time_number = $is_in->time_number + 1;
                $this->where(['app_version' => $user_device_id])->update([
                    'time_number' => $time_number
                ]);
            } else {
                $this->where(['app_version' => $user_device_id])->update([
                    'time_number' => 0,
                    'last_time' => time(),
                ]);
            }
        }

        $can_return = $this->where(['app_version' => $user_device_id])->first();
        return $can_return->time_number;
    }
}
