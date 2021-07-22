<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class StartPageDown extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_start_page_can_down';
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

    public function addDown($type){
        $date = date('Y-m-d', time());
        $down = $this->where(['date' => $date, 'type' => $type]);
        if($down->exists()){
            $down->update(['much' => DB::raw('much + 1')]);
        } else {
            $this->create([
                'date' => $date,
                'type' => $type,
                'much' => 1,
            ]);
        }

    }
}
