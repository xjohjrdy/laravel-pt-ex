<?php

namespace App\Http\Controllers\Alimama;

use App\Services\Ali\AliOrderService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AlimamaSyncController extends Controller
{
    /*
     *
     */
    function gatherOrder(Request $request)
    {
        $post_data = $request->all();

        if (@$post_data['code'] != 200) {
            return ['code' => 3001, 'message' => 'code is error'];
        }

        if (request()->header('tokk') != md5('_nihao_')) {
            return ['code' => 5001, 'message' => 'request is error to kk '];
        }

        $fail_data = [];
        $success = 0;
        $fail = 0;
        $service = new AliOrderService();

        foreach (@$post_data['data'] as $item) {
            $data = [
                'order_number' => $item['order_number'],
                'status' => $item['status'],
                'commission' => $item['commission'],
                'taobao_time' => $item['taobao_time'],
                'create_time' => time(),
                'admin_id' => "0"//操作人 0表示后台
            ];
            /*
             * 添加订单防止特殊情况下重放
             */
            if (Cache::has('a_t_o_' . $item['order_number'])) {
                $res = 0;
            } else {
                Cache::put('a_t_o_' . $item['order_number'], 1, 0.2);
                $res = $service->handleTaoBaoDataV1($data);
            }
            $success += $res ? 1 : 0;
            $fail += $res ? 0 : 1;
            if (!$res) {
                $fail_data[] = [
                    'order_number' => $item['order_number'],
                    'status' => @$item['status'],
                    'commission' => $item['commission'],
                    'taobao_time' => $item['taobao_time']
                ];
            }
        }
        $finish = empty($excel_data) ? 1 : 0;

        return [
            'finish' => $finish,
            'success' => $success,
            'fail' => $fail,
            'fail_data' => $fail_data
        ];
    }
}
