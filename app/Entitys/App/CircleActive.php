<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CircleActive extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring_active';
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
     * 获得动态列表
     * @param $circle_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList($circle_id)
    {
        $res = $this->where(['circle_id' => $circle_id])->orderByDesc('created_at')->paginate(20, [
            'id',
            'circle_name',
            'circle_id',
            'circle_content',
            'circle_content_img',
            'app_id',
            'user_name',
            'user_ico_img',
            'is_up',
            'like',
            'index_up',
            'have_number',
            'created_at',
        ]);
        return $res;
    }

    /**
     * 获取单条记录
     * @param $id
     * @return Model|null|static
     */
    public function getOne($id)
    {
        $res = $this->where(['id' => $id])->orderByDesc('created_at')->first([
            'id',
            'circle_name',
            'circle_id',
            'circle_content',
            'circle_content_img',
            'app_id',
            'user_name',
            'user_ico_img',
            'is_up',
            'like',
            'index_up',
            'have_number',
            'created_at',
        ]);
        return $res;
    }


    /**
     * 特地打造的首页置顶功能,专门针对一些圈主推荐的动态
     * @param $id
     * @return bool
     */
    public function indexUp($id)
    {
        return $this->where(['id' => $id])->update([
            'index_up' => 1,
            'index_up_time' => time(),
        ]);
    }

    /**
     * 获取当天的推过的动态的数量
     */
    public function getIndexUpCount($circle_id)
    {
        $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

        $res = $this
            ->where([
                'circle_id' => $circle_id,
                'index_up' => 1,
            ])
            ->where('index_up_time', '>', $start)
            ->where('index_up_time', '<', $end)
            ->count();
        return $res;
    }

    /**
     * 获得动态列表
     * @param $circle_id
     * @return \Illuminate\Support\Collection
     */
    public function getLittleList($circle_id)
    {
        $res = $this->where(['circle_id' => $circle_id])->orderByDesc('created_at')->limit(20)->get([
            'id',
            'circle_name',
            'circle_content',
            'circle_content_img',
            'app_id',
            'user_name',
            'user_ico_img',
            'is_up',
            'like',
            'index_up',
            'have_number',
            'created_at',
        ]);
        return $res;
    }

    /**
     * 获取最新的20条动态列表（分页）
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getNewList()
    {
        $res = $this->orderByDesc('created_at')->paginate(20, [
            'id',
            'circle_name',
            'circle_id',
            'circle_content',
            'circle_content_img',
            'app_id',
            'user_name',
            'user_ico_img',
            'is_up',
            'like',
            'index_up',
            'have_number',
            'created_at',
        ]);
        return $res;
    }

    /**
     * 获取最新的20条动态列表（分页）专门为首页打造
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getNewIndexList()
    {
        $res = $this->where(['index_up' => 1])->where('id', '>', '101804')->orderByDesc('index_up_time')->paginate(20, [
            'id',
            'circle_name',
            'circle_id',
            'circle_content',
            'circle_content_img',
            'app_id',
            'user_name',
            'user_ico_img',
            'is_up',
            'index_up_time',
            'like',
            'index_up',
            'have_number',
            'created_at',
        ]);
        return $res;
    }

    /**
     * 获取单个用户全部的圈子动态
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getByUser($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->orderByDesc('created_at')->get([
            'id',
            'circle_id',
            'circle_name',
            'circle_content',
            'circle_content_img',
            'app_id',
            'user_name',
            'user_ico_img',
            'is_up',
            'like',
            'index_up',
            'have_number',
            'created_at',
        ]);
        return $res;
    }

    /**
     * 获取单个用户某个的圈子动态
     * @param $app_id
     * @param $circle_id
     * @return \Illuminate\Support\Collection
     */
    public function getByUserCircle($app_id, $circle_id)
    {
        $res = $this->where(['app_id' => $app_id, 'circle_id' => $circle_id])->orderByDesc('created_at')->get([
            'id',
            'circle_id',
            'circle_name',
            'circle_content',
            'circle_content_img',
            'app_id',
            'user_name',
            'user_ico_img',
            'is_up',
            'like',
            'index_up',
            'have_number',
            'created_at',
        ]);
        return $res;
    }

    /**
     * 发布一个新的动态
     * @param $circle_id
     * @param $circle_name
     * @param $circle_content
     * @param $app_id
     * @param $user_name
     * @param $user_ico_img
     * @return $this|Model
     */
    public function pushActive($circle_id, $circle_name, $circle_content, $app_id, $user_name, $user_ico_img, $circle_content_img = 0)
    {
        $res = $this->create([
            'circle_id' => $circle_id,
            'circle_name' => $circle_name,
            'circle_content' => $circle_content,
            'circle_content_img' => $circle_content_img,
            'app_id' => $app_id,
            'user_name' => $user_name,
            'user_ico_img' => $user_ico_img,
        ]);
        return $res;
    }

    /**
     * 删除单个动态
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteActive($id)
    {
        return $this->where(['id' => $id])->delete();
    }

    /**
     * 删除所有动态
     * @param $circle_id
     * @param $app_id
     * @return bool|null
     * @throws \Exception
     */
    public function deleteAllActive($circle_id, $app_id)
    {
        return $this->where(['app_id' => $app_id, 'circle_id' => $circle_id])->delete();
    }

    /**
     * 给圈子某一些数字增加特定的值
     */
    public function addNumber($id, $column, $number)
    {
        return $this->where(['id' => $id])->update([$column => DB::raw($column . " + " . $number)]);
    }

    /**
     * 点赞
     * @param $id
     * @param $number
     * @return bool
     */
    public function like($id, $number)
    {
        return $this->where(['id' => $id])->update(['like' => DB::raw("`like` + " . $number)]);
    }

    /*
     * 通过id获取该条动态内容
     */
    public function getById($id)
    {
        return $this->where('id', $id)->first();
    }

    /*
     * 设置器
     * 存入数据库字符串自动切割，防止超过数量。
     *
     */
    public function setCircleContentAttribute($value)
    {
        $this->attributes['circle_content'] = substr($value, 0, 2400);
    }


    /*
     * 得到器
     */
    public function getUserIcoImgAttribute($value)
    {
        return $value ? $value : 'https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/CDN/default.png';
    }
}
