<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CircleRingAdd extends Model
{

    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_add';
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
     * 通过用户条件获取圈子所有信息
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getByAppId($app_id, $limit = 0)
    {
        if ($limit) {
            $res = $this->where(['app_id' => $app_id])->limit($limit)->get(['circle_id', 'status', 'use']);
        } else {
            $res = $this->where(['app_id' => $app_id])->get(['circle_id', 'status', 'use']);
        }
        return $res;
    }

    /**
     * 获取当前用户加入圈子的总数
     * @param $app_id
     * @return int
     */
    public function getAllUserCount($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->count();
        return $res;
    }

    /**
     * 通过条件和状态查询筛选用户所需要的分类
     * @param $app_id
     * @param $status
     * @return \Illuminate\Support\Collection
     */
    public function getByAppIdFilter($app_id, $status)
    {
        $res = $this->where(['app_id' => $app_id, 'status' => $status])->get(['circle_id', 'status', 'use']);
        return $res;
    }

    /**
     * 获得当前圈子所有的用户列表
     * @param $circle_id
     * @return \Illuminate\Support\Collection
     */
    public function getAllByCircle($circle_id)
    {
        $res = $this->where(['circle_id' => $circle_id])->get(['app_id', 'real_name', 'status', 'no_say']);
        return $res;
    }

    /**
     * 获得当前圈子所有的用户列表
     * @param $circle_id
     * @return \Illuminate\Support\Collection
     */
    public function getThreeByCircle($circle_id)
    {
        $res = $this->where(['circle_id' => $circle_id])->limit(3)->get(['app_id', 'real_name', 'status', 'no_say']);
        return $res;
    }

    /**
     * 更新当前用户对于圈子的状态
     * @param $circle_id
     * @param $app_id
     * @param $data
     * @return bool
     */
    public function updateOneUser($circle_id, $app_id, $data)
    {
        $res = $this->where(['app_id' => $app_id, 'circle_id' => $circle_id])->update($data);
        return $res;
    }

    /**
     * 获取圈子和用户唯一的加入信息
     * @param $circle_id
     * @param $app_id
     * @return Model|null|static
     */
    public function getByAppCircle($circle_id, $app_id)
    {
        $res = $this->where(['app_id' => $app_id, 'circle_id' => $circle_id])->first();
        return $res;
    }

    /**
     * 删除掉某一个唯一的数据
     * @param $circle_id
     * @param $app_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteRingAdd($circle_id, $app_id)
    {
        $res = $this->where(['app_id' => $app_id, 'circle_id' => $circle_id])->delete();
        return $res;
    }

    /**
     * * 通过圈子id查询该圈子用户人数
     * @param $circle_id
     * @return int
     */
    public function getSum($circle_id)
    {
        return $this->where('circle_id', $circle_id)->count();
    }

    /**
     * 创建加入记录
     * @param $data
     * @return $this|Model
     */
    public function createAdd($data)
    {
        $res = $this->create($data);
        return $res;
    }
}
