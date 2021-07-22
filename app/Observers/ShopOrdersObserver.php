<?php
namespace App\Observers;

use App\Entitys\App\ShopOrders;
use App\Services\PutaoRealActive\PutaoRealActive;

class ShopOrdersObserver
{
    /**
     * 监听更新事件
     * @param ShopOrders $shopOrders
     * @return void
     */
    public function updated(ShopOrders $shopOrders)
    {
        try {
            $created_at = $shopOrders->getAttribute('created_at');
            $updated_at = $shopOrders->getAttribute('updated_at');
            
            //正常流程，确认收货
            if ( $shopOrders->isDirty('status') && $shopOrders->status == 3 ) {
                
                $id = $shopOrders->id;
                $app_id = $shopOrders->app_id;
                $price = $shopOrders->price;
                
                
                //排除VIP商品
                if ( $price != 800 ) {
                    //活跃值：6.我的爆款商城购买金额
                    PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_SHOP, $id, $price, 0, $created_at);
                }
            }
            
            //确认收货后，申请退款，目前设计仅支持对冲一次，确保扣减。【如果出现退款申请取消又需要恢复，量少可先人工处理】
            if ( $shopOrders->isDirty('status') && $shopOrders->getOriginal('status') == 3 && $shopOrders->status == 4 ) {
                
                $id = $shopOrders->id;
                $app_id = $shopOrders->app_id;
                $price = $shopOrders->price;
                
                //排除VIP商品
                if ( $price != 800 ) {
                    //活跃值：6.我的爆款商城购买金额
                    PutaoRealActive::eventListen( $app_id, PutaoRealActive::EVENT_SHOP, -$id, -$price, 0, $created_at, null, $updated_at);
                }
            }
        } catch( \Exception $e ) {
            //可log
        }
    }
}