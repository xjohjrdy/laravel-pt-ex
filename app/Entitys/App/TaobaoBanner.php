<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class TaobaoBanner extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_banner';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * banner get
     * @return \Illuminate\Support\Collection
     */
    public function getBanner()
    {
        $time = time();
        return $this
            ->where('start_time', '<', $time)
            ->where('end_time', '>', $time)
            ->orderBy('order')->get();
    }
}
