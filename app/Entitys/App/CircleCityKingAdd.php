<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleCityKingAdd extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_city_king_add_order';
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
     * 用来判断是否已经拿过城主荣耀
     * @param $app_id
     * @return Model|null|static
     */
    public function getByAppId($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->first();
        return $res;
    }

    /**
     * 创建一个新的城主记录
     * @param $king_id
     * @param $app_id
     * @return $this|Model
     */
    public function createNewAdd($king_id, $app_id)
    {
        $res = $this->create([
            'king_id' => $king_id,
            'app_id' => $app_id,
        ]);
        return $res;
    }
}
