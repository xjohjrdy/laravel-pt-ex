<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleCheckInfo extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_article_check_info';
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


    public function getLastLog($app_id)
    {
        $log = $this->where(['app_id' => $app_id])->orderBy("check_time", "desc")->first();
        return $log;
    }

    /**
     * 获得用户操作记录总数
     * @param $uid
     * @param $operation
     * @param $dateline
     * @return int
     */
    public function getCountLog($app_id, $time)
    {
        $log = $this->where(['app_id' => $app_id, 'check_time' => $time])->count();
        return $log;
    }

    /**
     * @param $data
     * @return $this|Model
     */
    public function addInfo($data)
    {
        return $this->create($data);
    }
}
