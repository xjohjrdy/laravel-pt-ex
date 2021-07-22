<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\TaobaoCollection;
use App\Exceptions\ApiException;
use ETaobao\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    /**
     * 获取当前用户收藏
     * @param Request $request
     * @param TaobaoCollection $taobaoCollection
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, TaobaoCollection $taobaoCollection)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $collection = $taobaoCollection->getAll($arrRequest['app_id']);

            return $this->getResponse($collection);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, TaobaoCollection $taobaoCollection)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'item_id' => 'required',
                'commission_rate' => 'required',
                'url' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $config = config('taobao.config_taobao_one');

            $app = Factory::Tbk($config);
            $param = [
                'num_iids' => $arrRequest['item_id'],
            ];
            $res = $app->item->getInfo($param);

            $taobaoCollection->addResult([
                'app_id' => $arrRequest['app_id'],
                'image_url' => $res->results->n_tbk_item[0]->pict_url,
                'header_text' => $res->results->n_tbk_item[0]->title,
                'taobao_tianmao' => 0,
                'shop_title' => $res->results->n_tbk_item[0]->nick,
                'sell_number' => $res->results->n_tbk_item[0]->volume,
                'price_first' => $res->results->n_tbk_item[0]->reserve_price,
                'roll' => $res->results->n_tbk_item[0]->reserve_price - $res->results->n_tbk_item[0]->zk_final_price,
                'roll_price' => $res->results->n_tbk_item[0]->zk_final_price,
                'commission' => $arrRequest['commission_rate'] / 1000,
                'share_url' => $arrRequest['url'],
            ]);

            return $this->getResponse('收藏成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
