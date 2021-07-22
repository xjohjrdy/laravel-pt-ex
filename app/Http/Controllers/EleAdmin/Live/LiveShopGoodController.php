<?php

namespace App\Http\Controllers\EleAdmin\Live;

use App\Http\Controllers\EleAdmin\BaseController;
use App\Tools\ObjectDataHandle;
use Illuminate\Http\Request;
use App\Models\EleAdmin\LiveShopGoods as LiveShopGoodModel;

class LiveShopGoodController extends BaseController
{
    public function lists(Request $request)
    {
        try {
            $params = $request->all();
            $params['deleted_at'] = 'is null';

            $columns = ['id', 'good_id', 'read_is', 'updated_at', 'created_at'];

            $query = LiveShopGoodModel::with(['goods' => function ($query) {
                $query->select(['id', 'title', 'sidle_img', 'price']);
            }])->select($columns)
                ->ofConditions($params)
                ->orderBy('updated_at', 'desc');

            list($goods, $pagination) = $this->paginate($query);

            if ($goods) {
                foreach ($goods as &$good) {
                    if (empty($good->goods)) {
                        $good->goods->id = '';
                        $good->goods->title = '';
                        $good->goods->sidle_img = '';
                        $good->goods->price = '';
                    }
                }
                $records = $goods->toArray();
            } else {
                $records = [];
            }

            $data['records'] = $records;
            $data['pagination'] = $pagination;

            return $this->getResponse($data);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }
}