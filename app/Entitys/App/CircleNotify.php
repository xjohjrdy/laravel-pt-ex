<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CircleNotify extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_friend_notify';
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
     * 推送群聊的信息
     * @param $to_app_id
     * @param $from_app_id
     * @param $notify_info
     * @param $username
     * @param $ico
     * @return Model
     */
    public function pushGroupNotify($to_app_id, $from_app_id, $notify_info, $username, $ico)
    {
        $notify_info = mb_substr($notify_info, 0, 16, 'utf-8');
        return $this->updateOrCreate([
            'app_id' => $to_app_id,
            'from_app_id' => $from_app_id,
            'jump' => 3,
        ], [
            'app_id' => $to_app_id,
            'from_app_id' => $from_app_id,
            'username' => $username,
            'notify_info' => $notify_info,
            'ico' => $ico,
            'is_read' => DB::raw("is_read + " . 1),
            'jump' => 3,
        ]);
    }

    /**
     * 获取某一类型未读的总数
     * @param $app_id
     * @return mixed
     */
    public function getOneAllCount($app_id, $jump)
    {
        return $this->where([
            'app_id' => $app_id,
            'jump' => $jump,
        ])->sum('is_read');
    }

    /**
     * 获取某一类型未读的总数
     * @param $circle_id
     * @param $jump
     * @return mixed
     */
    public function getOneAllCircleCount($circle_id, $jump)
    {
        return $this->where([
            'from_app_id' => $circle_id,
            'jump' => $jump,
        ])->sum('is_read');
    }

    /**
     * 获取申请通过的关键词信息数据
     * 取自下方的putmsg里面的关键词
     */
    public function getSpecialMsg($app_id)
    {
        return $this->where([
            'notify_info' => '你们已经是好友了',
            'app_id' => $app_id,
            'jump' => 2,
        ])->get();
    }

    /**
     * 用户申请好友时候发送通知
     * 以及
     * 用户同意好友请求时候发送通知
     * @param $to_app_id
     * @param $from_app_id
     * @param $username
     * @param $ico
     * @param bool $is_ok
     * @return bool
     */
    public function putMsg($to_app_id, $from_app_id, $username, $ico, $is_ok = false)
    {
        try {
            if ($is_ok) {
                $notify_info = '请求添加您为好友';
                $jump = 1;
            } else {
                $notify_info = '你们已经是好友了';
                $jump = 2;
                $this->updateOrCreate([
                    'app_id' => $from_app_id,
                    'from_app_id' => $to_app_id,
                    'jump' => 1,
                ], [
                    'notify_info' => $notify_info,
                    'is_read' => 1,
                    'jump' => $jump,
                ]);
                $this->updateOrCreate([
                    'app_id' => $to_app_id,
                    'from_app_id' => $from_app_id,
                    'jump' => 1,
                ], [
                    'app_id' => $to_app_id,
                    'from_app_id' => $from_app_id,
                    'username' => $username,
                    'notify_info' => $notify_info,
                    'is_read' => 1,
                    'ico' => $ico,
                    'jump' => $jump,
                ]);
            }
            $this->updateOrCreate([
                'app_id' => $to_app_id,
                'from_app_id' => $from_app_id,
                'jump' => $jump,
            ], [
                'app_id' => $to_app_id,
                'from_app_id' => $from_app_id,
                'username' => $username,
                'notify_info' => $notify_info,
                'ico' => $ico,
                'jump' => $jump,
            ]);
        } catch (\Exception $e) {
            return false;
        }

        return true;

    }

    public function getMsgList($app_id, $where = [])
    {
        $where['app_id'] = $app_id;
        $msg_list = $this
            ->where($where)
            ->orderByDesc('updated_at')
            ->select('id', 'username', 'notify_info', 'from_app_id', 'ico', 'is_read', 'jump', 'updated_at')
            ->get();
        return $msg_list;
    }
    public function upMsg($username, $ico, $app_id, $to_app_id, $notify = '收到一条消息')
    {
        try {
            $where = [
                'from_app_id' => $app_id,
                'app_id' => $to_app_id,
            ];
            if ($this->where($where)->whereIn('jump', [1, 2])->exists()) {
                $params['username'] = $username;
                $params['ico'] = $ico;
                $params['notify_info'] = $notify;
                $params['jump'] = 2;
                $res = $this->where($where)->whereIn('jump', [1, 2])->increment('is_read', 1, $params);
            } else {
                $res = $this->create([
                    'app_id' => $to_app_id,
                    'from_app_id' => $app_id,
                    'username' => $username,
                    'notify_info' => $notify,
                    'ico' => $ico,
                    'is_read' => 1,
                    'jump' => 2,
                ]);
            }
        } catch (\Exception $e) {
            return false;
        }
        try {
            $where = [
                'from_app_id' => $to_app_id,
                'app_id' => $app_id,
            ];
            $appUserInfo = new AppUserInfo();
            $app_user_info = $appUserInfo->getUserById($to_app_id);
            if ($this->where($where)->whereIn('jump', [1, 2])->exists()) {
                $params['username'] = $app_user_info->user_name;
                $params['ico'] = $app_user_info->avatar;
                $params['notify_info'] = $notify;
                $params['jump'] = 2;
                $res = $this->where($where)->whereIn('jump', [1, 2])->increment('is_read', 0, $params);
            } else {
                $res = $this->create([
                    'app_id' => $app_id,
                    'from_app_id' => $to_app_id,
                    'username' => $app_user_info->user_name,
                    'notify_info' => $notify,
                    'ico' => $app_user_info->avatar,
                    'is_read' => 0,
                    'jump' => 2,
                ]);
            }
        } catch (\Exception $e) {
            return false;
        }

        return $res;
    }
    public function read($app_id, $to_app_id, $jump)
    {
        try {
            $where = [
                'app_id' => $app_id,
                'from_app_id' => $to_app_id,
                'jump' => $jump,
            ];

            $res = $this->where($where)->update(['is_read' => 0]);
        } catch (\Exception $e) {
            return false;
        }
        return $res;
    }

    /*
     * 通过消息ID消息修改为已读
     */
    public function readById($id)
    {
        try {
            $where = [
                'id' => $id,
            ];

            $res = $this->where($where)->update(['is_read' => 0]);

        } catch (\Exception $e) {
            return false;
        }
        return $res;
    }

    /*
     * 设置器
     * 通知数量截取 notify_info
     */
    public function setNotifyInfoAttribute($value)
    {
        $this->attributes['notify_info'] = substr($value, 0, 90);
    }
}
