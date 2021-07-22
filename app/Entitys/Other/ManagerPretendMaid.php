<?php

namespace App\Entitys\Other;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagerPretendMaid extends Model
{
    protected $connection = 'db001';
    protected $table = 'lc_manager_pretend_maid';
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
   * 生成商城的分佣记录
   */
    public function addMaidLog($data)
    {
        $res = $this->create($data);
        return $res;
    }
}
