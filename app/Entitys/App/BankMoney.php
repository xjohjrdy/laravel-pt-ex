<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankMoney extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_bank_money';
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
     * 查询内容
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllType($type = 0)
    {
        $res = $this->where(['type' => $type])->get();
        return $res;
    }

    /**
     * 找到唯一的一个字段值
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public function getById($id)
    {
        $res = $this->where(['id'=>$id])->first();
        return $res;
    }
}
