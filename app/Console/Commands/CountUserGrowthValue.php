<?php

namespace App\Console\Commands;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\GrowthUserValue;
use App\Entitys\App\GrowthUserValueChange;
use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\JdEnterOrders;
use App\Entitys\App\JdMaidOld;
use App\Entitys\App\PddEnterOrders;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\ShopOrdersOne;
use App\Entitys\App\ShopVipBuy;
use App\Entitys\App\TaobaoEnterOrder;
use App\Entitys\App\TaobaoMaidOld;
use App\Services\UpgradeVip\ChangeVipService;
use Illuminate\Console\Command;

class CountUserGrowthValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CountUserGrowthValue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '21日执行 计算用户上月成长值';

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
        $this->info('start');
        //获取成长值比例
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');

        //得到上月的时间范围
        $last_month_first = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $last_month_last = date('Y-m-t 23:59:59', strtotime('-1 month'));

        /**===========================淘宝=============================*/
        //淘宝订单表 报销假表 用户信息表
        $obj_taobao_enter_order = new TaobaoEnterOrder();
        $obj_taobao_maid_old = new TaobaoMaidOld();
        $obj_ad_user_info = new AdUserInfo();

        //获取淘宝上月所有有效订单数量
        $num_objs_taobao_data = $obj_taobao_enter_order->WhereIn('tk_status', [3, 12, 14])
            ->whereBetween('create_time', [$last_month_first, $last_month_last])
            ->count();

        /**临时测试用 以下查询 线上删除*/
