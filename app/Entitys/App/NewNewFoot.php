<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewNewFoot extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_new_new_foot';
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
     * 增加足迹
     */
    public function addFoot($data)
    {
        return $this->create($data);
    }

    /**
     * 拿
     * @param $id
     */
    public function getById($id)
    {
        return $this->where(['app_id' => $id])->orderBy('created_at', 'desc')->paginate(10);
    }

    public function one($itemid, $app_id)
    {
        return $this->where(['app_id' => $app_id, 'good_id' => $itemid])->first();
    }

    /**
     * 删
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    public function del($id)
    {
        return $this->where(['id' => $id])->delete();
    }

    /**
     * 删
     * @param $arr_id
     * @return bool|null
     * @throws \Exception
     */
    public function delAll($arr_id)
    {
        return $this->whereIn('id', $arr_id)->delete();
    }
}
