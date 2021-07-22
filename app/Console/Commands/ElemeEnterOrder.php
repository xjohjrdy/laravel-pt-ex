<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\AlimamaInfoNew;
use App\Entitys\App\EleEnterOrder;
use App\Entitys\App\EleMaidOld;
use App\Services\Alimama\NewAliOrderService;
use App\Services\Other\ShopCommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ElemeEnterOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ElemeEnterOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '饿了么订单抓取.假分佣';

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
        //获取今天 和昨天前6分钟的订单
        $new_ali_order_service = new NewAliOrderService();
        $this->info("start");
        $orders_0_day = $new_ali_order_service->getChannelOrders(1);
        $orders_1_day = $new_ali_order_service->getChannelOrders(2);
        $host_orders = array_merge($orders_0_day, $orders_1_day);

        //筛选饿了么的订单 取用户app_id
        $ele_order = [];
        foreach ($host_orders as $v) {
            //得到用户app_id
            if ($v['adzone_id'] == '109375250125') {
                $obj_alimama_info = new AlimamaInfo();
                $app_id = $obj_alimama_info->where(['relation_id' => $v['relation_id'], 'adzone_id' => $v['adzone_id']])->value('app_id');
            } else {
                $obj_alimama_info_new = new AlimamaInfoNew();
                $app_id = $obj_alimama_info_new->where(['relation_id' => $v['relation_id'], 'adzone_id' => $v['adzone_id']])->value('app_id');
            }
            if (empty($app_id)) {
                $this->info('渠道id：' . $v['relation_id'] . ',adzone_id:' . $v['adzone_id'] . '不存在');
                continue;
            }
            $v['app_id'] = $app_id;

            //取饿了么订单
            if (strstr($v['order_type'], '饿了么')) {
                $ele_order[] = $v;
            }
        }
        $this->info('最近订单获取数量：' . count($ele_order));

        //存入订单 至数据库lc_ele_enter_order
        $this->syncOrders($ele_order);

        //开始假分佣
        $this->info('本次待分佣订单数：' . count($ele_order));
        $data_api = [];
        $status_api = [
            3 => 2,     //3：订单结算
            12 => 1,    //12：订单付款
            13 => 3,    //13：订单失效
            14 => 1     //14：订单成功
        ];
        foreach ($ele_order as $order) {
            $this->info('订单号：' . $order['trade_id']);
            $data_api[] = [
                'app_id' => $order['app_id'],
                'order_number' => $order['trade_id'],
                'status' => @$status_api[$order['tk_status']],
                'commission' => $order['pub_share_pre_fee'],
                'taobao_time' => strtotime($order['tk_create_time']),
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
        $obj_ele_order_new = new EleEnterOrder();
        foreach ($orders as $item) {
            $obj_ele_order_new->firstOrCreate(['trade_id' => $item['trade_id']], $item);

            //有则更新
            $id_ele_new = $obj_ele_order_new->where(['trade_id' => $item['trade_id']])->value('id');
            if (!empty($id_ele_new)) {
                $obj_ele_order_new->where('id', $id_ele_new)->update($item);
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
                'taobao_time' => $item['taobao_time'],
                'create_time' => time(),
                'admin_id' => "0"//操作人 0表示后台
            ];

            if (Cache::has('e_l_e_' . $item['order_number'])) {
                return false;
            } else {
                Cache::put('e_l_e_' . $item['order_number'], 1, 0.2);
                $this->handleEleDataV1($data);
            }
        }
        return true;
    }

    /*
     * 添加假分佣逻辑
     */
    function handleEleDataV1($ele_data)
    {
        //再次确认订单是否存在
        $obj_ele_single = EleEnterOrder::whereRaw("trade_id='{$ele_data['order_number']}'")->first();
        if (empty($obj_ele_single)) {
            $this->info('异常订单：' . $ele_data['order_number']);
            return false;
        }

        $obj_Shop_commission_service = new ShopCommissionService();
        switch (@$ele_data['status']) {
            case 1:
                //剥离多级
                $this->addOrderCommissionV1($ele_data['order_number'], $ele_data['app_id'], $ele_data['commission']);//饿了么直属分
                break;
            case 2:
                $this->addOrderCommissionV1($ele_data['order_number'], $ele_data['app_id'], $ele_data['commission']);//饿了么直属分
                break;
            case 3:
                $this->reduceOrderCommissionV1($ele_data['order_number']);//删除直属分
                break;
        }
        return true;
    }

    /*
     * 饿了么订单分佣操作
     */
    protected function addOrderCommissionV1($order_id, $app_id, $commission)
    {

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

        if (EleMaidOld::where(['trade_id' => (string)$order_id, 'type' => 2])->exists()) {
            return $order_commission;
        }

        EleMaidOld::create([
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
            EleMaidOld::create([
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
     * 根据饿了么订单号进行扣除分佣订单操作
     */
    protected function reduceOrderCommissionV1($order_id)
    {
        return EleMaidOld::where('trade_id', (string)$order_id)->delete();
    }
}
