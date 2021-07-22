<?php

namespace App\Http\Controllers\Web;

use App\Entitys\App\OneGoH5CashGit;
use App\Entitys\App\OneGoMaidOld;
use App\Entitys\App\OneGoTaobaoEnterOrder;
use App\Exceptions\ApiException;
use App\Services\Alimama\BigWashUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ZeroBuyController extends Controller
{
    public function showInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
                'page' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];
            $page = $arrRequest['page'];


            $model_one_go = new OneGoMaidOld();
            $all_num = $model_one_go->where(['app_id' => $app_id])->count();
            $real_num = $model_one_go->where(['app_id' => $app_id, 'real' => 1])->count();
            $all_money = $model_one_go->where(['app_id' => $app_id])->sum('maid_money');
            $wait_money = $model_one_go->where(['app_id' => $app_id, 'real' => 0])->sum('maid_money');


            $list_order = $model_one_go
                ->where(['app_id' => $app_id])
                ->orderByDesc('id')
                ->forPage($page, 20)
                ->get(['trade_id', 'maid_money', 'created_at']);

            foreach ($list_order as &$item) {
                $item->title = OneGoTaobaoEnterOrder::where('trade_id', $item->trade_id)->value('item_title');
            }


            return $this->getResponse([
                'all_num' => $all_num,
                'real_num' => $real_num,
                'all_money' => $all_money,
                'wait_money' => $wait_money,
                'list_order' => $list_order,
            ]);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 补贴0元购
     */
    public function subsidyZeroShop(Request $request, OneGoH5CashGit $oneGoH5CashGit, BigWashUser $bigWashUser)
    {
        try {
            $post_data = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $data = [];
            $time = date('Y-m-d H:i:s');
            $all_res = $oneGoH5CashGit->get();

            foreach ($all_res as $v) {
                $welfare_type = [
                    1 => 'new',
                    2 => 'vip',
                    3 => 'all',
                ];
                if ($v->send_start_time <= $time && $time < $v->send_end_time) {
                    $goods_details_present = $bigWashUser->shareGoodsDetails(['goodsId' => $v->item_id]);
                    $arr_goods_details_present = $goods_details_present;

                    $arr_goods_details_present['price_new'] = $v->price;
                    $arr_goods_details_present['cash_new'] = $v->cash;
                    $arr_goods_details_present['subtract_price'] = $arr_goods_details_present['price_new'] - $arr_goods_details_present['cash_new'];

                    $data['present'][$welfare_type[$v->special_id]][] = $arr_goods_details_present;
                }
                if ($v->send_end_time <= $time) {
                    $goods_details_formerly = $bigWashUser->shareGoodsDetails(['goodsId' => $v->item_id]);
                    $arr_goods_details_formerly = $goods_details_formerly;

                    $arr_goods_details_formerly['price_new'] = $v->price;
                    $arr_goods_details_formerly['cash_new'] = $v->cash;
                    $arr_goods_details_formerly['subtract_price'] = $arr_goods_details_formerly['price_new'] - $arr_goods_details_formerly['cash_new'];

                    $data['formerly'][$welfare_type[$v->special_id]][] = $arr_goods_details_formerly;
                }
            }
            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
