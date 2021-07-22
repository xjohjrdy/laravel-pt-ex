<?php

namespace App\Entitys\Ad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class VoipAccount extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'pre_voip_account';

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
     * 增加用户账户上的金额
     * @param $phone
     * @param $value
     * @return bool
     */
    public function addMoney($phone, $value)
    {
        return $this->where(['phone' => $phone])->update(['money' => DB::raw("money + " . $value)]);
    }

    /**
     * 增加有效期
     * @param $phone
     * @param $time
     * @return bool
     */
    public function addTime($phone, $time)
    {
        $time_update = time() + $time;
        return $this->where(['phone' => $phone])->update(['delete_time' => $time_update]);
    }

    /**
     * 增加电影有效期
     * @param $phone
     * @return bool
     */
    public function addMovieTime($phone, $time = 15552000)
    {
        $time_update = time() + $time;
        return $this->where(['phone' => $phone])->update(['movie_time' => $time_update]);
    }

    /**
     * 获取电影时间
     * @param $app_id
     * @return mixed
     */
    public function getMovieTime($app_id)
    {
        $user = $this->where(['app_id' => $app_id])->first(['movie_time']);
        $movie_time = 0;
        if ($user) {
            $movie_time = $user->movie_time;
        }

        return $movie_time;
    }
}
