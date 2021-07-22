<?php

namespace App\Http\Controllers\AgentWeb;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class JsonController extends Controller
{
    public function systemParameter(Request $request)
    {
        $users = $request->session()->get('users');
        $users['last_time'] = date('Y年m月d日 H:i:s', $users['last_time']);
        return $users;
    }
    public function onlineGoods(Request $request, Client $client)
    {

        $post_data = $request->all();

        $validator = Validator::make($post_data, [
            'page' => 'required|numeric',
            'limit' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->getInfoResponse(4001, '错误请求');
        }

        $page = $post_data['page'];
        $limit = $post_data['limit'];

        $supplier_id = $request->session()->get('users.supplier_id');

        $post_api_data = [
            'data' => '{"supplier_id":' . $supplier_id . '}',
        ];
        $url = "http://api.36qq.com/admin_goods_supplier?page={$page}&limit={$limit}";
        $group_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data
        ];
        $res = $client->request('POST', $url, $group_data);

        $json_res = (string)$res->getBody();
        $arr_res = json_decode($json_res, true);

        if (@$arr_res['code'] != 200) {
            return $this->getInfoResponse(3001, '数据拉取失败');
        }

        $params['code'] = 200;
        $params['msg'] = '请求成功';
        $params['count'] = $arr_res['data']['total'];
        $params['data'] = $arr_res['data']['data'];

        return $params;
    }
    public function checkGoods(Request $request, Client $client)
    {

        $post_data = $request->all();

        $validator = Validator::make($post_data, [
            'page' => 'required|numeric',
            'limit' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->getInfoResponse(4001, '错误请求');
        }

        $page = $post_data['page'];
        $limit = $post_data['limit'];

        $supplier_id = $request->session()->get('users.supplier_id');

        $post_api_data = [
            'data' => '{"supplier_id":' . $supplier_id . '}',
        ];
        $url = "http://api.36qq.com/admin_goods_supplier_goods?page={$page}&limit={$limit}";
        $group_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data
        ];
        $res = $client->request('POST', $url, $group_data);

        $json_res = (string)$res->getBody();
        $arr_res = json_decode($json_res, true);

        if (@$arr_res['code'] != 200) {
            return $this->getInfoResponse(3001, '数据拉取失败');
        }

        $params['code'] = 200;
        $params['msg'] = '请求成功';
        $params['count'] = $arr_res['data']['total'];
        $params['data'] = $arr_res['data']['data'];

        return $params;
    }


}
