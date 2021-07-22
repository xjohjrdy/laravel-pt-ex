<?php

namespace App\Http\Controllers\Common;

use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopIndex;
use App\Entitys\Article\Article;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class DisplayController extends Controller
{
    /**
     * 获取标准时间
     * @return int
     */
    public function getCommonTime()
    {
        return $this->getResponse(time());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSignImage()
    {
        $array_no_image_url = [
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E6%94%AF%E4%BB%98%E5%AE%9D%E6%89%AB%E7%A0%81/1/%E5%90%8E.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E6%94%AF%E4%BB%98%E5%AE%9D%E6%89%AB%E7%A0%81/2/%E5%90%8E.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E6%94%AF%E4%BB%98%E5%AE%9D%E6%89%AB%E7%A0%81/3/%E5%90%8E.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E6%94%AF%E4%BB%98%E5%AE%9D%E6%89%AB%E7%A0%81/4/%E5%90%8E.jpg',
        ];
        $array_url = [
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E6%94%AF%E4%BB%98%E5%AE%9D%E6%89%AB%E7%A0%81/1/Qian.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E6%94%AF%E4%BB%98%E5%AE%9D%E6%89%AB%E7%A0%81/2/Qian.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E6%94%AF%E4%BB%98%E5%AE%9D%E6%89%AB%E7%A0%81/3/Qian.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E6%94%AF%E4%BB%98%E5%AE%9D%E6%89%AB%E7%A0%81/4/Qian.jpg',
        ];
        $image_url = $array_url[array_rand($array_url, 1)];
        $no_image_url = $array_no_image_url[array_rand($array_no_image_url, 1)];

        return $this->getResponse(['is_skip' => 0, 'url' => $image_url, 'no_sign_url' => $no_image_url]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIndexImage()
    {
        $image_url = 'https://a119112.oss-cn-beijing.aliyuncs.com/%E5%95%86%E5%9F%8E%E9%A6%96%E9%A1%B5/%E5%BC%B9%E7%AA%97%403x.png';

        return $this->getResponse(['is_close' => 1, 'url' => $image_url]);
    }

    /**
     * 广告联盟先前页面
     * @param $group_id
     * @param ShopGoods $shopGoods
     * @param ShopIndex $shopIndex
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShowBuyPage($group_id, ShopGoods $shopGoods, ShopIndex $shopIndex)
    {
        $no_way = 0;
        if ($group_id >= 23) {
            $no_way = 1;
        }

        if (Cache::has('show_buy_page_for_example_oo1') && Cache::has('show_buy_page_for_example_oo2')) {
            $vip_array = Cache::get('show_buy_page_for_example_oo1');
            $arr = Cache::get('show_buy_page_for_example_oo2');
        } else {
            $vip_array = $shopIndex->getVipArray();
            $arr = [];
            foreach ($vip_array as $k => $v) {
                $good = $shopGoods->getOneGood($k);
                if (empty($good)) {
                    unset($vip_array[$k]);
                    continue;
                }
                $vip_array[$k] = $good->title;
                $test_good['title'] = $good->title;
                $test_good['id'] = $good->id;
                $header_img = json_decode($good->header_img, true);
                if (!empty($header_img)) {
                    $test_good['header_img'] = $header_img[0];
                } else {
                    $test_good['header_img'] = null;
                }
                $test_good['price'] = $good->price;
                $arr[] = $test_good;
            }

            Cache::put('show_buy_page_for_example_oo1', $vip_array, 10);
            Cache::put('show_buy_page_for_example_oo2', $arr, 10);
        }

        return $this->getResponse([
            'no_way' => $no_way,
            'choice' => [
                'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/te/%E8%A7%86%E9%A2%91/img-1@2x.png',
                'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/te/%E8%A7%86%E9%A2%91/img-2@2x.png',
                'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/te/%E8%A7%86%E9%A2%91/img-3@2x.png',
            ],
            'forever' => $vip_array,
            'forever_new' => $arr,
        ]);
    }

    /**
     * 支付宝红包生成
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAliCode()
    {
        $array_code = [
            0 => '511085749',
            1 => '546862433',
            2 => '547035452',
            3 => '547073345',
            4 => '547326947',
            5 => '542126056',
        ];
        $array_url = [
            0 => 'https://qr.alipay.com/c1x0562049wr1xemxyd5rca',
            1 => 'https://qr.alipay.com/c1x0562049wr1xemxyd5rca',
            2 => 'https://qr.alipay.com/c1x0570274jmgxj3ngan427',
            3 => 'https://qr.alipay.com/c1x04950trvwiuxu5qdzs56',
            4 => 'https://qr.alipay.com/c1x0139671xv3jaydxp9f4d',
            5 => 'https://qr.alipay.com/c1x07543eciv0jskzx2tya2',
            6 => 'https://qr.alipay.com/c1x03180a9jyzwdrleicud4',
            7 => 'https://qr.alipay.com/c1x07543eciv0jskzx2tya2',
            8 => 'https://qr.alipay.com/c1x08758tnwbyaqpxeksw4e',
            9 => 'https://qr.alipay.com/c1x05650lg2zg9w3cb66m67',
        ];

        $test_url = [
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E7%AC%AC%E4%B8%80%E5%A4%A9.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E7%AC%AC%E4%B8%89%E5%A4%A9.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E7%AC%AC%E4%BA%8C%E5%A4%A9.jpg',
            'http://a119112.oss-cn-beijing.aliyuncs.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BE%E7%89%87/%E7%AC%AC%E5%9B%9B%E5%A4%A9.jpg',
        ];
        $image_url = $array_url[array_rand($array_url, 1)];
        $ali_code = $array_code[array_rand($array_code, 1)];
        $test_code = $test_url[array_rand($test_url, 1)];

        $show_url = 'http://a119112.oss-cn-beijing.aliyuncs.com/kuang/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20181114165758.jpg';
        return $this->getResponse([
            'image_url' => $image_url,
            'ali_code' => $ali_code,
            'ali_image' => $test_code,
            'teacher' => $show_url
        ]);
    }

    /**
     * @param ShopGoods $shopGoods
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTodayShop(Request $request, ShopGoods $shopGoods)
    {
        $res = $shopGoods->getAllGoodsByType(2);
        if (empty($res->toArray())) {
            $res = $shopGoods->getAllGoodsByType(4);
        }
        $count = 0;
        foreach ($res as $k => $model) {
            $arr = json_decode($model->header_img, true);
            if (array_key_exists(0, $arr)) {
                $model->header_img = $arr[0];
            } else {
                $model->header_img = '';
            }
            $model->remain_time = $model->open_time - time();
            if ($model->remain_time <= 0) {
                $count++;
            } else {
                $count++;
            }
            if (($model->volume + $model->sale_volume) == 0) {
                $model->percent = 0;
            } else {
                $model->percent = $model->sale_volume / ($model->volume + $model->sale_volume);
            }
        };
        if (!$count) {
            $res = $shopGoods->getAllGoodsByType(2);
        }
        $request_device = $request->header('Accept-Device');
        $request_appversion = $request->header('Accept-Appversion');
        if ($request_device == 'android' && $request_appversion >= 199) {
            $res = array_slice($res->toArray(), 0, 3);
        }

        return $this->getResponse($res);
    }

    /**
     * 展示新闻
     */
    public function displayNews($id, Article $article)
    {
        $use_article = $article->getUseArticle($id);
        echo $use_article->content;
    }

    /**
     * 代理商客服的微信和字
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRechargeInfoAnswer()
    {
        return $this->getResponse('');
    }

    /**
     * 获取处理的京东链接
     */
    public function getNewJdUrlThisWu(Client $client)
    {
        $url = "apimd.haojingke.com/api/index/getunionurlzdy?positionId=584021***1234";
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);
        $res_url = @$json_res['data'];
        return "<script>
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $res_url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
    }

    public function getSuNingUrlByWu(Client $client)
    {
        $url = "http://sn.dydui.cn/api/thirdParty/getTwitterUrl?channelId=01032&userId=36";
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);
        $res_url = @$json_res['result'];
        return "<script>
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $res_url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
    }

    /**
     * 获取处理的京东链接
     */
    public function getNewJdUrlThisWan(Client $client)
    {
        $url = "apimd.haojingke.com/api/index/getunionurlzdy?positionId=584021***652901";
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);
        $res_url = @$json_res['data'];
        return "<script>
        function htmlDecode(text){
            var temp = document.createElement(\"div\");
            temp.innerHTML = text;
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
var url = '" . $res_url . "';
var uri = htmlDecode(url);
console.log(uri);
window.location.replace(uri);
</script>
";
    }

    /**
     * 获取当前已知的所有订单
     * @param Client $client
     * @return string
     */
    public function getNewJdAllOrdersThisWu(Client $client)
    {
        $str_order = "";
        $url = "apimd.haojingke.com/api/index/getorderlist1903?page=1&pagesize=100&uid=584021&type=1&begintime=1551369600&endtime=1554048000";
        $t = 0;
        $x = 0;
        $obj_res = $client->request('POST', $url, ['verify' => false]);
        $json_res = json_decode((string)$obj_res->getBody(), true);
        echo "<h1>当前一共：" . @$json_res['total'] . "单<br></h1>";
        for ($i = 1; $i < 1000; $i++) {
            $url = "apimd.haojingke.com/api/index/getorderlist1903?page=" . $i . "&pagesize=100&uid=584021&type=1&begintime=1551369600&endtime=1554048000";
            $obj_res = $client->request('POST', $url, ['verify' => false]);
            $json_res = json_decode((string)$obj_res->getBody(), true);
            $res_url = @$json_res['data'];
            if (empty($res_url) && $i == 1) {
                return "暂时没有订单！";
            }
            if (empty($res_url) && $i <> 1) {
                break;
            }
            foreach ($res_url as $item) {
                if ($item['order_status'] == 10) {
                    $t++;
                }
                if ($item['positionId'] == 652901) {
                    $x++;
                }
                $str_order .=
                    "订单id，" . $item['orderId'] . "，下单时间，" . date('Y-m-d H:i:s', $item['orderTime']) . "，" .
                    "是否plus，" . $item['plus'] . "，推广位id，" . $item['positionId'] . "，" .
                    "商品id，" . $item['skuId'] . "，订单商品名字，" . $item['skuName'] . "，" .
                    "京东联盟标签，" . $item['unionTag'] . "，商品数量，" . $item['skuNum'] . "，" .
                    "退货数量，" . $item['skuReturnNum'] . "，订单商品名字，" . $item['skuName'] . "，" .
                    "完整推广位id，" . $item['subUnionId'] . "，计佣金额，" . $item['cosPrice'] . "，" .
                    "官方状态，" . $item['valistatus'] . "，官方状态码，" . $item['validCode'] . "，" .
                    "是否首购订单，" . $item['order_status'] . "，订单状态描述，" . $item['order_remark'] . "<br>";
            }
        }
        echo "<h2>有效订单：" . $t . "<br></h2>";
        echo "<h2>652901id的订单：" . $x . "<br></h2>";
        echo $str_order;
        return "<br>";
    }

    /**
     * 获取当前已知的所有订单
     * @param Client $client
     * @return string
     */
    public function getNewJdAllOrdersThisWan(Client $client)
    {
        $str_order = "";
        $t = 0;
        $x = 0;
        for ($i = 1; $i < 1000; $i++) {
            $url = "apimd.haojingke.com/api/index/getorderlist1903?page=" . $i . "&pagesize=100&uid=584021&type=1&begintime=1551369600&endtime=1554048000";
            $obj_res = $client->request('POST', $url, ['verify' => false]);
            $json_res = json_decode((string)$obj_res->getBody(), true);
            $res_url = @$json_res['data'];
            if (empty($res_url) && $i == 1) {
                return "暂时没有订单！";
            }
            if (empty($res_url) && $i <> 1) {
                break;
            }
            foreach ($res_url as $item) {
                if ($item['order_status'] == 10 && $item['positionId'] == 652901) {
                    $t++;
                }
                if ($item['positionId'] == 652901) {
                    $x++;
                    $str_order .=
                        "订单id，" . $item['orderId'] . "，下单时间，" . date('Y-m-d H:i:s', $item['orderTime']) . "，" .
                        "是否plus，" . $item['plus'] . "，推广位id，" . $item['positionId'] . "，" .
                        "商品id，" . $item['skuId'] . "，订单商品名字，" . $item['skuName'] . "，" .
                        "京东联盟标签，" . $item['unionTag'] . "，商品数量，" . $item['skuNum'] . "，" .
                        "退货数量，" . $item['skuReturnNum'] . "，订单商品名字，" . $item['skuName'] . "，" .
                        "完整推广位id，" . $item['subUnionId'] . "，计佣金额，" . $item['cosPrice'] . "，" .
                        "官方状态，" . $item['valistatus'] . "，官方状态码，" . $item['validCode'] . "，" .
                        "是否首购订单，" . $item['order_status'] . "，订单状态描述，" . $item['order_remark'] . "<br>";
                }
            }
        }
        echo "<h2>当前有效订单：" . $t . "<br></h2>";
        echo "<h2>当前652901的订单：" . $x . "<br></h2>";
        echo $str_order;
        return "<br>";
    }
}
