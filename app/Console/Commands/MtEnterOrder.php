<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\MtEnterOrder as MtEnterOrderModel;
use App\Entitys\App\MtMaidOld;
use App\Services\MeiTuan\MeiTuanServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MtEnterOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MtEnterOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '美团订单抓取.假分佣 每天11点运行';

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
        //开始
        $this->info("start");

        //得到昨天的时间 YYYY-mm-dd
        $yesterday_time = date('Y-m-d', strtotime('yesterday'));

        //取总数据量计算分页
        $obj_mei_tuan_services = new MeiTuanServices();
        $arr_orders_data = $obj_mei_tuan_services->getVerifyOrderData(1, 1, $yesterday_time, $yesterday_time);
        if ($arr_orders_data['code'] != 200) {
            $this->info("接口请求异常code=" . $arr_orders_data['code']);
            die;
        }

        //总页数
        $page = 1;
        $size = 100;
        $all_page = ceil(($arr_orders_data['msg']['recordCount'] / $size));

        //分页获取美团昨天有效的所有订单
        $arr_mt_orders_data_all = [];
        while ($page <= $all_page) {
            $arr_orders_data_page = $obj_mei_tuan_services->getVerifyOrderData($page, $size, $yesterday_time, $yesterday_time);
            $arr_mt_orders_data_all = array_merge($arr_mt_orders_data_all, $arr_orders_data_page['msg']['records']);
            $page++;
        }

        //格式化数据 科学计数法展示的订单号;
        $data_orders = [];
        foreach ($arr_mt_orders_data_all as $k => $v) {
            $data_orders[$k]['app_id'] = $obj_mei_tuan_services->decrypt($v['utmMedium']);                                          #通过AES解密得到用户id

            /** 临时兼容*/
            $arr = [
                '1473091' => '4446218',
                '5891499' => '3675700',
                '5891502' => '9873668',
            ];
            $data_orders[$k]['app_id'] = !empty($arr[$data_orders[$k]['app_id']]) ? $arr[$data_orders[$k]['app_id']] : $data_orders[$k]['app_id'];

            $data_orders[$k]['verify_date'] = $v['verifyDate'];
            $data_orders[$k]['verify_time'] = $v['verifyTime'];
            $data_orders[$k]['item_id'] = number_format($v['itemId'], 0, '', '');               #格式化科学计数法字符
            $data_orders[$k]['unique_item_id'] = number_format($v['uniqueItemId'], 0, '', '');  #格式化科学计数法字符
            $data_orders[$k]['order_pay_time'] = $v['orderPayTime'];
            $data_orders[$k]['order_id'] = $v['orderId'];
            $data_orders[$k]['actual_item_amount'] = $v['actualItemAmount'];
            $data_orders[$k]['actual_order_amount'] = $v['actualOrderAmount'];
            $data_orders[$k]['shop_id'] = $v['shopId'];
            $data_orders[$k]['shop_uuid'] = $v['shopUuid'];
            $data_orders[$k]['shop_name'] = $v['shopName'];
            $data_orders[$k]['city_name'] = $v['cityName'];
            $data_orders[$k]['cat0_name'] = $v['cat0Name'];
            $data_orders[$k]['cat1_name'] = $v['cat1Name'];
            $data_orders[$k]['order_type'] = $v['orderType'];
            $data_orders[$k]['coupon_id'] = $v['couponId'];
            $data_orders[$k]['coupon_group_id'] = $v['couponGroupId'];
            $data_orders[$k]['coupon_discount_amount'] = $v['couponDiscountAmount'];
            $data_orders[$k]['coupon_price_limit'] = $v['couponPriceLimit'];
            $data_orders[$k]['balance_amount'] = $v['balanceAmount'];
            $data_orders[$k]['balance_commission_ratio'] = $v['balanceCommissionRatio'];
            $data_orders[$k]['order_user_id'] = $v['orderUserId'];
            $data_orders[$k]['item_status'] = $v['itemStatus'];
            $data_orders[$k]['balance_status'] = $v['balanceStatus'];
            $data_orders[$k]['settlement_type'] = $v['settlementType'];
            $data_orders[$k]['coupon_source'] = $v['couponSource'];
            $data_orders[$k]['order_platform'] = $v['orderPlatform'];
            $data_orders[$k]['utm_source'] = $v['utmSource'];
            $data_orders[$k]['utm_medium'] = $v['utmMedium'];
            $data_orders[$k]['modify_time'] = $v['modifyTime'];
            $data_orders[$k]['add_date'] = $v['addDate'];
        }

        //存入订单 至数据库lc_mt_enter_order
        $this->info('获取昨日有效订单量：' . count($data_orders));
        $this->syncOrders($data_orders);

        /**临时记录测试日志*/
        $this->log($data_orders);

        //开始假分佣
        $this->info('本次待分佣订单数：' . count($data_orders));
        $data_api = [];
        $status_api = [
            1 => 1,     #已结算
            2 => 2,     #待结算
            99 => 2,    #无需结算
        ];
        foreach ($data_orders as $order) {
            $this->info('唯一子订单号：' . $order['unique_item_id']);

            //整理筛选分佣所需数据
            $data_api[] = [
                'app_id' => $order['app_id'],#用户id
                'order_number' => $order['unique_item_id'],           #唯一子订单号
                'status' => @$status_api[$order['balance_status']],  #结算状态1=已结算 2=待结算 99=无需结算
                'commission' => $order['balance_amount'],            #分佣的金额
            ];
        }

        //假分佣
        $this->handleOrder($data_api);

        $this->info("end");
    }

    /*
     * 往数据库里面存数据
     */
    public function syncOrders($orders)
    {
        $obj_mt_enter_order = new MtEnterOrderModel();
        foreach ($orders as $item) {
            //无则新增
            $obj_mt_enter_order->firstOrCreate(['unique_item_id' => $item['unique_item_id']], $item);

            //有则更新
            $id_mt_order = $obj_mt_enter_order->where(['unique_item_id' => $item['unique_item_id']])->value('id');
            if (!empty($id_mt_order)) {
                $obj_mt_enter_order->where('id', $id_mt_order)->update($item);
            }
        }
        return true;
    }

    /*
     * 进行假分佣处理
     */
    public function handleOrder($real_data)
    {
        foreach ($real_data as $item) {
            $data = [
                'app_id' => $item['app_id'],
                'order_number' => $item['order_number'],
                'status' => $item['status'],
                'commission' => $item['commission'],
            ];

            if (Cache::has('m_t_' . $item['order_number'])) {
                return false;
            } else {
                Cache::put('m_t_' . $item['order_number'], 1, 0.2);
                $this->handleMtDataV1($data);
            }
        }
        return true;
    }

    /*
     * 添加假分佣逻辑
     */
    function handleMtDataV1($mt_data)
    {
        //再次确认订单是否存在
        $obj_ele_single = MtEnterOrderModel::whereRaw("unique_item_id='{$mt_data['order_number']}'")->first();
        if (empty($obj_ele_single)) {
            $this->info('异常订单：' . $mt_data['order_number']);
            return false;
        }

        switch (@$mt_data['status']) {
            case 1:
                //美团直属分
                $this->addOrderCommissionV1($mt_data['order_number'], $mt_data['app_id'], $mt_data['commission']);
                /**管理费分*/

                break;
            case 2:
                //删除直属分
                $this->reduceOrderCommissionV1($mt_data['order_number']);
                /**删除管理费*/

                break;
        }
        return true;
    }

    /*
     * 饿了么订单分佣操作
     */
    protected function addOrderCommissionV1($order_id, $app_id, $commission)
    {
        //得到分佣用户数据
        $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();
        if (empty($ad_user_info)) {
            $this->info('分佣失败，该用户不存在于淘宝联盟账号库！！app_id=' . $app_id);
            return false;
        }
        $group_id = $ad_user_info->groupid;

        if (in_array($group_id, [23, 24])) {
            $f_commission = round($commission * 0.645, 2);
        } else {
            $f_commission = round($commission * 0.42, 2);
        }

        $order_commission = $f_commission;

        if (MtMaidOld::where(['trade_id' => (string)$order_id, 'type' => 2])->exists()) {
            return $order_commission;
        }

        MtMaidOld::create([
            'father_id' => 0,
            'order_enter_id' => 0,
            'trade_id' => (string)$order_id,
            'app_id' => $app_id,
            'group_id' => $group_id,
            'maid_money' => $f_commission,
            'type' => 2,
            'real' => 0,
        ]);

        $count_partner = 0;
        $tmp_next_id = $ad_user_info->pt_pid;

        for ($i = 1; $i < 50; $i++) {
            if (empty($tmp_next_id)) {
                break;
            }

            //上级信息
            $parent_info = AdUserInfo::where('pt_id', $tmp_next_id)->first(['groupid', 'pt_pid', 'pt_id']);

            if (empty($parent_info)) {
                $this->info('分佣失败，上级用户' . $tmp_next_id . '不存在于淘宝联盟账号库！！');
                return false;
            }
            $p_groupid = $parent_info['groupid'];
            $p_pt_pid = $parent_info['pt_pid'];
            $p_pt_id = $parent_info['pt_id'];

            $tmp_next_id = $p_pt_pid;

            if ($i == 1) {
                if ($p_groupid == 23) {
                    $due_rmb = round($commission * 0.1, 2);
                } elseif ($p_groupid == 24) {
                    $due_rmb = round($commission * 0.1, 2);
                    $count_partner += 1;
                } else {
                    $due_rmb = round($commission * 0.05, 2);
                }
            } else {
                break;
//                if ($p_groupid != 24) {
//                    continue;
//                }
//                if ($count_partner == 0) {
//                    $due_rmb = round($commission * 0.05, 2);
//                } else {
//                    $due_rmb = round($commission * 0.025, 2);
//                }
//                $count_partner += 1;
            }

            if (empty($due_rmb)) {
                continue;
            }
            MtMaidOld::create([
                'father_id' => $app_id,
                'order_enter_id' => 0,
                'trade_id' => (string)$order_id,
                'app_id' => $p_pt_id,
                'group_id' => $p_groupid,
                'maid_money' => $due_rmb,
                'type' => 1,
                'real' => 0,
            ]);

            if ($count_partner >= 2) {
                break;
            }
        }

        return $order_commission;
    }

    /*
     * 根据美团订单号进行扣除分佣订单操作
     */
    protected function reduceOrderCommissionV1($order_id)
    {
        return MtMaidOld::where('trade_id', (string)$order_id)->delete();
    }

    /*
     * 记录日志
     */
    private function log($msg)
    {
        Storage::disk('local')->append('callback_document/mtmtmtm_order.txt', date('Y-m-d H:i:s') . var_export($msg, true));
    }
}
