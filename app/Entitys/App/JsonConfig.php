<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;

class JsonConfig extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_json_config';
    public $timestamps = false;
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];


    public function getValue($key)
    {
        $ser_v = $this->where(['key' => $key])->value('value');
        if (empty($ser_v)) return false;
        return unserialize($ser_v);
    }

}
