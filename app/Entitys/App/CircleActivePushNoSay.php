<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleActivePushNoSay extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_active_push_no_say';
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

    /*
     * 得到禁言关键词
     */
    public function getNoSay()
    {
        $list_info = $this->pluck('say');
        if ($list_info->isEmpty()) {
            return [];
        }
        return $list_info->toArray();
    }
}
