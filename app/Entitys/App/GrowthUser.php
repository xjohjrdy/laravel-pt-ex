<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrowthUser extends Model
{
    //
    protected $connection = 'app38';
    protected $table = 'lc_growth_user';
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

    public function getUser($app_id)
    {
        return $this->where(['app_id' => $app_id])->first();
    }
}
