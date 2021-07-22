<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalShopGoods extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_shop_goods';
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
     * index for
     * @return \Illuminate\Support\Collection
     */
    public function getIndex()
    {
        return $this->limit(6)->where(['show_index' => 1])->get();
    }

    /**
     * all for
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAll()
    {
        return $this->paginate(10);
    }
}