//        $num_objs_taobao_data = $obj_taobao_enter_order->WhereIn('tk_status', [3, 12])
//            ->count();

        $tb_page_size = 10000; #页大小
        $tb_page_total = ceil($num_objs_taobao_data / $tb_page_size); #总页数

        //分页处理数据
        for ($page = 1; $page <= $tb_page_total; $page++) {
            //获取淘宝上月所有有效订单分页数据
            $objs_taobao_data = $obj_taobao_enter_order->WhereIn('tk_status', [3, 12, 14])
                ->whereBetween('create_time', [$last_month_first, $last_month_last])
                ->forPage($page, $tb_page_size)
                ->get();

            /**测试用 以下查询 线上删除*/
//            $objs_taobao_data = $obj_taobao_enter_order->WhereIn('tk_status', [3, 12])
//                ->forPage($page, $tb_page_size)
//                ->get();

            $this->info('开始处理淘宝第' . $page . '/' . $tb_page_total . '页订单数据');

            //循环处理上月有效淘宝订单分页数据
            foreach ($objs_taobao_data as $v) {
                //取到该笔订单 报销的数据
                $obj_data_maid_old = $obj_taobao_maid_old->where(['type' => 2, 'trade_id' => $v->trade_id])->first();

                if (empty($obj_data_maid_old)) {
                    $this->info('不存在该笔淘宝订单的报销记录:trade_id=' . $v->trade_id);
                    continue;
                }

                //取该用户等级 只处理 groupid=10的用户
                $int_user_groupid = $obj_ad_user_info->where(['pt_id' => $obj_data_maid_old->app_id])->value('groupid');

                if ($int_user_groupid == 10) {
                    //根据该笔订单报销金额计算出该成长值分数
                    $growth_value = round($obj_data_maid_old->maid_money / $num_growth_value, 2);

                    //用户成长值表
                    $obj_growth_user_value = new GrowthUserValue();
                    //得到该用户成长值数据
                    $obj_data_groth_value = $obj_growth_user_value->where('app_id', $obj_data_maid_old->app_id)->first();

                    //存在该用户数据则累加成长值 否则新建
                    if (!empty($obj_data_groth_value)) {
                        $obj_data_groth_value->growth += $growth_value;
                        $obj_data_groth_value->save();
                    } else {
                        $obj_growth_user_value->app_id = $obj_data_maid_old->app_id;
                        $obj_growth_user_value->growth = $growth_value;
                        $obj_growth_user_value->save();
                    }

                    //成长值变化表
                    $obj_growth_user_value_change = new GrowthUserValueChange();

                    //增加该用户的成长值变化记录
                    $obj_growth_user_value_change->app_id = $obj_data_maid_old->app_id;
                    $obj_growth_user_value_change->growth_value_before = empty($obj_data_groth_value->growth) ? 0 : $obj_data_groth_value->growth - $growth_value; #变化前
                    $obj_growth_user_value_change->growth_value = $growth_value;                                                                                   #变化值
                    $obj_growth_user_value_change->growth_value_after = empty($obj_data_groth_value->growth) ? $growth_value : $obj_data_groth_value->growth;      #变化后
                    $obj_growth_user_value_change->title = $v->item_title;
                    $obj_growth_user_value_change->from_type = 2; #淘报销为2
                    $obj_growth_user_value_change->get_time = strtotime($v->create_time);
                    $obj_growth_user_value_change->status = $v->tk_status;
                    $obj_growth_user_value_change->save();
                } else {
                    $this->info('该用户等级为:' . $int_user_groupid . '非普通用户不处理');
                    continue;
                }
            }
        }

        /**===========================拼多多=============================*/
        //拼多多订单表 报销假表
        $obj_pdd_enter_order = new PddEnterOrders();
        $obj_pdd_maid_old = new PddMaidOld();

        //获取拼多多上月所有有效订单数量
        $objs_pdd_data = $obj_pdd_enter_order->WhereIn('order_status', [1, 2, 3, 5])
            ->whereBetween('order_create_time', [strtotime($last_month_first), strtotime($last_month_last)])
            ->count();

        $pdd_page_size = 10000; #页大小
        $pdd_page_total = ceil($objs_pdd_data / $pdd_page_size); #总页数

        //分页处理数据
        for ($page = 1; $page <= $pdd_page_total; $page++) {

            //获取拼多多上月所有有效订单
            $objs_pdd_data = $obj_pdd_enter_order->WhereIn('order_status', [1, 2, 3, 5])
                ->whereBetween('order_create_time', [strtotime($last_month_first), strtotime($last_month_last)])
                ->forPage($page, $pdd_page_size)
                ->get();

            $this->info('开始处理拼多多第' . $page . '/' . $pdd_page_total . '页订单数据');

            //循环处理上月有效拼多多订单
            foreach ($objs_pdd_data as $v) {
                //取到该笔拼多多订单 报销的数据
                $obj_data_pdd_maid_old = $obj_pdd_maid_old->where(['type' => 2, 'trade_id' => $v->order_sn, 'app_id' => $v->app_id])->first();

                if (empty($obj_data_pdd_maid_old)) {
                    $this->info('不存在该笔拼多多订单的报销记录:trade_id=' . $v->order_sn);
                    continue;
                }

                //取该用户等级 只处理 groupid=10的用户
                $int_user_groupid = $obj_ad_user_info->where(['pt_id' => $obj_data_pdd_maid_old->app_id])->value('groupid');

                if ($int_user_groupid == 10) {
                    //根据该笔订单报销金额计算出该成长值分数
                    $growth_value = round($obj_data_pdd_maid_old->maid_money / $num_growth_value, 2);

                    //用户成长值表
                    $obj_growth_user_value = new GrowthUserValue();
                    //得到该用户成长值数据
                    $obj_data_groth_value = $obj_growth_user_value->where('app_id', $obj_data_pdd_maid_old->app_id)->first();

                    //存在该用户数据则累加成长值 否则新建
                    if (!empty($obj_data_groth_value)) {
                        $obj_data_groth_value->growth += $growth_value;
                        $obj_data_groth_value->save();
                    } else {
                        $obj_growth_user_value->app_id = $obj_data_pdd_maid_old->app_id;
                        $obj_growth_user_value->growth = $growth_value;
                        $obj_growth_user_value->save();
                    }

                    //成长值变化表
                    $obj_growth_user_value_change = new GrowthUserValueChange();

                    //增加该用户的成长值变化记录
                    $obj_growth_user_value_change->app_id = $obj_data_pdd_maid_old->app_id;
                    $obj_growth_user_value_change->growth_value_before = empty($obj_data_groth_value->growth) ? 0 : $obj_data_groth_value->growth - $growth_value; #变化前
                    $obj_growth_user_value_change->growth_value = $growth_value;                                                                                   #变化值
                    $obj_growth_user_value_change->growth_value_after = empty($obj_data_groth_value->growth) ? $growth_value : $obj_data_groth_value->growth;      #变化后
                    $obj_growth_user_value_change->title = $v->goods_name;
                    $obj_growth_user_value_change->from_type = 3; #拼多多报销为3
                    $obj_growth_user_value_change->get_time = $v->order_create_time;
                    $obj_growth_user_value_change->status = $v->order_status;
                    $obj_growth_user_value_change->save();
                } else {
                    $this->info('该用户等级为:' . $int_user_groupid . '非普通用户不处理');
                    continue;
                }
            }
        }

        /**===========================京东=============================*/
        //京东订单表 报销假表
        $obj_jd_enter_order = new JdEnterOrders();
        $obj_jd_maid_old = new JdMaidOld();

        //获取京东上月所有有效订单数量
        $objs_jd_data = $obj_jd_enter_order->where(['validCode' => 17, 'frozenSkuNum' => 0, 'skuReturnNum' => 0])
            /**测试以下条件加注释*/
            ->whereBetween('orderTime', [strtotime($last_month_first) . '000', strtotime($last_month_last) . '000'])
            ->count();

        $jd_page_size = 10000; #页大小
        $jd_page_total = ceil($objs_jd_data / $jd_page_size); #总页数

        //分页处理数据
        for ($page = 1; $page <= $jd_page_total; $page++) {

            //获取京东上月所有有效订单
            $objs_jd_data = $obj_jd_enter_order->where(['validCode' => 17, 'frozenSkuNum' => 0, 'skuReturnNum' => 0])
                /**测试以下条件加注释*/
                ->whereBetween('orderTime', [strtotime($last_month_first) . '000', strtotime($last_month_last) . '000'])
                ->forPage($page, $pdd_page_size)
                ->get();

            $this->info('开始处理京东第' . $page . '/' . $jd_page_total . '页订单数据');

            //循环处理上月有效京东订单
            foreach ($objs_jd_data as $v) {
                //取到该笔京东订单 报销的数据
                $obj_data_jd_maid_old = $obj_jd_maid_old->where(['type' => 2, 'trade_id' => $v->orderId, 'app_id' => $v->app_id, 'sku_id' => $v->skuId])->first();

                if (empty($obj_data_jd_maid_old)) {
                    $this->info('不存在该笔京东订单的报销记录:trade_id=' . $v->orderId . ':skuId=' . $v->skuId);
                    continue;
                }

                //取该用户等级 只处理 groupid=10的用户
                $int_user_groupid = $obj_ad_user_info->where(['pt_id' => $obj_data_jd_maid_old->app_id])->value('groupid');

                if ($int_user_groupid == 10) {
                    //根据该笔订单报销金额计算出该成长值分数
                    $growth_value = round($obj_data_jd_maid_old->maid_money / $num_growth_value, 2);

                    //用户成长值表
                    $obj_growth_user_value = new GrowthUserValue();
                    //得到该用户成长值数据
                    $obj_data_groth_value = $obj_growth_user_value->where('app_id', $obj_data_jd_maid_old->app_id)->first();

                    //存在该用户数据则累加成长值 否则新建
                    if (!empty($obj_data_groth_value)) {
                        $obj_data_groth_value->growth += $growth_value;
                        $obj_data_groth_value->save();
                    } else {
                        $obj_growth_user_value->app_id = $obj_data_jd_maid_old->app_id;
                        $obj_growth_user_value->growth = $growth_value;
                        $obj_growth_user_value->save();
                    }

                    //成长值变化表
                    $obj_growth_user_value_change = new GrowthUserValueChange();

                    //增加该用户的成长值变化记录
                    $obj_growth_user_value_change->app_id = $obj_data_jd_maid_old->app_id;
                    $obj_growth_user_value_change->growth_value_before = empty($obj_data_groth_value->growth) ? 0 : $obj_data_groth_value->growth - $growth_value; #变化前
                    $obj_growth_user_value_change->growth_value = $growth_value;                                                                                   #变化值
                    $obj_growth_user_value_change->growth_value_after = empty($obj_data_groth_value->growth) ? $growth_value : $obj_data_groth_value->growth;      #变化后
                    $obj_growth_user_value_change->title = $v->skuName;
                    $obj_growth_user_value_change->from_type = 4; #京东报销为4
                    $obj_growth_user_value_change->get_time = $v->orderTime;
                    $obj_growth_user_value_change->status = $v->validCode;
                    $obj_growth_user_value_change->save();
                } else {
                    $this->info('该用户等级为:' . $int_user_groupid . '非普通用户不处理');
                    continue;
                }
            }
        }

        /**===========================爆款商城=============================*/
        //爆款商城订单表 [] 代理商商品id表 分佣表
        $obj_shop_orders = new ShopOrders();
        $obj_shop_orders_one = new ShopOrdersOne();
        $obj_shop_vip_buy = new ShopVipBuy();
        $obj_shop_orders_maid = new ShopOrdersMaid();

        //获取爆款商城上月所有确认收货的订单总数量
        $num_shop_data = $obj_shop_orders->Where('status', 3)
            /**测试以下条件加注释*/
            ->whereBetween('created_at', [$last_month_first, $last_month_last])
            ->count();

        $page_size = 10000; #页大小
        $page_total = ceil($num_shop_data / $page_size); #总页数

        //分页处理数据
        for ($page = 1; $page <= $page_total; $page++) {
            //获取爆款商城上月所有确认收货的订单
            $objs_page_shop_data = $obj_shop_orders->Where('status', 3)
                /**测试以下条件加注释*/
                ->whereBetween('created_at', [$last_month_first, $last_month_last])
                ->forPage($page, $page_size)
                ->get();

            $this->info('开始处理爆款商城第' . $page . '/' . $page_total . '页订单数据');

            //循环处理上月确认收货的订单 分页数据
            foreach ($objs_page_shop_data as $v) {
                //取该用户等级 只处理 groupid=10的用户
                $int_user_groupid = $obj_ad_user_info->where(['pt_id' => $v->app_id])->value('groupid');

                if ($int_user_groupid == 10) {
                    //取lc_shop_orders_one对应订单的good_id
                    $good_id = $obj_shop_orders_one->where('order_id', $v->id)->value('good_id');

                    //取所有的代理商商品的 vip_id
                    $arr_vip_id = $obj_shop_vip_buy->pluck('vip_id')->toArray();

                    //该商品的 good_id 是否为 vip_id 是跳过该商品
                    if (in_array($good_id, $arr_vip_id)) {
                        $this->info('该订单商品为代理商商品不计算成长值order_id=' . $v->order_id);
                        continue;
                    }

                    //取到该笔爆款商城订单 id最小的报销数据
                    $obj_data_shop_maid_old = $obj_shop_orders_maid->where('order_id', $v->order_id)->orderBy('id')->first();

                    if (empty($obj_data_shop_maid_old)) {
                        $this->info('不存在该笔爆款商城的报销记录:order_id=' . $v->order_id);
                        continue;
                    }

                    //我的币转元
                    $money = $obj_data_shop_maid_old->money / 10;

                    //取爆款单独计算比例
                    $num_growth_shop_value = $obj_growth_user_value_Config->value('growth_shop_config_value');
                    //根据该笔订单报销金额计算出该成长值分数
                    $growth_value = round($money / $num_growth_shop_value, 2);

                    //用户成长值表
                    $obj_growth_user_value = new GrowthUserValue();
                    //得到该用户成长值数据
                    $obj_data_groth_value = $obj_growth_user_value->where('app_id', $obj_data_shop_maid_old->app_id)->first();

                    //存在该用户数据则累加成长值 否则新建
                    if (!empty($obj_data_groth_value)) {
                        $obj_data_groth_value->growth += $growth_value;
                        $obj_data_groth_value->save();
                    } else {
                        $obj_growth_user_value->app_id = $obj_data_shop_maid_old->app_id;
                        $obj_growth_user_value->growth = $growth_value;
                        $obj_growth_user_value->save();
                    }

                    //成长值变化表
                    $obj_growth_user_value_change = new GrowthUserValueChange();

                    //增加该用户的成长值变化记录
                    $obj_growth_user_value_change->app_id = $obj_data_shop_maid_old->app_id;
                    $obj_growth_user_value_change->growth_value_before = empty($obj_data_groth_value->growth) ? 0 : $obj_data_groth_value->growth - $growth_value; #变化前
                    $obj_growth_user_value_change->growth_value = $growth_value;                                                                                   #变化值
                    $obj_growth_user_value_change->growth_value_after = empty($obj_data_groth_value->growth) ? $growth_value : $obj_data_groth_value->growth;      #变化后
                    $obj_growth_user_value_change->title = '爆款商城商品id:' . $v->order_id;
                    $obj_growth_user_value_change->from_type = 1; #爆款商城报销为1
                    $obj_growth_user_value_change->get_time = strtotime($v->created_at);
                    $obj_growth_user_value_change->status = $v->status;
                    $obj_growth_user_value_change->save();
                } else {
                    $this->info('该用户等级为:' . $int_user_groupid . '非普通用户不处理');
                    continue;
                }
            }
        }

        /**===========================后续逻辑处理growth>=100的用户=============================*/
        $obj_change_vip_service = new ChangeVipService();
        //循环处理growth>=100的用户数据
        $all_data = $obj_growth_user_value->where('growth', '>=', 100)->where('is_vip', 0)->get();
        foreach ($all_data as $v) {
            //判断用户是否为普通用户
            $int_user_groupid = $obj_ad_user_info->where(['pt_id' => $v->app_id])->value('groupid');
            if ($int_user_groupid == 10) {
                //升级
                $obj_change_vip_service->installOrder($v->app_id, 2, '通过成长值大于100升级超级用户');
                $obj_change_vip_service->upgradeGroup($v->app_id);
                $obj_change_vip_service->installGrowthOrder($v->app_id, 1);
                $obj_change_vip_service->updateGrowthUser($v->app_id);
            }else{
                $this->info('该用户等级为:' . $int_user_groupid . '非普通用户不处理');
                continue;
            }
        }


        $this->info('end');
    }
}
