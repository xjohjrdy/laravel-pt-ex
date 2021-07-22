<?php

namespace App\Console\Commands;

use App\Entitys\App\FuluGoodsInfo as FuluGoodsInfoModel;
use App\Services\FuLu\FuLuServices;
use Illuminate\Console\Command;

class FuluGoodsInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:FuluGoodsInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '福禄商品抓取';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //开始
        $this->info("start");

        //取所有福禄商品
        $fuLuServices = new FuLuServices();
        $json_goods = $fuLuServices->getGoodsList();
        $arr_goods = json_decode($json_goods, true);
        if ($arr_goods['code'] != 0) {
            $this->info('商品列表拉取失败:' . $arr_goods['message']);
            die;
        }
        $result = json_decode($arr_goods['result'], true);

        //存入订单 至数据库 lc_fulu_goods_info
        $this->info('获取有效商品数量：' . count($result));
        $this->syncGoods($result);

        //删除数据库 lc_fulu_goods_info 下架商品
        $collect = collect($result);
        $arr_product_id = $collect->pluck('product_id')->toArray();
        $this->delGoods($arr_product_id);

        $this->info("end");
    }

    /*
     * 往数据库里面存数据
     */
    public function syncGoods($goods)
    {
        $fuluGoodsInfo = new FuluGoodsInfoModel();
        foreach ($goods as $item) {
            //有则更新 无责新增
            $good_id = $fuluGoodsInfo->where(['product_id' => $item['product_id']])->value('id');
            if (!empty($good_id)) {
                $fuluGoodsInfo->where('id', $good_id)->update($item);
                $this->info('更新商品id：' . $item['product_id']);
            } else {
                $fuluGoodsInfo->create($item);
                $this->info('新增商品id：' . $item['product_id']);
            }
        }
        return true;
    }

    /*
     * 删除数据库下架商品
     */
    public function delGoods($arr_product_id)
    {
        //取数据库所有 product_id
        $fuluGoodsInfo = new FuluGoodsInfoModel();
        $all_product_id = $fuluGoodsInfo->pluck('product_id')->toArray();

        //返回数组不重复的值 表示下架商品
        $del_goods = array_diff($arr_product_id, $all_product_id);

        $fuluGoodsInfo->whereIn('product_id', $del_goods)->forceDelete();

        foreach ($del_goods as $item) {
            $this->info('删除下架商品id：' . $item);
        }

        return true;
    }
}
