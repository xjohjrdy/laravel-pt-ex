<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 签到表（v201907）
 */
class ActiveRealSign extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_active_real_sign';
    
    /**
     * 新增忽略数据冲突
     * @param array $array
     * @return void
     */
    public static function insertIgnore($array){
        $a = new static();
        DB::connection($a->connection)->statement('INSERT IGNORE INTO '.$a->table.' ('.implode(',',array_keys($array)).') values (?'.str_repeat(',?',count($array) - 1).')',array_values($array));
    }
}