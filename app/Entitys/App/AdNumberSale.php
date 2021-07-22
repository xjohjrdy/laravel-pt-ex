<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdNumberSale extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_ad_number_sale';
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

    public function getPackages(){
        return $this->orderBy('use_money','asc')->get(['id','use_money', 'much']);
    }

    public function getPackageById($id){
        return $this->where(['id' => $id])->first();
    }
}
