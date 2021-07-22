<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CircleRing extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_circle_ring';
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
     * 以id为标准查询（需要搭配用户加入情况表）
     * @param $id
     * @param int $all
     * @return Model|null|static
     */
    public function getById($id, $all = 0)
    {
        if (!$all) {
            $res = $this->where(['id' => $id])->first(['id', 'back_img', 'buy_number', 'show_message', 'ico_img', 'app_id', 'ico_title', 'desc', 'area', 'area_land', 'use_time', 'number_person', 'number_zone', 'number_anima', 'price', 'close', 'add_price']);
        } else {
            $res = $this->where(['id' => $id])->first();
        }
        return $res;
    }

    /**
     * 模糊查询
     * @param $key_word
     * @return \Illuminate\Support\Collection
     */
    public function getByLike($key_word)
    {
        $res = $this->where('ico_title', 'like', '%' . $key_word . '%')->get(['ico_img', 'ico_title']);
        return $res;
    }

    /**
     * 通过城市id找到对应的圈子
     * @param $king_id
     * @return \Illuminate\Support\Collection
     */
    public function getByKingId($king_id)
    {
        $res = $this->where(['king_id' => $king_id])->get(['id', 'back_img', 'app_id', 'ico_img', 'ico_title', 'desc', 'area', 'area_land', 'use_time', 'number_person', 'number_zone', 'number_anima', 'price', 'close', 'add_price']);
        return $res;
    }

    /**
     * 找到全国所有的圈子
     * @return \Illuminate\Support\Collection
     */
    public function getAll()
    {
        $res = $this->get(['id', 'back_img', 'app_id', 'ico_img', 'ico_title', 'desc', 'area', 'area_land', 'use_time', 'number_person', 'number_zone', 'number_anima', 'price', 'close', 'add_price']);
        return $res;
    }

    /**
     * 获取当前城市的圈子特定
     * @param $king_id
     * @param $ico_title
     * @return \Illuminate\Support\Collection
     */
    public function getByKingIdAndTitle($king_id, $ico_title)
    {
        $res = $this->where(['king_id' => $king_id])
            ->where('ico_title', 'like', '%' . $ico_title . '%')
            ->get(['id', 'back_img', 'ico_img', 'ico_title', 'desc', 'area', 'area_land', 'use_time', 'number_person', 'number_zone', 'number_anima', 'price', 'close', 'add_price']);
        return $res;
    }

    /**
     * 拿到下三级用户，成为圈主的总数
     * @param $app_id
     * @return array
     */
    public function getAllCountNextThree($app_id)
    {
        $sql = "
               select count(*) as res from `lc_circle_ring` as uo
        INNER JOIN (
        SELECT
                        *
        FROM
                        lc_user
        WHERE
                        id = " . $app_id . "
        UNION
        SELECT
                        t2.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
        WHERE
                        t1.id = " . $app_id . "
        UNION
        SELECT
                        t3.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
        WHERE
                        t1.id = " . $app_id . "
        UNION
        SELECT
                        t4.*
        FROM
                        lc_user t1
                        INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                        INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                        INNER JOIN lc_user t4 ON t3.id = t4.parent_id
        WHERE
                        t1.id = " . $app_id . "
        ) as tt2
        on uo.app_id = tt2.id
        ";

        $res = DB::connection("app38")->select($sql);
        return $res[0]->res;
    }

    /**
     * 以分类为标准查询
     * @param $type
     * @return \Illuminate\Support\Collection
     */
    public function getByType($type = 0)
    {
        if ($type) {
            $res = $this->where(['keyword' => $type])->get(['id', 'back_img', 'ico_img', 'ico_title', 'desc', 'number_person', 'number_zone', 'number_anima', 'price', 'close']);
        } else {
            $res = $this->limit(10)->get(['id', 'back_img', 'ico_img', 'ico_title', 'desc', 'number_person', 'number_zone', 'number_anima', 'price', 'close']);
        }
        return $res;
    }

    /**
     * 以用户为标准查询
     * @param $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getByUser($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->get(['id', 'back_img', 'ico_img', 'ico_title', 'desc', 'number_person', 'number_zone', 'number_anima', 'price', 'close', 'use']);
        return $res;
    }

    /**
     * 推荐字段查询
     * @return \Illuminate\Support\Collection
     */
    public function getByRecommend()
    {
        $res = $this->where(['recommend' => 1])->get(['id', 'back_img', 'ico_img', 'ico_title', 'desc', 'number_person', 'number_zone', 'number_anima', 'price', 'close']);
        return $res;
    }

    /**
     * 模糊标题查询
     * @param $title
     * @param int $app_id
     * @return \Illuminate\Support\Collection
     */
    public function getByLikeTitle($title, $app_id = 0)
    {
        if ($app_id) {
            $res = $this->where('ico_title', 'like', '%' . $title . '%')->get(['ico_img', 'ico_title', 'desc', 'number_person', 'number_zone', 'number_anima', 'price', 'close']);

        } else {
            $res = $this->where('ico_title', 'like', '%' . $title . '%')
                ->where('app_id', '=', $app_id)
                ->get(['id', 'back_img', 'ico_img', 'ico_title', 'desc', 'number_person', 'number_zone', 'number_anima', 'price', 'close']);
        }
        return $res;
    }

    /**
     * 更新所有或者部分信息的内容
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateLittle($id, $data)
    {
        $res = $this->where(['id' => $id])->update($data);
        return $res;
    }


    /**
     * 通过查询条件查询该表第一条内容
     * @param $where
     * @return Model|null|static
     */
    public function getInfo($where)
    {
        return $this->where($where)->first();
    }

    /**
     * 给圈子某一些数字增加特定的值
     */
    public function addNumber($id, $column, $number)
    {
        return $this->where(['id' => $id])->update([$column => DB::raw($column . " + " . $number)]);
    }

    /*
     *  通过条件创建该条圈子记录
     */
    public function createRing($params)
    {
        return $this->create($params);
    }
}
