<?php

namespace App\Entitys\App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ShopGoods extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_shop_goods';


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
     * 获取供应商对应的商品信息
     */
    public function getBySupplier($supplier_id)
    {
        return $this->where(['shop_id' => $supplier_id])->paginate(10);
    }

    /**
     * 创建一个商品
     * @param $data
     * @return $this|Model
     */
    public function newGoods($data, $area_good_id)
    {
        if (empty($area_good_id)) {
            $res = $this->create($data);
        } else {
            $res = $this->where([
                'id' => $area_good_id
            ])->update($data);
        }

        return $res;
    }

    /**
     * 批量获取商品的列表
     * @param int $sort
     * @param int $title
     * @return \Illuminate\Database\Eloquent\Collection|int|static[]
     */
    public function getAllGoods($sort = 0, $title = 0)
    {
        $res = 0;
        if ($title) {
            $res = $this->where('title', 'like', '%' . $title . '%')
                ->where(['status' => 1])
                ->orderByDesc('weight')
                ->orderBy('id', 'desc')
                ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        }

        if ($sort) {
            $res = $this->where(['sort' => $sort])
                ->where(['status' => 1])
                ->orderByDesc('weight')
                ->orderBy('id', 'desc')
                ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        }

        if (!$res) {
            $res = $this->where(['status' => 1])
                ->orderByDesc('weight')
                ->orderBy('id', 'desc')
                ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        }

        return $res;
    }

    /**
     * 获取某个分类，以某种方式排序
     * @param int $sort
     * @param string $order_by
     */
    public function getAllTypeGoods($sort = 1, $order_by = 'profit_value', $fix = 'desc')
    {
        $res = $this->where([
            'status' => 1,
            'sort' => $sort,
        ])
            ->orderBy($order_by, $fix)
            ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        return $res;
    }

    /**
     * 获取某个分类，以某种方式排序
     * @param int $sort
     * @param string $order_by
     */
    public function getAllTypeGoodsWeb($sort = 1, $order_by = 'profit_value', $fix = 'desc')
    {
        $res = $this->where([
            'status' => 1,
            'sort' => $sort,
            'weight' => 98,
        ])
            ->orderBy($order_by, $fix)
            ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        return $res;
    }

    /**
     * 搜索过滤器 20191108
     * @param int $sort
     * @param string $order_by
     */
    public function getAllTypeGoodsNew($sort = 1, $order_by = 'profit_value', $fix = 'desc', $no_in = [])
    {
        $res = $this->whereNotIn('id', $no_in)->where([
            'status' => 1,
            'sort' => $sort,
        ])
            ->orderBy($order_by, $fix)
            ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        return $res;
    }

    /**
     * 搜索过滤器 20191108
     * @param int $sort
     * @param string $order_by
     */
    public function getAllTypeGoodsNewWeb($sort = 1, $order_by = 'profit_value', $fix = 'desc', $no_in = [])
    {
        $res = $this->whereNotIn('id', $no_in)->where([
            'status' => 1,
            'sort' => $sort,
            'weight' => 98,
        ])
            ->orderBy($order_by, $fix)
            ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        return $res;
    }

    /**
     * 获取某个分类，搜索关键词
     * @param int $sort
     * @param string $order_by
     */
    public function getAllSearchGoods($title = 1, $no_in = [], $order_by = 'profit_value', $fix = 'desc')
    {
        $res = $this->whereNotIn('id', $no_in)->where('title', 'like', '%' . $title . '%')
            ->where([
                'status' => 1,
            ])
            ->orderBy($order_by, $fix)
            ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        return $res;
    }

    /**
     * 获取某个分类，搜索关键词
     * @param int $sort
     * @param string $order_by
     */
    public function getAllSearchGoodsWeb($title = 1, $no_in = [], $order_by = 'profit_value', $fix = 'desc')
    {
        $res = $this->whereNotIn('id', $no_in)->where('title', 'like', '%' . $title . '%')
            ->where([
                'status' => 1,
                'weight' => 98,
            ])
            ->orderBy($order_by, $fix)
            ->paginate(20, ['id', 'cost_price', 'header_img', 'title', 'sale_volume', 'volume', 'vip_price', 'price']);
        return $res;
    }

    /**
     *
     * {"0":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","1":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","2":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","3":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","4":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","5":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","6":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","7":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","8":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png","9":"https://a119112.oss-cn-beijing.aliyuncs.com/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20180821162509.png"}
     * 根据商品开放时间查询，(1)昨日，(2)今日，(3)明日
     * @param $type
     * @return \Illuminate\Support\Collection
     */
    public function getAllGoodsByType($type)
    {

        $oneday_time = Carbon::today()->timestamp;
        $yesterday_time = $oneday_time - 86400;
        $tomorrow_time = $oneday_time + 86400;
        $acquired_time = $oneday_time + (86400 * 2);

        if ($type == 1) {
            $res = $this
                ->where('open_time', '<', $oneday_time)
                ->where('open_time', '<>', 0)
                ->where(['status' => 1])
                ->orderBy('open_time', 'asc')
                ->get(['id', 'title', 'cost_price', 'price', 'sidle_img', 'vip_price', 'sale_volume', 'volume', 'open_time', 'header_img']);
        }

        if ($type == 2) {
            $res = $this
                ->where('open_time', '>', $oneday_time)
                ->where('open_time', '<', $tomorrow_time)
                ->where(['status' => 1])
                ->orderBy('open_time', 'asc')
                ->get(['id', 'title', 'cost_price', 'price', 'sidle_img', 'vip_price', 'sale_volume', 'volume', 'open_time', 'header_img']);
        }

        if ($type == 3) {
            $res = $this
                ->where('open_time', '>', $tomorrow_time)
                ->where('open_time', '<', $acquired_time)
                ->where(['status' => 1])
                ->orderBy('open_time', 'asc')
                ->get(['id', 'title', 'cost_price', 'price', 'sidle_img', 'vip_price', 'sale_volume', 'volume', 'open_time', 'header_img']);
        }

        if ($type == 4) {
            $res = $this
                ->where('id', '>', 87)
                ->where('id', '<', 93)
                ->where(['status' => 1])
                ->get(['id', 'title', 'cost_price', 'price', 'sidle_img', 'vip_price', 'sale_volume', 'volume', 'open_time', 'header_img']);
        }

        return $res;

    }

    /**
     * 获取单个商品的信息
     * @param $id
     * @return Model|null|static
     */
    public function getOneGood($id)
    {
        $res = $this->where(['id' => $id, 'status' => 1])
            ->first(['id', 'header_img', 'cost_price', 'profit_value', 'detail_share_img', 'parameter', 'custom', 'video_url', 'sidle_img', 'express', 'detail_desc', 'open_time', 'sale_volume', 'zone', 'price', 'vip_price', 'title', 'detail_img', 'shop_id', 'volume', 'can_active']);
        return $res;
    }

    /**
     * 获取单个商品的信息
     * @param $id
     * @return Model|null|static
     */
    public function getGoodData($id)
    {
        $res = $this->where(['id' => $id])
            ->first(['id', 'header_img', 'cost_price', 'profit_value', 'detail_share_img', 'parameter', 'custom', 'video_url', 'sidle_img', 'express', 'detail_desc', 'open_time', 'sale_volume', 'zone', 'price', 'vip_price', 'title', 'detail_img', 'shop_id', 'volume', 'can_active']);
        return $res;
    }

    /**
     * 获取单个商品的信息
     */
    public function getSupplierGoods($good_id, $app_id)
    {
        $res = $this->where(['id' => $good_id, 'shop_id' => $app_id])->first();
        return $res;
    }

    /**
     * 利用id找到商品
     * @param $id (此方法拿的是缩略版)
     * @param int $status
     * @return Model|null|static
     */
    public function getOneById($id, $status = 1)
    {
        if ($status) {
            $res = $this->where(['id' => $id, 'status' => $status])
                ->first(['id', 'header_img', 'cost_price', 'price', 'is_push', 'vip_price', 'detail_share_img', 'express', 'title', 'profit_value', 'volume', 'sale_volume', 'detail_desc']);
        } else {
            $res = $this->where(['id' => $id])
                ->first(['id', 'header_img', 'cost_price', 'price', 'is_push', 'vip_price', 'detail_share_img', 'express', 'title', 'profit_value', 'volume', 'sale_volume', 'detail_desc']);
        }
        return $res;
    }

    /**
     * 增加真实销量
     * @param $id
     * @return bool
     */
    public function increaseSaleNumber($id, $number)
    {
        return $this->where(['id' => $id])->update(['real_sale_volume' => DB::raw("real_sale_volume + " . $number)]);
    }

    /**
     * 增加点击量
     * @param $id
     * @return bool
     */
    public function increaseClickNumber($id)
    {
        return $this->where(['id' => $id])->update(['click_number' => DB::raw("click_number + 1")]);
    }

    /**
     * 平衡销量与库存
     * @param $id
     * @param $number
     * @return int
     */
    public function balanceNumber($id, $number)
    {
        $res = $this->where(['id' => $id])->update(['sale_volume' => DB::raw("sale_volume + " . $number)]);
        $res = $this->where(['id' => $id])->update(['volume' => DB::raw("volume - " . $number)]);
        return 1;
    }

    /*
     * 根据id得到数据
     */
    public function getOneGoodById($id)
    {
        $res = $this->where(['id' => $id])
            ->first();
        return $res;
    }
}
