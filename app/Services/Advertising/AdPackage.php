<?php


namespace App\Services\Advertising;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AdNumberSale;
use App\Entitys\App\ArticleOrders;
use App\Entitys\Article\Agent;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;

class AdPackage
{
    /**
     * 微信支付回调更新订单状态
     */
    public function updateOrderPayStatusByWechat($order_id, $app_id, $num){
        try{
            DB::beginTransaction();
            $md_article_order = new ArticleOrders();
            $md_article_order->upOrder($order_id, [
                'pay_status' => 1,
            ]);
            $this->handleArticle($app_id, $num);
            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 葡萄币支付更新订单状态
     */
    public function updateOrderPayStatusByPtb($order_id, $app_id, $num, $price){
        try{
            DB::beginTransaction();
            $md_article_order = new ArticleOrders();
            $md_article_order->upOrder($order_id, [
                'pay_status' => 1,
            ]);
            $this->takePtb($app_id, $price);
            $this->handleArticle($app_id, $num);
            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 增加文章
     * @param $app_id
     * @param $count 文章数量
     */
    public function handleArticle($app_id, $count)
    {
        $obj_agent = new Agent();
        $obj_ad_info = new AdUserInfo();
        $res = $obj_agent->where('pt_id', $app_id)->first();
        $user_data = $obj_ad_info->where('pt_id', $app_id)->first();
        if ($res) {
            $res->number += $count;
            $res->update_time = time();
            $res->save();
        } else {
            $obj_agent->username = $user_data->pt_username;
            $obj_agent->pt_id = $user_data->pt_id;
            $obj_agent->uid = $user_data->uid;
            $obj_agent->update_time = time();
            $obj_agent->number = $count;
            $obj_agent->forever = 0;
            $obj_agent->save();
        }

    }
    /**
     * 通过用户 app_id 扣除相应葡萄币，并记录日志
     * $value 为葡萄币值
     * （独立方法，可直接调用）
     * @param $app_id
     * @param $value
     * @return bool
     * @throws ApiException
     */
    public function takePtb($app_id, $value)
    {
        $obj_user = new AdUserInfo();
        $obj_info = $obj_user->appToAdUserId($app_id);
        $user_uid = $obj_info->uid;
        $username = $obj_info->username;
        $obj_account = new UserAccount();
        $user_ptb = $obj_account->getUserAccount($user_uid)->extcredits4;
        $obj_account->subtractPTBMoney($value, $user_uid);
        $obj_credit_log = new UserCreditLog();
        $obj_about_log = new UserAboutLog();
        $insert_id = $obj_credit_log->addLog($user_uid, "ADP", ['extcredits4' => -$value]);
        $obj_about_log->addLog($insert_id, $user_uid, $username, $app_id, ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb - $value]);
    }
}