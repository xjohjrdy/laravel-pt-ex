<?php

namespace App\Entitys\Article;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'tbl_agent';
    public $timestamps = false;

    /**
     * 根据app_id查询用户
     * @param $pt_id
     * @return Model|null|static
     */
    public function getAgent($pt_id)
    {
        $user = $this->where('pt_id', $pt_id)->first();
        return $user;
    }

    /**
     *
     * 校验用户的头条数量是否不符合使用条件
     * @param $pt_id
     * @return mixed
     */
    public function checkNumber($pt_id)
    {
        $agent = $this->where('pt_id', $pt_id)->first(['number']);
        $number = empty($agent->number) ? 0 : $agent->number;
        return $number;
    }

    /**
     *
     * 更新文章数量
     * @param $app_id
     * @param $number
     * @return bool
     */
    public function decrementUserArticle($app_id, $number)
    {
        $res = $this->where(['pt_id' => $app_id])->update(['number' => $number]);
        return $res;
    }
}
