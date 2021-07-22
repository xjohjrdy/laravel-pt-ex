<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleCardcase extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_cardcase';
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
     * 查询用户当前的名片
     * @param $app_id
     * @param int $user
     * @return Model
     */
    public function getUserInfo($app_id, $user = 0)
    {
        if (!$user) {
            $res = $this->firstOrCreate([
                'app_id' => $app_id,
            ], [
                'app_id' => $app_id,
                'username' => '未设置',
                'ico_img' => '未设置',
                'content' => '未设置',
                'wechat' => '未设置',
                'area' => '未设置',
                'qq' => '未设置',
                'phone' => '未设置',
                'talk' => '未设置',
            ]);
        } else {
            $res = $this->firstOrCreate([
                'app_id' => $app_id,
            ], [
                'app_id' => $app_id,
                'username' => $user->user_name,
                'ico_img' => $user->avatar,
                'content' => '未设置',
                'wechat' => '未设置',
                'area' => '未设置',
                'qq' => '未设置',
                'phone' => $user->phone,
                'talk' => '未设置',
            ]);
        }
        return $res;
    }

    /**
     * 更新用户名片
     * @param $app_id
     * @param $data
     * @return bool
     */
    public function updateCardcase($app_id, $data)
    {
        $res = $this->where(['app_id' => $app_id])->update($data);
        return $res;
    }
}
