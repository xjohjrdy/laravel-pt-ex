<?php

namespace App\Console\Commands;

use App\Entitys\App\JdEnterOrders;
use App\Entitys\App\JdEnterOrdersFirst;
use App\Services\JdCommodity\JdCommodityServices;
use Illuminate\Console\Command;

class SyncJdOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SyncJdOrders';

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
        $jd_enter_order = new JdEnterOrders();
        $jd_enter_order_first = new JdEnterOrdersFirst();
        $time = time();
        $minute = substr(date("YmdHis", strtotime("-2 minute", $time)), 0, 12);
        $this->info("start: " . $minute);
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
                        'big_ext1' => empty($data['ext1']) ? "" : $data['ext1'],
                        'finishTime' => empty($data['finishTime']) ? "" : $data['finishTime'],
                        'orderEmt' => empty($data['orderEmt']) ? "" : $data['orderEmt'],
                        'orderId' => empty($data['orderId']) ? "" : $data['orderId'],
                        'orderTime' => empty($data['orderTime']) ? "" : $data['orderTime'],
                        'parentId' => empty($data['parentId']) ? "" : $data['parentId'],
                        'big_payMonth' => empty($data['payMonth']) ? "" : $data['payMonth'],
                        'plus' => empty($data['plus']) ? "" : $data['plus'],
                        'big_popId' => empty($data['popId']) ? "" : $data['popId'],
                        'big_validCode' => empty($data['validCode']) ? "" : $data['validCode'],
                    ];
                    foreach ($data['skuList'] as $sku) {
                        $model['app_id'] = empty($sku['positionId']) ? 0 : $sku['positionId'];
                        $model['unionId'] = empty($sku['unionId']) ? "" : $sku['unionId'];
                        $model['actualCosPrice'] = empty($sku['actualCosPrice']) ? 0 : $sku['actualCosPrice'];
                        $model['actualFee'] = empty($sku['actualFee']) ? 0 : $sku['actualFee'];
                        $model['cid1'] = empty($sku['cid1']) ? "" : $sku['cid1'];
                        $model['cid2'] = empty($sku['cid2']) ? "" : $sku['cid2'];
                        $model['cid3'] = empty($sku['cid3']) ? "" : $sku['cid3'];
                        $model['commissionRate'] = empty($sku['commissionRate']) ? 0 : $sku['commissionRate'];
                        $model['estimateCosPrice'] = empty($sku['estimateCosPrice']) ? 0 : $sku['estimateCosPrice'];
                        $model['estimateFee'] = empty($sku['estimateFee']) ? 0 : $sku['estimateFee'];
                        $model['ext1'] = empty($sku['ext1']) ? "" : $sku['ext1'];
                        $model['finalRate'] = empty($sku['finalRate']) ? 0 : $sku['finalRate'];
                        $model['frozenSkuNum'] = empty($sku['frozenSkuNum']) ? "" : $sku['frozenSkuNum'];
                        $model['payMonth'] = empty($sku['payMonth']) ? "" : $sku['payMonth'];
                        $model['pid'] = empty($sku['pid']) ? "" : $sku['pid'];
                        $model['popId'] = empty($sku['popId']) ? "" : $sku['popId'];
                        $model['positionId'] = empty($sku['positionId']) ? 0 : $sku['positionId'];
                        $model['price'] = empty($sku['price']) ? "" : $sku['price'];
                        $model['siteId'] = empty($sku['siteId']) ? 0 : $sku['siteId'];
                        $model['skuId'] = empty($sku['skuId']) ? "" : $sku['skuId'];
                        $model['skuName'] = empty($sku['skuName']) ? "" : $sku['skuName'];
                        $model['skuNum'] = empty($sku['skuNum']) ? 0 : $sku['skuNum'];
                        $model['skuReturnNum'] = empty($sku['skuReturnNum']) ? 0 : $sku['skuReturnNum'];
                        $model['subSideRate'] = empty($sku['subSideRate']) ? "" : $sku['subSideRate'];
                        $model['subUnionId'] = empty($sku['subUnionId']) ? "" : $sku['subUnionId'];
                        $model['subsidyRate'] = empty($sku['subsidyRate']) ? "" : $sku['subsidyRate'];
                        $model['traceType'] = empty($sku['traceType']) ? "" : $sku['traceType'];
                        $model['unionAlias'] = empty($sku['unionAlias']) ? "" : $sku['unionAlias'];
                        $model['unionTag'] = empty($sku['unionTag']) ? "" : $sku['unionTag'];
                        $model['unionTrafficGroup'] = empty($sku['unionTrafficGroup']) ? "" : $sku['unionTrafficGroup'];
                        $model['validCode'] = empty($sku['validCode']) ? "" : $sku['validCode'];
                        $model['cpActId'] = empty($sku['cpActId']) ? "" : $sku['cpActId'];
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
