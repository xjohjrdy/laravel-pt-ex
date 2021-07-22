<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCheckAllFunction extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_user_check_all_function';
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
     *
     */
    public function getOne($app_id)
    {
        return $this->where(['app_id' => $app_id])->first();
    }

    /**
     * update special
     */
    public function addNewOne($data)
    {
        return $this->create($data);
    }

    public function updateOne($app_id, $data)
    {
        return $this->where(['app_id' => $app_id])->update($data);
    }

    /*
     * 增加认证记录
     */
    public function addProveSign($data)
    {
        return $this->create([
            'app_id' => $data['app_id'],
            'you_order' => $data['you_order'],
            'my_order' => $data['my_order'],
        ]);
    }

}
