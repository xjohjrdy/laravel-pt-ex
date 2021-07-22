<?php

namespace App\Http\Controllers\Taobaoke;

use App\Exceptions\ApiException;
use ETaobao\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SpecialGoodController extends Controller
{
    protected $config_nine = [
        'appkey' => '25626319',
        'secretKey' => '05668c4eefc404c0cd175fb300b2723d',
        'format' => 'json',
        'sandbox' => false,
    ];

    protected $config_high = [
        'appkey' => '25626319',
        'secretKey' => '05668c4eefc404c0cd175fb300b2723d',
        'format' => 'json',
        'sandbox' => false,
    ];

    /**
     * @throws ApiException
     */
    public function index(Request $request)
    {
        try {
            if (empty($request->nine_page_size)) {
                $nine_page_size = 20;
            } else {
                $nine_page_size = $request->nine_page_size;
            }
            if (empty($request->nine_page_no)) {
                $nine_page_no = 1;
            } else {
                $nine_page_no = $request->nine_page_no;
            }
            if (empty($request->high_page_size)) {
                $high_page_size = 20;
            } else {
                $high_page_size = $request->high_page_size;
            }
            if (empty($request->high_page_no)) {
                $high_page_no = 1;
            } else {
                $high_page_no = $request->high_page_no;
            }
            $res_nine = null;
            $res_high = null;
            if (empty($request->all_info)) {
                $app = Factory::Tbk($this->config_nine);
                $param = [
                    'adzone_id' => '91593200288',
                    'platform' => '2',
                    'page_size' => $nine_page_size,
                    'favorites_id' => '19214453',
                    'page_no' => $nine_page_no,
                    'fields' => 'volume,title,reserve_price,zk_final_price,pict_url,small_images,coupon_info,coupon_click_url,click_url',
                ];
                $res_nine = $app->uatm->getItemFavorites($param);
                $app = Factory::Tbk($this->config_high);
                $param = [
                    'adzone_id' => '91593200288',
                    'platform' => '2',
                    'page_size' => $high_page_size,
                    'favorites_id' => '19214454',
                    'page_no' => $high_page_no,
                    'fields' => 'volume,title,reserve_price,zk_final_price,pict_url,small_images,coupon_info,coupon_click_url,click_url',
                ];
                $res_high = $app->uatm->getItemFavorites($param);
            }

            if ($request->all_info == 1) {
                $app = Factory::Tbk($this->config_nine);
                $param = [
                    'adzone_id' => '91593200288',
                    'platform' => '2',
                    'page_size' => $nine_page_size,
                    'favorites_id' => '19214453',
                    'page_no' => $nine_page_no,
                    'fields' => 'volume,title,reserve_price,zk_final_price,pict_url,small_images,coupon_info,coupon_click_url,click_url',
                ];
                $res_nine = $app->uatm->getItemFavorites($param);
            }

            if ($request->all_info == 2) {
                $app = Factory::Tbk($this->config_high);
                $param = [
                    'adzone_id' => '91593200288',
                    'platform' => '2',
                    'page_size' => $high_page_size,
                    'favorites_id' => '19214454',
                    'page_no' => $high_page_no,
                    'fields' => 'volume,title,reserve_price,zk_final_price,pict_url,small_images,coupon_info,coupon_click_url,click_url',
                ];
                $res_high = $app->uatm->getItemFavorites($param);
            }
            return $this->getResponse([
                'nine' => $res_nine,
                'high' => $res_high
            ]);

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
    public function store(Request $request)
    {
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
