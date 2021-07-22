<?php

namespace App\Http\Controllers\AgentWeb;

use App\Entitys\App\ShopAddress;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersOne;
use App\Entitys\App\ShopSupplierGoods;
use App\Http\Controllers\Common\Jump;
use App\Http\Controllers\Common\Oss;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use OSS\OssClient;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        return view('agent.index');
    }
    public function main(Request $request)
    {
        return view('agent.main');
    }
    public function centerOnline(Request $request)
    {

        $view_params = [
            'title' => '线上商品管理',
        ];


        return view('agent.center.online', $view_params);
    }
    public function centerCheck()
    {
        $view_params = [
            'title' => '审核商品管理',
        ];

        return view('agent.center.check', $view_params);
    }
    public function centerEduce()
    {
        $view_params = [
            'title' => '导出供应商订单',
        ];

        return view('agent.center.educe', $view_params);
    }
    public function centerCheckAddHtml(Request $request)
    {
        if ($request->isMethod('post')) {

            $supplier_id = $request->session()->get('users.supplier_id');

            $post_data = $request->all();
            foreach ($post_data as &$item) {
                if (!is_array($item)) {
                    $item = trim($item);
                }
            }

            $post_data['app_id'] = $supplier_id;
            $post_data['shop_id'] = $supplier_id;
            $post_data['real_sale_volume'] = 0;
            $post_data['click_number'] = 0;
            $post_data['sale_volume'] = 0;
            if (isset($post_data['zone'])) {
                $post_data['zone'] = str_replace("/", "", $post_data['zone']);
            }
            unset($post_data['provinceId']);
            unset($post_data['cityId']);
            $custom = array();
            foreach ($post_data['custom'] as $single) {
                $single = trim($single);
                $pieces = explode(':', $single);
                if (count($pieces) < 2) {
                    Jump::fail('自定义参数有误请检查');
                }
                $custom[$pieces[0]] = explode(',', $pieces[1]);
            }
            $post_data['custom'] = json_encode($custom, true);

            $post_data['sidle_img'] = $request->file('sidle_img');

            $arr_file = $request->file();
            $post_data['header_img'] = [];
            $post_data['detail_img'] = [];
            $post_data['detail_share_img'] = [];
            $post_data['video_url'] = [];


            foreach ($arr_file as $key => $single_file) {
                $id_list = explode("_", $key);
                if (count($id_list) == 3) {
                    if ($id_list[0] == 'header') {
                        $post_data['header_img'][] = $single_file;
                    } elseif ($id_list[0] == 'detail') {
                        $post_data['detail_img'][] = $single_file;
                    } elseif ($id_list[0] == 'share') {
                        $post_data['detail_share_img'][] = $single_file;
                    } elseif ($id_list[0] == 'video') {
                        $post_data['video_url'][] = $single_file;
                    }

                    unset($post_data[$key]);
                }
            }


            $post_data['area'] = implode(',', $post_data['area']);

            $ed_id = $request->get('id');
            $sidle_img = '';
            if (!empty($post_data['sidle_img'])) {
                $upload_header = Oss::upload($post_data['sidle_img'], 'shop_header');
                $sidle_img = $upload_header['object'];
            }
            if (empty($ed_id) || !empty($sidle_img)) {
                $post_data['sidle_img'] = $sidle_img;
            } else {
                unset($post_data['sidle_img']);
            }
            $data_header = array();
            foreach ($post_data['header_img'] as $shop_header) {
                $upload_header = Oss::upload($shop_header, 'header_img');
                $data_header[] = $upload_header['object'];
            }
            if (empty($ed_id) || !empty($data_header)) {
                $post_data['header_img'] = json_encode($data_header, JSON_FORCE_OBJECT);
            } else {
                unset($post_data['header_img']);
            }
            $data_detail = array();
            foreach ($post_data['detail_img'] as $shop_detail) {
                $upload_detail = Oss::upload($shop_detail, 'detail_img');
                $data_detail[] = $upload_detail['object'];
            }
            if (empty($ed_id) || !empty($data_detail)) {
                $post_data['detail_img'] = json_encode($data_detail, JSON_FORCE_OBJECT);
            } else {
                unset($post_data['detail_img']);
            }
            $data_share = array();
            foreach ($post_data['detail_share_img'] as $shop_share) {
                $upload_detail = Oss::upload($shop_share, 'detail_share_img');
                $data_share[] = $upload_detail['object'];
            }
            if (empty($ed_id) || !empty($data_share)) {
                $post_data['detail_share_img'] = json_encode($data_share, JSON_FORCE_OBJECT);
            } else {
                unset($post_data['detail_share_img']);
            }
            $data_mp4 = array();
            foreach ($post_data['video_url'] as $shop_mp4) {
                $upload_mp4 = Oss::upload($shop_mp4, 'video_url');
                $data_mp4[] = $upload_mp4['object'];
            }
            if (empty($ed_id) || !empty($data_mp4)) {
                $post_data['video_url'] = json_encode($data_mp4, JSON_FORCE_OBJECT);
            } else {
                unset($post_data['video_url']);
            }


            $client = new Client();

            $post_api_data = [
                'data' => json_encode($post_data),
            ];
            $url = "http://api.36qq.com/admin_push_supplier_goods";
            $group_data = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => $post_api_data
            ];
            $res = $client->request('POST', $url, $group_data);

            $json_res = (string)$res->getBody();
            $arr_res = json_decode($json_res, true);


            if (@$arr_res['code'] != 200) {
                Jump::fail('上传商品失败！');
            }
            Jump::win('操作成功！', '/agent_admin', 1500);

        }

        $view_params = [
            'title' => '线上商品管理',
        ];

        return view('agent.center.check_add', $view_params);
    }
    public function centerCheckEdHtml(Request $request)
    {


        $ed_id = $request->get('ed_id');

        if (empty($ed_id)) {
            Jump::fail('异常操作！');
        }

        $data = ShopSupplierGoods::find($ed_id);
        $arrCustom = array();
        foreach (json_decode($data->custom, true) as $key => $arrValue) {
            $arrCustom[] = $key . ':' . implode(",", $arrValue);
        }
        $data->custom = $arrCustom;

        $data->area = explode(",", $data->area);

        $view_params = [
            'title' => '线上商品管理',
            'data' => $data
        ];

        return view('agent.center.check_add', $view_params);


    }
    public function centerCheckDeHtml(Request $request)
    {
        $de_id = $request->get('de_id');

        if (empty($de_id)) {
            return $this->getInfoResponse('1001', '错误请求');
        }

        ShopSupplierGoods::destroy($de_id);

        return $this->getResponse('操作成功');

    }
    function exportExcelGoods(Request $request, ShopOrders $shopOrders, ShopGoods $shopGoods, ShopOrdersOne $shopOrdersOne, ShopAddress $shopAddress)
    {
        $arr_request = $request->toArray();
        $arr_request['status'] = is_null($arr_request['status']) ? "" : $arr_request['status'];
        $arr_request['begin_time'] = empty($arr_request['begin_time']) ? "" : $arr_request['begin_time'];
        $arr_request['end_time'] = empty($arr_request['end_time']) ? "" : $arr_request['end_time'];
        $supplier_id = $request->session()->get('users.supplier_id');
        $supplier_id = empty($supplier_id) ? "" : $supplier_id;
        $where_params_one = [];
        if (!empty($arr_request['begin_time']) && !empty($arr_request['end_time'])) {
            $where_params_one['created_at'] = ['between',
                [
                    $arr_request['begin_time'], $arr_request['end_time']
                ]
            ];
        }
        switch (true) {
            case $arr_request['status'] === '0':
                $sort = '待付款';
                break;
            case $arr_request['status'] == 1:
                $sort = '待发货';
                break;
            case  $arr_request['status'] == 2:
                $sort = '待收货';
                break;
            case  $arr_request['status'] == 3:
                $sort = '待评价';
                break;
            case  $arr_request['status'] == 4:
                $sort = '退款与售后';
                break;
            default:
                $sort = '所有订单';
        }
        $arrGoods = $shopGoods->where(['shop_id' => $supplier_id])->select(['id'])->get();
        if (empty($arrGoods)) {
            Jump::fail('此供应商没有商品！');
        }

        foreach ($arrGoods as $k => $arrGood) {
            $arrGoods[$k] = $arrGood->id;
        }

        $res_order_one = $shopOrdersOne
            ->whereIn('good_id', array_values($arrGoods->toArray()))
            ->where('status', 1)
            ->get();
        foreach ($res_order_one as $k => $v) {
            $res_order = $shopOrders->getById($v->order_id);
            if (!$res_order) {
                unset($res_order_one[$k]);
                continue;
            }
            $res_order_one[$k]->order_info = $res_order->order_id;
            $address = $shopAddress->getOneAddress($res_order->address_id);
            if ($address) {
                $res_order_one[$k]->collection = $address->collection;
                $res_order_one[$k]->phone = $address->phone;
                $res_order_one[$k]->zone = $address->zone;
                $res_order_one[$k]->detail = $address->detail;
                $res_order_one[$k]->real_price = $res_order->real_price;
                $res_order_one[$k]->ptb_number = $res_order->ptb_number;
            } else {
                $res_order_one[$k]->collection = '';
                $res_order_one[$k]->phone = '';
                $res_order_one[$k]->zone = '';
                $res_order_one[$k]->detail = '';
                $res_order_one[$k]->real_price = '';
                $res_order_one[$k]->ptb_number = '';
            }
            $good = $shopGoods->getOneGoodById($v->good_id);
            if ($good) {
                $res_order_one[$k]->good_title = $good->title;
            }
        }

        $data = array();
        $arrList = $res_order_one;
        foreach ($arrList as $singleData) {
            switch ($singleData->status) {
                case 0:
                    $status = '待付款';
                    break;
                case 1:
                    $status = '待发货（已付款）';
                    break;
                case 2:
                    $status = '待收货（已发货）';
                    break;
                case 3:
                    $status = '待评价';
                    break;
                case 4:
                    $status = '退款与售后';
                    break;
                default :
                    $status = '状态异常';
            }

            $data[] = [
                'id' => $singleData->id,
                'order_info' => $singleData->order_info,
                'app_id' => $singleData->app_id,
                'good_id' => $singleData->good_id,
                'good_title' => preg_replace('/\s/', "", $singleData->good_title),
                'collection' => $singleData->collection,
                'phone' => $singleData->phone,
                'zone' => preg_replace('/\s/', "", $singleData->zone),
                'detail' => preg_replace('/\s/', "", $singleData->detail),
                'number' => $singleData->number,
                'desc' => preg_replace('/\s/', "", $singleData->desc),
                'express' => $singleData->express,
                'status' => $status,
                'postage' => $singleData->postage,
                'real_price' => $singleData->real_price,
                'ptb_number' => $singleData->ptb_number,
                'created_at' => $singleData->created_at,
                'updated_at' => $singleData->updated_at,
            ];
        }
        $filename = '发货列表-' . $sort . date('-Y_m_d_H_i_s');
        $this->create_xls($data, $filename);

    }

    /*
     * 导出csv
     */
    protected function create_xls($csv_body, $filename)
    {
        $data = ['订单ID', '订单号', '购买用户ID', '商品id', '商品标题', '收货人信息', '收货人电话', '收货人所在地区',
            '收货人详细地址', '发货数量', '用户留言以及选择的参数信息', '发货订单号', '订单状态',
            '实际支付邮费', '实际支付金额', '实际支付葡萄币', '创建时间', '更新时间', '发货快递单号'];
        $header = implode(',', $data) . PHP_EOL;
        $content = '';
        foreach ($csv_body as $k => $v) {
            $content .= implode(',', $v) . PHP_EOL;
        }
        $csv = $header . $content;
        header("Content-type:text/csv;");
        header("Content-Disposition:attachment;filename=" . $filename.'.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $csv;
        exit;
    }

}
