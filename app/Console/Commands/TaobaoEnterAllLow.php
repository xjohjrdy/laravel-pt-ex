<?php

namespace App\Console\Commands;

use App\Entitys\App\TaobaoEnterOrder;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TaobaoEnterAllLow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:taobaoEnterAllLow';

    protected $testObj;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理大量淘宝失效订单';

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
        $status_api = [
            3 => 2,
            12 => 1,
            13 => 3,
            14 => 1
        ];
        $data_api = [];

        $this->info("start  -15");

        $appkey = 25821858;
        $secret = '0676acbd3d38d1ceac4b476a25556eef';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_id,create_time,tk_status,pub_share_pre_fee");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-15 day")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("1");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $resp['results']['n_tbk_order'] = [];
        }
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));

        foreach ($resp['results']['n_tbk_order'] as $item) {
            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['create_time']),
            ];
        }

        $appkey = 25842871;
        $secret = 'db9604b2acf693b95c7da990ad07b4f7';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_id,create_time,tk_status,pub_share_pre_fee");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-15 day")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("1");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $resp['results']['n_tbk_order'] = [];
        }
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));
        foreach ($resp['results']['n_tbk_order'] as $item) {
            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['create_time']),
            ];
        }
        $post_api_data = [
            'code' => 200,
            'data' => $data_api
        ];
        $json_post_data = ($post_api_data);
        $url = "http://api.36qq.com/api/ali_sync_gather_order";
        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'tokk' => '64d8ea7cf1dc5710d61e373d34f69e23',
            ],
            'json' => $json_post_data
        ];
        $client = new Client();
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $this->info($json_res);
        $this->info("end");

        /*****************************************/

        $data_api = [];

        $this->info("start  -10");

        $appkey = 25821858;
        $secret = '0676acbd3d38d1ceac4b476a25556eef';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_id,create_time,tk_status,pub_share_pre_fee");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-10 day")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("1");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $resp['results']['n_tbk_order'] = [];
        }
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));

        foreach ($resp['results']['n_tbk_order'] as $item) {
            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['create_time']),
            ];
        }

        $appkey = 25842871;
        $secret = 'db9604b2acf693b95c7da990ad07b4f7';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_id,create_time,tk_status,pub_share_pre_fee");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-10 day")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("1");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $resp['results']['n_tbk_order'] = [];
        }
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));
        foreach ($resp['results']['n_tbk_order'] as $item) {
            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['create_time']),
            ];
        }
        $post_api_data = [
            'code' => 200,
            'data' => $data_api
        ];
        $json_post_data = ($post_api_data);
        $url = "http://api.36qq.com/api/ali_sync_gather_order";
        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'tokk' => '64d8ea7cf1dc5710d61e373d34f69e23',
            ],
            'json' => $json_post_data
        ];
        $client = new Client();
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $this->info($json_res);
        $this->info("end");

        /*****************************************/

        $data_api = [];

        $this->info("start  -5");

        $appkey = 25821858;
        $secret = '0676acbd3d38d1ceac4b476a25556eef';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_id,create_time,tk_status,pub_share_pre_fee");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-5 day")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("1");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $resp['results']['n_tbk_order'] = [];
        }
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));

        foreach ($resp['results']['n_tbk_order'] as $item) {
            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['create_time']),
            ];
        }

        $appkey = 25842871;
        $secret = 'db9604b2acf693b95c7da990ad07b4f7';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_id,create_time,tk_status,pub_share_pre_fee");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-5 day")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("1");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $resp['results']['n_tbk_order'] = [];
        }
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));
        foreach ($resp['results']['n_tbk_order'] as $item) {
            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['create_time']),
            ];
        }
        $post_api_data = [
            'code' => 200,
            'data' => $data_api
        ];
        $json_post_data = ($post_api_data);
        $url = "http://api.36qq.com/api/ali_sync_gather_order";
        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'tokk' => '64d8ea7cf1dc5710d61e373d34f69e23',
            ],
            'json' => $json_post_data
        ];
        $client = new Client();
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $this->info($json_res);
        $this->info("end");

        /*****************************************/

        $data_api = [];

        $this->info("start  -1");

        $appkey = 25821858;
        $secret = '0676acbd3d38d1ceac4b476a25556eef';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_id,create_time,tk_status,pub_share_pre_fee");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-1 day")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("1");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $resp['results']['n_tbk_order'] = [];
        }
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));

        foreach ($resp['results']['n_tbk_order'] as $item) {
            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['create_time']),
            ];
        }

        $appkey = 25842871;
        $secret = 'db9604b2acf693b95c7da990ad07b4f7';
        $c = new \TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $c->format = 'json';
        $req = new \TbkOrderGetRequest();
        $req->setFields("trade_id,create_time,tk_status,pub_share_pre_fee");
        $req->setStartTime(date("Y-m-d H:i:s", strtotime("-1 day")));
        $req->setSpan("360");
        $req->setPageNo("1");
        $req->setPageSize("100");
        $req->setTkStatus("1");
        $req->setOrderQueryType("create_time");
        $req->setOrderScene("1");
        $req->setOrderCountType("1");
        $resp = $c->execute($req);
        if (empty($resp['results'])) {
            $resp['results']['n_tbk_order'] = [];
        }
        $this->info('本次获取数量：' . count($resp['results']['n_tbk_order']));
        foreach ($resp['results']['n_tbk_order'] as $item) {
            $data_api[] = [
                'order_number' => $item['trade_id'],
                'status' => @$status_api[$item['tk_status']],
                'commission' => $item['pub_share_pre_fee'],
                'taobao_time' => strtotime($item['create_time']),
            ];
        }
        $post_api_data = [
            'code' => 200,
            'data' => $data_api
        ];
        $json_post_data = ($post_api_data);
        $url = "http://api.36qq.com/api/ali_sync_gather_order";
        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'tokk' => '64d8ea7cf1dc5710d61e373d34f69e23',
            ],
            'json' => $json_post_data
        ];
        $client = new Client();
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $this->info($json_res);
        $this->info("end");

        /*****************************************/

    }
}
