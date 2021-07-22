<?php

namespace App\Http\Controllers\Test;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\BuyBack;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\App\AppUserInfo;
use App\Entitys\Article\Article;
use App\Services\Advertising\CountBuyLogActive;
use App\Services\Crypt\RsaUtils;
use App\Services\Recharge\PurchaseUserGroup;
use App\Services\Shop\Order;
use ETaobao\Factory;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;

class TestController extends Controller
{
    public function testRedisWebsocket()
    {
        var_dump(1);
        Redis::publish('msg_shop_orders', '{"code":200,"data":{"good_id":"1278","desc":"1份粉干","number":"1"}}');
        var_dump(2);
        exit();
    }

    public function getArr(Request $request)
    {
        $err_ali_pay = $request->all();
        Storage::disk('local')->append('callback_document/no_show_oOo0oO0OoO0Oo.txt', var_export($err_ali_pay, true));
        Storage::disk('local')->append('callback_document/no_show_oOo0oO0OoO0Oo.txt', var_export($request->url(), true));

        return $this->getResponse(111);
    }
    public function PostRsa(Request $request, RsaUtils $rsaUtils)
    {
        $public_key = file_get_contents(base_path('resources\keys\request\rsa_public_key_client.pem'));
        $pu_key = openssl_pkey_get_public($public_key);
        $data = '{"app_id":"1614803"}';
        $encrypted = $rsaUtils->rsaPublicEncode($data);
        $time = time();
        $url = "http://";
        $url .= "api.36qq.com/api/address/1416";
        $key = "62h3svBYRsPUaZPXNRU9";
        $key_sub = mb_substr($key, 0, 5);
        $key_sub2 = mb_substr($key, 5, 5);
        $key_sub3 = mb_substr($key, 10, 5);
        $key_sub4 = mb_substr($key, 15, 5);
        $need_sign = $key_sub . $encrypted . $key_sub2 . $time . $key_sub3 . $url . $key_sub4;
        $sign = hash("sha512", $need_sign);

        return $this->getResponse([
            'encrypted' => $encrypted,
            'time' => $time,
            'url' => $url,
            'sign' => $sign
        ]);
    }
    public function DeRsa(Request $request)
    {
        $test = RsaUtils::rsaPublicDecode($request->data);
        dd($test);
    }

    public function TestCount(Request $request, AppUserInfo $appUserInfo)
    {
        $aa = $appUserInfo->getUserByPhone(13194089498);
        dd($aa);
        dd($request->getClientIp());
    }
    public function AddLoss()
    {
        $sql = "
        select `id`,`phone`,`create_time`,`parent_id` from lc_user where create_time > 1528102800 and create_time < 1528108200;
        ";
        $res = DB::connection("app38")->select($sql);

        foreach ($res as $v) {
            $sql2 = "
            SELECT * FROM pre_common_member WHERE username  = " . $v->phone . "
            ";
            $r = DB::select($sql2);
            if ($r) {
                echo $v->phone . "已经存在<br>";
            } else {
                $sql3 = "
                INSERT INTO `pre_common_member`
                (`email`, `username`, `password`,`secret`, `groupid`, `groupexpiry`,  `regdate`,  `timeoffset`, `pt_id`, `pt_pid`, `pt_username`,`check_code`) 
                VALUES 
                ('152826496176203@rapp.com'," . $v->phone . ",'25f9e794323b453885f5181f1b624d0b',' ',10,'10'," . $v->create_time . ",'9999'," . $v->id . "," . $v->parent_id . "," . $v->phone . ",' ')
                ";

                $last_insert_id = DB::insert($sql3);

                echo $v->phone . "新增成功<br>";
            }
        }


    }

