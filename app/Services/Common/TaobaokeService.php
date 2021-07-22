<?php

namespace App\Services\Common;

class TaobaokeService
{
    private $topClient;

    public function __construct()
    {
        $c = new \TopClient();
        $c->appkey = config('taobaoke.woxiaoli.appkey');
        $c->secretKey = config('taobaoke.woxiaoli.secretKey');
        $c->format = 'json';
        $this->topClient = $c;
    }

    /*
     * 通过商品id获取对应商品的主图
     * 如 588006506534,588005242813
     * 返回结果 [id:img_url]
     */
    public function getGoodsPictUrlByIds($num_iids)
    {

        $req = new \TbkItemInfoGetRequest();
        $req->setNumIids($num_iids);
        $resp = $this->topClient->execute($req);
        if (empty($resp['results']['n_tbk_item'])) {
            return false;
        }

        $list_goods = $resp['results']['n_tbk_item'];

        $res = [];
        foreach ($list_goods as $item) {
            $res[$item['num_iid']] = $item['pict_url'];
        }

        return $res;
    }


}