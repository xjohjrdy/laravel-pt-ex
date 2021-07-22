<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class ActiveCount extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_active_count';
    public $timestamps = false;

    /**
     * 插入或者更新数据
     * @param $key
     * @param $item
     */
    public function checkInsert($key, $item)
    {
        $this->updateOrInsert(['pt_id' => $key, 'type' => 1], [
            'pt_id' => $key,
            'type' => 1,
            'update_time' => time(),
            'value' => $item
        ]);
    }
}
