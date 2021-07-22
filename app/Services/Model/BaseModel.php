<?php

namespace App\Services\Model;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $connection = 'a1191125678';
    public $timestamps = false;
    protected function table($table){
        $this->table = $table;
        return $this;
    }

}
