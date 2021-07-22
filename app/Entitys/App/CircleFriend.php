<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleFriend extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_friend';
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
     * 拿到用户的所有好友信息
     * @param $pt_id
     * @return \Illuminate\Support\Collection
     */
    public function getAllUserFriend($pt_id)
    {
        $res = $this->where(['app_id' => $pt_id])->get();
        return $res;
    }

    /**
     * 拿到用户的所有好友数量
     * @param $pt_id
     * @return int
     */
    public function getAllCountFriend($pt_id)
    {
        $res = $this->where(['app_id' => $pt_id])->count();
        return $res;
    }

    /**
     * 模糊查询
     * @param $key_word
     * @return \Illuminate\Support\Collection
     */
    public function getByLike($key_word)
    {
        $res = $this->where('username', 'like', '%' . $key_word . '%')->get();
        return $res;
    }

    /**
     * 增加某个用户的好友并或者记录
     * @param $app_id
     * @param $friend_id
     * @param $ico_head
     * @param $username
     * @return Model
     */
    public function addUserFriend($app_id, $friend_id, $ico_head, $username)
    {
        $res = $this->firstOrCreate([
            'app_id' => $app_id,
            'friend_id' => $friend_id,
        ], [
            'app_id' => $app_id,
            'friend_id' => $friend_id,
            'ico_head' => $ico_head,
            'username' => $username,
        ]);
        return $res;
    }

    /**
     * 获取用户是否有好友
     * @param $app_id
     * @param $friend_id
     * @return Model|null|static
     */
    public function getUser($app_id, $friend_id)
    {
        $res = $this->where([
            'app_id' => $app_id,
            'friend_id' => $friend_id,
        ])->first();
        return $res;
    }


    /**
     * 删除好友
     * @param $app_id
     * @param $friend_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteFriend($app_id, $friend_id)
    {
        $this->where([
            'app_id' => $app_id,
            'friend_id' => $friend_id,
        ])->delete();

        $this->where([
            'friend_id' => $app_id,
            'app_id' => $friend_id,
        ])->delete();

        return true;
    }
}
