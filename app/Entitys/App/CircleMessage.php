<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CircleMessage extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_friend_message';
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
    public function getItemList($app_id, $to_app_id, $page = 0, $where = [])
    {
        $limit = 10;
        $where_me = [
            'app_id' => $app_id,
            'to_app_id' => $to_app_id
        ];
        $where_to = [
            'app_id' => $to_app_id,
            'to_app_id' => $app_id
        ];

        $msg_list_me = $this
            ->where($where_me)
            ->select('id', 'message', 'created_at', DB::raw('1 as msg_type'));

        $msg_list = $this
            ->where($where_to)
            ->select('id', 'message', 'created_at', DB::raw('2 as msg_type'))
            ->union($msg_list_me)
            ->latest('id')
            ->offset($page * $limit)
            ->limit($limit)
            ->get();


        return $msg_list;
    }
    public function addMsg($app_id, $to_app_id, $msg)
    {
        try {
            $params['app_id'] = $app_id;
            $params['to_app_id'] = $to_app_id;
            $params['message'] = $msg;
            $res = $this->create($params);
        } catch (\Exception $e) {
            return false;
        }
        return $res;

    }
}
