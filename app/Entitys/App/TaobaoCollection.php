<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaobaoCollection extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_taobao_collection';
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
     * 插入或者更新收藏数据
     * @param $data
     * @return Model
     */
    public function addResult($data)
    {
        return $this->updateOrCreate([
            'app_id' => $data['app_id'],
            'share_url' => $data['share_url']
        ], $data);
    }

    /**
     * 获取当前用户所有收藏
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getAll($app_id)
    {
        return $this->where([
            'app_id' => $app_id
        ])->get();
    }

    /**
     * 获取当前用户单个信息
     * @param $app_id
     * @param $share_url
     * @return Model|null|static
     */
    public function getOne($app_id, $share_url)
    {
        return $this->where([
            'app_id' => $app_id,
            'share_url' => $share_url,
        ])->first();
    }
}
