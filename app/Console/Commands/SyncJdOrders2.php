<?php

namespace App\Console\Commands;

use App\Entitys\App\JdEnterOrders;
use App\Entitys\App\JdEnterOrdersFirst;
use App\Services\JdCommodity\JdCommodityServices;
use Illuminate\Console\Command;

class SyncJdOrders2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SyncJdOrders2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command 京东抓取';

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
        //
        $jd_enter_order = new JdEnterOrders();
        $jd_enter_order_first = new JdEnterOrdersFirst();
        $time = strtotime('2020-04-07 19:07:31');
        $current = strtotime('2020-04-29 00:10:32');
        // 拉取当前时间前两分钟的订单数据
        while (1){
            $minute = substr(date("YmdHis", $time), 0, 10);
            $this->info($minute);
            if($time > $current){
                break;
            }
            $results = $this->getOrdersPages($minute);
            foreach ($results as $t) {
                if ((int)$t['siteId'] == 0) {
                    $jd_enter_order->insertOrUpdate($t);
                }
                if ((int)$t['siteId'] == 1046958515) {
                    $jd_enter_order->insertOrUpdate($t);
                }
                $jd_enter_order_first->insertOrUpdate($t);
            }

            $time = strtotime("+1 hour", $time);
//            dd(date( 'Y-m-d H:m:s',$time));

        }

        $this->info("end: ");

    }

    public function getOrdersPages($time)
    {
        $result_array = [];
        $jd_service = new JdCommodityServices();
        $page = 1;
        while (true) {
            $result = json_decode($jd_service->getOrder($time, 3, $page, 10), true);
            if (@$result['message'] == 'success') {
                $data_list = empty(@$result['data']['data']) ? [] : $result['data']['data'];
                foreach ($data_list as $data) {
                    $model = [
                        'big_ext1' => empty($data['ext1']) ? "" : $data['ext1'],  //推客生成推广链接时传入的扩展字段，订单维度（需要联系运营开放白名单才能拿到数据）
                        'finishTime' => empty($data['finishTime']) ? "" : $data['finishTime'],
                        'orderEmt' => empty($data['orderEmt']) ? "" : $data['orderEmt'], // 下单设备(1:PC,2:无线)
                        'orderId' => empty($data['orderId']) ? "" : $data['orderId'], // 订单ID
                        'orderTime' => empty($data['orderTime']) ? "" : $data['orderTime'], // 下单时间(时间戳，毫秒)
                        'parentId' => empty($data['parentId']) ? "" : $data['parentId'], // 父单的订单ID，仅当发生订单拆分时返回， 0：未拆分，有值则表示此订单为子订单
                        'big_payMonth' => empty($data['payMonth']) ? "" : $data['payMonth'], // 订单维度预估结算时间（格式：yyyyMMdd），0：未结算，订单的预估结算时间仅供参考。账号未通过资质审核或订单发生售后，会影响订单实际结算时间。
                        'plus' => empty($data['plus']) ? "" : $data['plus'], // 下单用户是否为PLUS会员 0：否，1：是
                        'big_popId' => empty($data['popId']) ? "" : $data['popId'], // 商家ID
                        'big_validCode' => empty($data['validCode']) ? "" : $data['validCode'], //订单维度的有效码 ---------------
                    ];
                    foreach ($data['skuList'] as $sku) {
                        $model['app_id'] = empty($sku['positionId']) ? 0 : $sku['positionId']; // 推广位ID,0代表无推广位
                        $model['unionId'] = empty($sku['unionId']) ? "" : $sku['unionId']; // 推客的联盟ID
                        $model['actualCosPrice'] = empty($sku['actualCosPrice']) ? 0 : $sku['actualCosPrice']; //实际计算佣金的金额。
                        $model['actualFee'] = empty($sku['actualFee']) ? 0 : $sku['actualFee']; // 推客获得的实际佣金（实际计佣金额*佣金比例*最终比例）。如订单完成后发生退款，此金额会更新。
                        $model['cid1'] = empty($sku['cid1']) ? "" : $sku['cid1']; // 一级类目ID
                        $model['cid2'] = empty($sku['cid2']) ? "" : $sku['cid2']; // 二级类目ID
                        $model['cid3'] = empty($sku['cid3']) ? "" : $sku['cid3']; // 三级类目ID
                        $model['commissionRate'] = empty($sku['commissionRate']) ? 0 : $sku['commissionRate']; // 佣金比例
                        $model['estimateCosPrice'] = empty($sku['estimateCosPrice']) ? 0 : $sku['estimateCosPrice']; // 预估计佣金额，即用户下单的金额(已扣除优惠券、白条、支付优惠、进口税，未扣除红包和京豆)，有时会误扣除运费券金额，完成结算时会在实际计佣金额中更正。如订单完成前发生退款，此金额不会更新。
                        $model['estimateFee'] = empty($sku['estimateFee']) ? 0 : $sku['estimateFee']; // 推客的预估佣金（预估计佣金额*佣金比例*最终比例），如订单完成前发生退款，此金额不会更新。
                        $model['ext1'] = empty($sku['ext1']) ? "" : $sku['ext1'];
                        $model['finalRate'] = empty($sku['finalRate']) ? 0 : $sku['finalRate']; // 最终比例（分成比例+补贴比例）
                        $model['frozenSkuNum'] = empty($sku['frozenSkuNum']) ? "" : $sku['frozenSkuNum']; // 商品售后中数量
                        $model['payMonth'] = empty($sku['payMonth']) ? "" : $sku['payMonth']; // 订单行维度预估结算时间
                        $model['pid'] = empty($sku['pid']) ? "" : $sku['pid']; // 联盟子站长身份标识，格式：子站长ID_子站长网站ID_子站长推广位ID
                        $model['popId'] = empty($sku['popId']) ? "" : $sku['popId']; // 商家ID，订单行维度
                        $model['positionId'] = empty($sku['positionId']) ? 0 : $sku['positionId']; //推广位ID,0代表无推广位
                        $model['price'] = empty($sku['price']) ? "" : $sku['price']; // 商品单价
                        $model['siteId'] = empty($sku['siteId']) ? 0 : $sku['siteId']; // 网站ID，0：无网站
                        $model['skuId'] = empty($sku['skuId']) ? "" : $sku['skuId']; // 商品ID
                        $model['skuName'] = empty($sku['skuName']) ? "" : $sku['skuName']; // 商品名称
                        $model['skuNum'] = empty($sku['skuNum']) ? 0 : $sku['skuNum']; // 商品数量
                        $model['skuReturnNum'] = empty($sku['skuReturnNum']) ? 0 : $sku['skuReturnNum']; //商品已退货数量
                        $model['subSideRate'] = empty($sku['subSideRate']) ? "" : $sku['subSideRate']; // 分成比例
                        $model['subUnionId'] = empty($sku['subUnionId']) ? "" : $sku['subUnionId']; // 子联盟ID
                        $model['subsidyRate'] = empty($sku['subsidyRate']) ? "" : $sku['subsidyRate']; // 补贴比例
                        $model['traceType'] = empty($sku['traceType']) ? "" : $sku['traceType']; // 2：同店；3：跨店
                        $model['unionAlias'] = empty($sku['unionAlias']) ? "" : $sku['unionAlias']; // PID所属母账号平台名称（原第三方服务商来源）
                        $model['unionTag'] = empty($sku['unionTag']) ? "" : $sku['unionTag']; // 联盟标签数据
                        $model['unionTrafficGroup'] = empty($sku['unionTrafficGroup']) ? "" : $sku['unionTrafficGroup']; // 渠道组 1：1号店，其他：京东
                        $model['validCode'] = empty($sku['validCode']) ? "" : $sku['validCode']; // sku维度的有效码
                        $model['cpActId'] = empty($sku['cpActId']) ? "" : $sku['cpActId']; // 招商团活动id，正整数，为0时表示无活动
                        $result_array[] = $model;
                    }
                }
                if (@$result['data']['hasMore'] == true) {
                    $page++;
                } else {
                    break;
                }
            } else {
                $this->info("error_info: message:" . $result['message'] . " code:" . $result['status_code']);
                break;
            }

        }
        return $result_array;

    }
}
