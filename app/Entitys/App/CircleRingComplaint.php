<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleRingComplaint extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_complaint';
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
     * 增加一个投诉信息
     * @param $data
     * @return Model
     */
    public function createComplaint($data)
    {
        $res = $this->updateOrCreate([
            'app_id' => $data['app_id'],
            'target_id' => $data['circle_id'],
            'type' => $data['type'],
            'result' => 0,
        ], $data);
        return $res;
    }
}
