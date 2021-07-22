<?php

namespace App\Http\Controllers\EleAdmin\Live;

use App\Http\Controllers\EleAdmin\BaseController;
use App\Models\EleAdmin\ShopGoods as ShopGoodsModel;
use App\Tools\ObjectDataHandle;
use Illuminate\Http\Request;
use App\Models\EleAdmin\LiveShopGoods as LiveShopGoodModel;

class ShopGoodController extends BaseController
{
    public function getList(Request $request)
    {
        try {
            $params['status'] = ShopGoodsModel::STATUS_UP;
            $params['deleted_at'] = 'is null';

            $columns = ['id', 'title'];

            $goods = ShopGoodsModel::select($columns)
                ->ofConditions($params)
                ->where('volume', '>', 0)
                ->get();
            if ($goods) {
                foreach ($goods as &$good) {
                    if (mb_strlen($good->title) > 15) {
                        $good->title = mb_substr($good->title, 0, 15) . '...';
                    }
                }
                $goods->toArray();
            }

            return $this->getResponse($goods);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }
}