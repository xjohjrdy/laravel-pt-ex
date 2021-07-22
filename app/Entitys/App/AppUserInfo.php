<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppUserInfo extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_user';
    public $timestamps = false;

    /**
     * 获得用户的下一级(只能获取部分信息)
     * @param $user_id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getNextFloor($user_id)
    {
        $model = $this->where(['parent_id' => $user_id, 'status' => 1])->orderByDesc('create_time')->get(['id', 'phone', 'avatar', 'create_time', 'level', 'active_value', 'real_name']);
        return $model->toArray();
    }

    /**
     * 分页获得用户的下一级(只能获取部分信息)
     * @param $user_id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getNextFloorPage($user_id)
    {
        $model = $this->where(['parent_id' => $user_id, 'status' => 1])->orderByDesc('create_time')->select(['id', 'phone', 'avatar', 'create_time', 'level', 'active_value', 'real_name'])->paginate(10);
        return $model->toArray();
    }

    /**
     * 获取用户下一级直推的数量（需要制定其注册时间，默认不指定）
     * @param $user_id
     * @return int
     */
    public function getNextOneFloorCount($user_id, $time)
    {
        $model = $this->where(['parent_id' => $user_id, 'status' => 1])
            ->where('create_time', '>', $time)
            ->get(['id', 'phone', 'avatar', 'create_time', 'level', 'active_value', 'real_name']);
        return $model->count();
    }

    /**
     * 获得用户的下三级总数
     * @param $user_id
     * @return mixed
     */
    public function getNextFloorCount($user_id)
    {
        $count = DB::connection('app38')
            ->select(
                "
         SELECT
((
SELECT
   count(*)
FROM
   lc_user t1
   RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
   RIGHT JOIN lc_user t3 ON t2.id = t3.parent_id
   RIGHT JOIN lc_user t4 ON t3.id = t4.parent_id
WHERE
   t1.id = {$user_id}
AND
   t4.status = 1
)+(
SELECT
   count(*)
FROM
   lc_user t1
   RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
   RIGHT JOIN lc_user t3 ON t2.id = t3.parent_id
WHERE
   t1.id = {$user_id}
AND
   t3.status = 1
)+(
SELECT
   count(*)
FROM
   lc_user t1
   RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
WHERE
   t1.id = {$user_id}
AND
   t2.status = 1
   )
) as user_count
              ");

        return $count[0]->user_count;
    }

    /**
     * 根据用户id获取用户信息
     * @param $user_id
     * @return Model|null|static
     */
    public function getUserById($user_id)
    {
        $model = $this->where(['id' => $user_id])->first();
        return $model;
    }

    /**
     * 通过电话拿到用户的信息
     * @param $phone
     * @return Model|null|static
     */
    public function getUserByPhone($phone)
    {
        $model = $this->where(['phone' => (string)$phone])->first();
        return $model;
    }

    /**
     * 修改当前用户的密码
     * @param $phone
     * @param $password
     * @return bool
     */
    public function changePassword($phone, $password)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $res = $this->where(['phone' => (string)$phone])->update([
            'password' => $password
        ]);
        return $res;
    }

    /**
     * 增加附加活跃度
     * @param int $value
     * @return int
     */
    public function addUserActiveAppend($value, $app_id)
    {
        return $this->where(['id' => $app_id])->update(['append_active_value' => DB::raw("append_active_value + " . $value)]);
    }

    /**
     * 更新用户的登录信息
     * @param $app_id
     * @return bool
     */
    public function updateUserLogin($app_id, $device_id)
    {
        return 1;
    }

    /**
     * 退出登录
     * @param $app_id
     * @return bool
     */
    public function logoutUser($app_id)
    {
        return $this->where(['id' => $app_id])->update([
            'is_online' => 0,
        ]);
    }

    /*
     * 通过app_id 查询用户数据
     */
    public function getUserData($app_id)
    {
        $obj_user_info = $this
            ->where('id', $app_id)
            ->first([
                'id',
                'avatar',
                'user_name',
                'order_can_apply_amount',
                'order_amount',
                'order_can_apply_amount',
                'order_amount'
            ]);
        return $obj_user_info;
    }

    /*
     * 三级以内，包括自己，团队的总订单数
     */
    public function getThreeInFo($app_id, $time)
    {
        $res_data = DB::connection('app38')
            ->select("
            SELECT SUM(tt1.price) as money,COUNT(*) as number
                FROM lc_shop_orders as tt1
                INNER JOIN (
                        SELECT 
                                *
                        FROM
                                lc_user
                        WHERE
                                id = {$app_id}
                        UNION
                        SELECT
                                t2.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id 
                        WHERE
                                t1.id = {$app_id}
                        UNION
                        SELECT
                                t3.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                INNER JOIN lc_user t3 ON t2.id = t3.parent_id 
                        WHERE
                                t1.id = {$app_id}
                        UNION
                        SELECT
                                t4.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                                INNER JOIN lc_user t4 ON t3.id = t4.parent_id 
                        WHERE
                                t1.id = {$app_id}  
                ) as tt2
                on tt1.app_id = tt2.id 
                WHERE tt1.status = 3
                AND tt2.status = 1
                AND tt1.created_at > '{$time}'
            ");
        if (empty($res_data[0])) {
            return ['money' => 0, 'number' => 0];
        }
        return $res_data[0];
    }

    /*
     * 根据app_id得到用户数据
     */
    public function getUserInfo($app_id)
    {
        return $this->where('id', $app_id)->first();
    }

    /*
     * 取得偶像用户的数据
     */
    public function getIdolInfo($parent_id)
    {
        return $this->where('id', $parent_id)->first(['id']);
    }

    /*
     * 得到指定用户偶像名
     */
    public function getTaegetUserName($parent_id)
    {
        $data = $this->where(['id' => $parent_id])->first(['id', 'real_name']);
        if (empty($data)) return false;
        //展示数据修改
        $data = $data->toArray();
        $parent_id = $data['id'];
        if ($parent_id >= 10000000) {
            $new_app_id = base_convert($parent_id, 10, 33); // 10 转 33
            $data['id'] = 'x' . $new_app_id;
        }

        return empty($data['real_name']) ? $data['id'] : $data['real_name'];
    }

    /*
     * 得到偶像数据信息
     */
    public function getParentUserInfo($app_id)
    {
        return $this->where(['id' => $app_id])
            ->first(['id as parent_id', 'real_name', 'phone', 'create_time']);
    }

    /*
     * 验证偶像id和用户id是否对应
     */
    public function isCorresponding($app_id, $parent_id)
    {
        return $this->where(['id' => $app_id, 'parent_id' => $parent_id])->value('id');
    }

    /*
     * 根据id得到用户数据
     */
    public function getUserAssignInfo($app_id)
    {
        return $this->where(['id' => $app_id, 'status' => 1])
            ->first([
                'alipay',
                'apply_cash_amount',
                'bonus_amount',
                'order_amount',
                'order_can_apply_amount',
            ]);
    }

    /*
     * 根据id得到用户支付宝数据
     */
    public function getCheckApply($app_id)
    {
        return $this->where('id', $app_id)->value('alipay');
    }

    /*
     * 得到下级粉丝数
     */
    public function getChildrenCount($app_id)
    {
        return (int)$this->where(['parent_id' => $app_id, 'status' => 1])->count('id');
    }

    /*
    * 得到下级全部id
    */
    public function getChildrenId($app_id)
    {
        return $this->where(['parent_id' => $app_id, 'status' => 1])->pluck('id');
    }

    /*
     * 得到3级以内粉丝数
     */
    public function getNewThreeFloorChildrenCount($app_id)
    {
        $res = DB::connection('app38')
            ->select("
                        SELECT
                ((
                SELECT
                   count(*)
                FROM
                   lc_user t1
                   RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
                   RIGHT JOIN lc_user t3 ON t2.id = t3.parent_id
                   RIGHT JOIN lc_user t4 ON t3.id = t4.parent_id
                WHERE
                   t1.id = {$app_id}
                AND
                   t4.status = 1
                )+(
                SELECT
                   count(*)
                FROM
                   lc_user t1
                   RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
                   RIGHT JOIN lc_user t3 ON t2.id = t3.parent_id
                WHERE
                   t1.id = {$app_id}
                AND
                   t3.status = 1
                )+(
                SELECT
                   count(*)
                FROM
                   lc_user t1
                   RIGHT JOIN lc_user t2 ON t1.id = t2.parent_id
                WHERE
                   t1.id = {$app_id}
                AND
                   t2.status = 1
                   )
                ) as ct1
        ");
        $total = 0;
        $total += (float)$res[0]->ct1;
        return $total;
    }

    /*
     * 根据id得到指定字段数据
     */
    public function getFieldsData($app_id, $fields)
    {
        return (int)$this->where(['id' => $app_id, 'status' => 1])->value($fields);
    }

    /*
     * 得到3级内的人数
     */
    public function getThreeFloorChildrenCount($app_id)
    {
        $res = DB::connection('app38')
            ->select("
            SELECT
			((
			SELECT
				count(*)
			FROM
				lc_user t1
				INNER JOIN lc_user t2 ON t1.id = t2.parent_id
				INNER JOIN lc_user t3 ON t2.id = t3.parent_id
				INNER JOIN lc_user t4 ON t3.id = t4.parent_id
			WHERE
				t1.id = {$app_id} and t4.status =1
			)+(
			SELECT
				count(*)
			FROM
				lc_user t1
				INNER JOIN lc_user t2 ON t1.id = t2.parent_id
				INNER JOIN lc_user t3 ON t2.id = t3.parent_id
			WHERE
				t1.id = {$app_id} and t3.status =1
			)+(
			SELECT
				count(*)
			FROM
				lc_user t1
				INNER JOIN lc_user t2 ON t1.id = t2.parent_id
			WHERE
				t1.id ={$app_id} and t2.status =1) 
			) as ct1
            ");
        return (int)$res[0]->ct1;
    }

    /*
     * 验证申请等级是否成功
     */
    public function checkApplyUpgrade($app_id, $user_level, $goal_level)
    {
        $datas = $this->where(['parent_id' => $app_id])->get(['id', 'level']);
        $match_num = 0;
        if (count($datas) >= 10) {
            foreach ($datas as $data) {
                if ($data->level == $user_level) {
                    $match_num += 1;
                } else {
                    $children_ids[] = $data->id;
                }
            }
            if ($match_num >= 10) {
                return $goal_level;
            } else {
                if (empty($children_ids)) {
                    return 0;
                }
                $floor = 1;
                while ($floor <= 3) {
                    $query_ids = implode(',', $children_ids);
                    $total = $this->getTeamFloorLevelNum($query_ids, $floor, $user_level);
                    $pass_children_ids = array_column($total, 'id');
                    $pass_num = count($pass_children_ids);
                    $match_num += $pass_num;
                    if ($match_num >= 10) {
                        return $goal_level;
                    }
                    $children_ids = array_diff($children_ids, $pass_children_ids);
                    $floor++;
                }
            }
        }
        return 0;
    }

    /*
     * 得到团队指定层级人员总数,分组查询
     */
    public function getTeamFloorLevelNum($query_ids, $n, $user_level)
    {
        $where_sql = $this->makeFloorSql($n);
        $res = DB::connection('app38')
            ->select("
            select count(u.id) as total,a.id
from `lc_user` as u
inner join (select id from lc_user where id in ({$query_ids})) as a
on u.parent_id in 
{$where_sql}
where 
u.level = {$user_level} AND 
u.status = 1 
group by a.id
            ");
        return $res;
    }

    /*
     *得到指定层级数据
     */
    public function makeFloorSql($n, $sql = '(a.id)')
    {
        $i = 1;
        while ($i < $n) {
            $sql = "(select id from lc_user where parent_id in {$sql})";
            $i++;
        }
        return $sql;
    }

    /*
     * 修改用户昵称
     */
    public function updateUserInfoWithIM($app_id, $user_name)
    {
        return $this->where(['id' => $app_id])->update(['user_name' => $user_name]);
    }

    /*
     * 修改用户头像
     */
    public function updateUserInfoAvatar($app_id, $avatar)
    {
        return $this->where(['id' => $app_id])->update(['avatar' => $avatar]);
    }

    /*
     * 修改支付宝
     */
    public function updateAlipayInfo($app_id, $alipay, $real_name)
    {
        return $this->where('id', $app_id)->update(['alipay' => $alipay, 'real_name' => $real_name]);
    }

    /*
     * 新增用户
     */
    public function addWith($data)
    {
        $new = new AppUserInfo();
        $new->user_name = $data['phone'];
        $new->real_name = "";
        $new->avatar = "";
        $new->phone = $data['phone'];
        $new->password = bcrypt($data['password']);
        $new->alipay = "";
        $new->level = 1;
        $new->parent_id = empty($data['parent_id']) ? 0 : $data['parent_id'];
        $new->up_three_floor = "";
        $new->up_four_floor = "";
        $new->status = 1;
        $new->create_time = time();
        $new->active_value = 0;
        $new->append_active_value = 0;
        $new->sign_active_value = 0;
        $new->order_num_active_value = 0;
        $new->history_active_value = 0;
        $new->bonus_amount = 0;
        $new->order_amount = 0;
        $new->apply_cash_amount = 0;
        $new->next_month_cash_amount = 0;
        $new->current_month_passed_order = 0;
        $new->order_can_apply_amount = empty($data['order_can_apply_amount']) ? 0 : $data['order_can_apply_amount'];
        $new->sign_number = 0;
        $new->level_modify_time = time();
        $new->apply_status = 2;
        $new->save();
        return $new->id;
    }

    /*
     * 检测手机号是否绑定用户
     */
    public function isBindingByPhone($phone)
    {
        $data = $this->where('phone', $phone)->get();
        $datas = [];
        foreach ($data as $value) {
            $datas[$value->id] = $value;
        }
        return $datas;
    }

    /*
     * 激活用户
     */
    public function activeUserById($id, $password)
    {
        DB::beginTransaction();
        try {
            $user = $this->find($id);
            $user->status = 1;
            $user->password = $password;
            $user->save();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    //获取直属下级人数
    function getRangeChildrenCount($user_ids)
    {
        $user_ids = is_array($user_ids) ? implode(',', $user_ids) : $user_ids;
        if (empty($user_ids)) {
            return false;
        }
        $sql = "
        select count(u.id) as count,a.id
        from `lc_user` as u
        inner join (select id from lc_user where id in ({$user_ids})) as a
        on u.parent_id in (a.id)
        where u.status = 1 
        group by a.id";
        $res = DB::connection('app38')->select($sql);
        $res = array_column($res, 'count', 'id');
        return $res;
    }
}