    public function getAllFive(BuyBack $buyBack, AdUserInfo $adUserInfo, AppUserInfo $appUserInfo)
    {
        $buy_back = DB::connection('a1191125678')->select('
      select `uid`, `gra_all` from `pre_common_buy_back` where `gra_all` >= 500 ORDER BY `gra_all` desc
        ');
        foreach ($buy_back as $k => $v) {
            $pt_id = $adUserInfo->where(['uid' => $v->uid])->first(['pt_id']);
            if ($pt_id) {
                $pt_id = $pt_id->toArray();
            } else {
                continue;
            }
            $app_user_info = $appUserInfo->where(['id' => $pt_id['pt_id']])->first(['real_name', 'phone']);
            if ($app_user_info) {
                $app_user_info = $app_user_info->toArray();
            } else {
                continue;
            }
            echo '用户uid：' . $v->uid . '&nbsp;&nbsp;&nbsp;';
            echo '用户总数：' . $v->gra_all . '&nbsp;&nbsp;&nbsp;';
            echo '用户真实姓名：' . $app_user_info['real_name'] . '&nbsp;&nbsp;&nbsp;';
            echo '用户电话：' . $app_user_info['phone'] . '<br>';
        }
    }

    public function getAllErrorUser(RechargeOrder $rechargeOrder, RechargeCreditLog $rechargeCreditLog, AdUserInfo $adUserInfo)
    {
        $res = $rechargeOrder->get(['orderid', 'uid', 'price'])->toArray();

        foreach ($res as $k => $v) {
            $sum = 0;
            $res_fenyong = $rechargeCreditLog->where(['orderid' => $v['orderid']])->get(['uid', 'money'])->toArray();
            foreach ($res_fenyong as $r => $i) {
                $is_three = $adUserInfo->checkUserThreeFloor($v['uid'], $i['uid']);
                $i_detail = $adUserInfo->getUserById($i['uid']);
                if ($is_three) {
                    if ($i_detail->groupid == 24) {
                        $sum++;
                    }
                } else {
                    if ($i_detail->groupid == 24) {
                        $sum++;
                    }
                }

            }
            if ($sum == 2) {
                var_dump($v['orderid']);
            }
        }

    }

    /**
     *
     */
    public function testFunction(Order $order, Excel $excel)
    {
        $config = [
            'appkey' => '24912242',
            'secretKey' => 'ae03bfe2bfd1d980a6ce3d377acee835',
            'format' => 'json',
            'sandbox' => false,
        ];

        $app = Factory::Tbk($config);
        $param = [
            'fields' => 'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick',
            'q' => '蚊香'
        ];
        $res = $app->uatm->getItemFavorites($param);

        var_dump($res);
        exit();
    }

    /**
     * 补充广告联盟的用户
     */
    public function addUserForAd()
    {
        $id = DB::connection('a1191125678')->table('pre_common_member')->insertGetId([
            'email' => '135893052121723011@rapp.com',
            'username' => '13589305212',
            'password' => '25f9e794323b453885f5181f1b624d0b',
            'secret' => ' ',
            'groupid' => 10,
            'groupexpiry' => '10',
            'regdate' => '1528078920',
            'timeoffset' => '9999',
            'pt_id' => '1723011',
            'pt_pid' => '1668759',
            'pt_username' => '13589305212',
            'check_code' => ' '
        ]);

        DB::connection('a1191125678')->table('pre_common_member_profile')->insert([
            'uid' => $id,
            'realname' => '13589305212',
            'gender' => '0',
            'birthyear' => '0',
            'birthmonth' => '0',
            'birthday' => '0',
            'constellation' => '',
            'zodiac' => '',
            'telephone' => '',
            'mobile' => '13589305212',
            'bio' => '',
            'interest' => '',
            'field1' => '',
            'field2' => '',
            'field3' => '',
            'field4' => '',
            'field5' => '',
            'field6' => '',
            'field7' => '',
            'field8' => ''
        ]);

        DB::connection('a1191125678')->table('pre_common_member_count')->insert([
            'uid' => $id,
            'extcredits1' => '0',
            'extcredits2' => '0',
            'extcredits3' => '0',
            'extcredits4' => '0',
            'extcredits5' => '0',
            'extcredits6' => '0',
            'extcredits7' => '0',
            'extcredits8' => '0',
        ]);


        dd($id);
    }

    /**
     *
     */
    public function testGetArticleByWu(Client $client, Article $article)
    {
        $list_url = 'http://m.yangtse.com/news/2';
        $res_list = $client->request('get', $list_url);

        $jsonRes = (string)$res_list->getBody();
        $arr_res_list = json_decode($jsonRes, true);

        foreach ($arr_res_list as $k => $v) {
            $detail_url = 'http://m.yangtse.com/content/app/' . $v['id'] . '.html';
            $res_body = $client->request('get', $detail_url);
            $res_body_html = (string)$res_body->getBody();
            $res_body_html = preg_replace('/<div class=\"am-btn-group am-btn-group-justify\"(.|\n)+?<\/div>/', ' ', $res_body_html);
            $res_body_html = preg_replace('/<div class=\"am-header-left am-header-nav\">(.|\n)+?<\/div>/', ' ', $res_body_html);
            $res_body_html = preg_replace('/<div data-am-widget=\"titlebar\" class=\"am-titlebar am-titlebar-default am-no-layout\"(.|\n)+?<\/div>/', ' ', $res_body_html);
            $res_body_html = preg_replace('/<div class=\"\" data-backend-compiled=\"\">(.|\n)+?<\/div>/', ' ', $res_body_html);
            $res_body_html = preg_replace('/<h2 class=\"am-text-primary\">(.|\n)+?<\/h2>/', ' ', $res_body_html);
            $res_body_html = preg_replace('/<h1 class=\"am-header-title\">(.|\n)+?<\/h1>/', ' ', $res_body_html);
            $res_body_html = preg_replace('/<p class=\"am-article-meta\">(.|\n)+?<\/p>/', '<p class="am-article-meta">' . date('Y-m-d H:i:s', time()) . ' &nbsp;&nbsp;葡萄浏览器</p>', $res_body_html);
            $res_body_html = preg_replace('/<div class=\"am-footer-switch\">(.|\n)+<\/div>/', ' ', $res_body_html);
            $is_need_jump = $article->getCanUseArticle('yzwb_wuhang' . $v['id']);
            if ($is_need_jump) {
                continue;
            }
            print_r($res_body_html);
            exit();
            $article->insertNewTitle($v['title'], 'http://api.36qq.com/display_news/yzwb_wuhang' . $v['id'], 1, 'yzwb_wuhang' . $v['id'], $res_body_html, $v['titlepic']);
        }
        return $arr_res_list;
    }
}
