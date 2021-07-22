<?php


namespace App\Services\Xin;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\PretendShopOrdersMaid;
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\SpecialOption;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\Xin\WorkOrder;
use Illuminate\Support\Facades\DB;

class CalUserData
{


    private $app_id = '';
    private $appUserModel = null;
    private $adUserModel = null;
    private $st_time = null;
    private $end_time = null;
    private $st_timestamp = null;
    private $end_timestamp = null;
    private $new_column_keys = [];
    private $obj_bonus_log = null;
    private $obj_ad_money= null;
    private $obj_maid_model= null;
    private $obj_special_model = null;
    private $obj_work_order = null;
    private $taobaoMaidOld = null;
    private $ordersMaid = null;


    /**
     * CalUserData constructor.
     */
    public function __construct($st_time, $end_time, $st_timestamp, $end_timestamp)
    {
        $this->adUserModel = new AdUserInfo();
        $this->appUserModel = new AppUserInfo();
        $this->obj_bonus_log = new BonusLog();
        $this->obj_ad_money = new RechargeCreditLog();
        $this->obj_maid_model = new ShopOrdersMaid();
        $this->obj_special_model = new SpecialOption();
        $this->obj_work_order = new WorkOrder();
        $this->taobaoMaidOld = new  TaobaoMaidOld();
        $this->ordersMaid = new PretendShopOrdersMaid();
        $this->st_time = $st_time;
        $this->end_time = $end_time;
        $this->st_timestamp = $st_timestamp; // 时间戳
        $this->end_timestamp = $end_timestamp; // 时间戳

    }

//    /**
//     * 设置要计算的用户
//     * @param $app_id
//     */
//    public function setAppId($app_id){
//        $this->app_id = $app_id;
//    }

    public function setAppId($app_id){
        $this->app_id = $app_id;
    }
    /**
     * 设置脚本同步新增字段的标识。
     * 新增方法需要定义$key，并且数据库新增一个字段new_column_key，如果脚本存在该字段，并与新增字段同步的方法一致。则标识新增字段，
     * 脚本执行的起始时间需从0 开始，结束时间等一脚本配置中的 end_time。
     * 执行该方法的前提条件： 脚本配置中的page_index 分页索引 值需等 1。方可执行，否则会有部分逻辑问题。
     * 脚本全部执行成功后，默认清空该值
     * @param $key 设置的key值
     * @param $command_config 当前运行的脚本配置
     */
    public function setNewColumnKey($command_config = []){
        if($command_config['page_index'] == 1 && !empty($command_config['new_column_keys'])){
            $this->new_column_keys = explode("|", $command_config['new_column_keys']);
        }
    }

    private function checkKey($key){
        return in_array($key, $this->new_column_keys);
    }

    /**
     * 新增字段key校验用例
     */
    public function getNewColumnTest(){

        $start_time = $this->st_time;
        $start_timestamp = $this->st_timestamp;
        $end_time = $this->end_time;
        $end_timestamp = $this->end_timestamp;
        if($this->checkKey(__FUNCTION__)){ // 校验是否新增字段，重置初始时间为0，如果新增的字段为非自增字段，则无需校验
            $start_timestamp = 0;
            $start_time = date('Y-m-d H:i:s', $start_timestamp);

        }
//        dd($this->checkKey(__FUNCTION__));
        // 下面执行具体的任务

    }

    /**
     * 获取总分红收益
     */
    public function getTotalDividendIncome()
    {
//        $obj_bonus_log = new BonusLog();
        $fol_bonus_amount = (float)$this->obj_bonus_log->where(['user_id' => $this->app_id])->whereBetween('create_time', [$this->st_timestamp, $this->end_timestamp])->sum('bonus_amount');
        return round($fol_bonus_amount * 1, 2);
    }

    /**
     * 获取广告联盟收益
     */
    public function getAdIncome()
    {
        $int_user_uid = $this->adUserModel->getUidById($this->app_id);
        if (empty($int_user_uid)) {
            return 0;
        }
        $obj_credit_money = $this->obj_ad_money->where(['uid' => $int_user_uid])->whereBetween('dateline', [$this->st_timestamp, $this->end_timestamp])->sum('money');
//        $obj_credit_money = DB::connection('a1191125678')
//            ->select("SELECT SUM(money/10 ) as money FROM pre_aljbgp_credit_log WHERE uid = {$int_user_uid}");
        $int_credit_money = $obj_credit_money / 10;
        return round($int_credit_money * 1, 2);
    }

    /**
     * 获取商城分佣收益
     */
    public function getShopMaidIncome()
    {
//        $res_data = DB::connection('app38')
//          ->select("SELECT SUM(money/10 ) as money FROM lc_shop_orders_maid WHERE app_id = {$app_id}");
        $obj_maid_money = $this->obj_maid_model->where(['app_id' => $this->app_id])->whereBetween('created_at', ["{$this->st_time}", "{$this->end_time}"])->sum('money');
        $int_maid_money = $obj_maid_money / 10;
        return round($int_maid_money * 1, 2);
    }

    /**
     * 获取期权收益
     */
    public function getOptionIncome()
    {
        $int_special_money = $this->obj_special_model
            ->where(['app_id' => $this->app_id])->whereBetween('created_at', ["{$this->st_time}", "{$this->end_time}"])->sum('option_value');
        return round($int_special_money * 1, 2);
    }


    /**
     * 获取商城订单数和总业绩
     * $arr_number_money->number 订单数
     * $arr_number_money->money 总业绩
     */
    public function getShopOrderCount()
    {
        $res_data = DB::connection('app38')
            ->select("
            SELECT SUM(tt1.price) as money,COUNT(*) as number
                FROM lc_shop_orders as tt1
                INNER JOIN (
                        SELECT 
                                *
                        FROM
                                lc_user
                        WHERE
                                id = {$this->app_id}
                        UNION
                        SELECT
                                t2.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id 
                        WHERE
                                t1.id = {$this->app_id}
                        UNION
                        SELECT
                                t3.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                INNER JOIN lc_user t3 ON t2.id = t3.parent_id 
                        WHERE
                                t1.id = {$this->app_id}
                        UNION
                        SELECT
                                t4.* 
                        FROM
                                lc_user t1
                                INNER JOIN lc_user t2 ON t1.id = t2.parent_id
                                INNER JOIN lc_user t3 ON t2.id = t3.parent_id
                                INNER JOIN lc_user t4 ON t3.id = t4.parent_id 
                        WHERE
                                t1.id = {$this->app_id}  
                ) as tt2
                on tt1.app_id = tt2.id 
                WHERE tt1.status = 3
                AND tt1.created_at BETWEEN '{$this->st_time}' AND '{$this->end_time}'
                AND tt2.status = 1
            ");
        if (empty($res_data[0])) {
            return ['money' => 0, 'number' => 0];
        } else {
            return ['money' => $res_data[0]->money, 'number' => $res_data[0]->number];
        }

    }

    /**
     * 获取新版葡萄通讯总额
     * $int_vip_number  获取团队VIP数
     * $num_sum_voip_money 新版葡萄通讯总额
     */
    public function getMobilePassMoney()
    {
        $num_sum_voip_money = 0;
        $res_data = DB::connection('a1191125678')
            ->select("
        SELECT SUM(tt1.real_price) as money
        FROM pre_voip_money_order as tt1
        INNER JOIN (
        SELECT 
                                        *
        FROM
                                        pre_common_member
        WHERE
                                        pt_id ={$this->app_id}
        UNION
        SELECT
                                        t2.* 
        FROM
                                        pre_common_member t1
                                        INNER JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid 
        WHERE
                                        t1.pt_id ={$this->app_id}
        UNION
        SELECT
                                        t3.* 
        FROM
                                        pre_common_member t1
                                        INNER JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                                        INNER JOIN pre_common_member t3 ON t2.pt_id = t3.pt_pid 
        WHERE
                                        t1.pt_id ={$this->app_id}
        UNION
        SELECT
                                        t4.* 
        FROM
                                        pre_common_member t1
                                        INNER JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                                        INNER JOIN pre_common_member t3 ON t2.pt_id = t3.pt_pid
                                        INNER JOIN pre_common_member t4 ON t3.pt_id = t4.pt_pid 
        WHERE
                                        t1.pt_id ={$this->app_id}
        ) as tt2  
        on tt1.app_id = tt2.pt_id 
        WHERE tt1.status = 1
        AND tt1.created_at BETWEEN '{$this->st_time}' AND '{$this->end_time}'
        ");

        if (empty($res_data[0]->money)) {
            $num_sum_voip_money = 0;
        } else {
            $num_sum_voip_money = $res_data[0]->money;
        }
        return $num_sum_voip_money;
//        $voip_money_new = $this->adUserModel->getNewVoipMoney($this->app_id);


    }


    /**
     * 获取团队VIP数
     * pre_common_member 该表无created_at 时间字段 非自增字段
     */
    public function getTeamVipCount()
    {
        $res_data2 = DB::connection('a1191125678')
            ->select("
                SELECT
                    ((
                    SELECT
                        count(*)
                    FROM
                        pre_common_member t1
                        RIGHT JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                        RIGHT JOIN pre_common_member t3 ON t2.pt_id = t3.pt_pid
                        RIGHT JOIN pre_common_member t4 ON t3.pt_id = t4.pt_pid
                    WHERE
                        t1.pt_id = {$this->app_id}
                    AND 
                        t4.groupid in (23,24)
                    )+(
                    SELECT
                        count(*)
                    FROM
                        pre_common_member t1
                        RIGHT JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                        RIGHT JOIN pre_common_member t3 ON t2.pt_id = t3.pt_pid
                    WHERE
                        t1.pt_id = {$this->app_id}
                    AND
                      t3.groupid in (23,24)
                    )+(
                    SELECT
                        count(*)
                    FROM
                        pre_common_member t1
                        RIGHT JOIN pre_common_member t2 ON t1.pt_id = t2.pt_pid
                    WHERE
                        t1.pt_id = {$this->app_id}
                    AND 
                            t2.groupid in (23,24)
                        )
                    ) as ct
            ");
        if (empty($res_data2[0]->ct)) {
            $int_vip_number = 0;
        } else {
            $int_vip_number = $res_data2[0]->ct;
        }
        return $int_vip_number;
//        $int_vip_number = $this->adUserModel->getVipCount($this->app_id);
    }

    /**
     * 获取已回复工单数量
     */
    public function getReplyWorkCount()
    {
        $reply_work_order_unread = $this->obj_work_order->where(['user_id' => $this->app_id, 'status' => 2, 'read_status' => 1])->whereBetween('create_time', [$this->st_timestamp, $this->end_timestamp])->count('id'); // 已回复工单数量
//        $reply_work_order_unread = $obj_work_order->getReplyUnread($this->app_id); // 已回复工单数量
        return $reply_work_order_unread;
    }

    /**
     * 获取本月预估收入 非自增字段，直接更新
     */
    public function getCurrentPreIncome()
    {
        $two_prediction_now_2 = $this->taobaoMaidOld->getTime($this->app_id, 2);
        $two_prediction_now_1 = $this->taobaoMaidOld->getTime($this->app_id, 1);
        $taobao_two_prediction_now = $two_prediction_now_2 + $two_prediction_now_1;
        return $taobao_two_prediction_now;
    }


    /**
     * 获取用户所有分佣收入
     */
    public function getUserAllDivideIncome()
    {
//        $accountPtb = $shopOrdersMaid->getAllCreditLog($this->app_id)->sum('money');
        $accountPtb = $this->obj_maid_model->where(['app_id' => $this->app_id])->whereBetween('created_at', ["{$this->st_time}", "{$this->end_time}"])->sum('money');
        return $accountPtb / 10; // 葡萄币 转 元
    }

    /**
     * 获取用户分佣全部预估收入
     */
    public function getUserAllPreIncome()
    {
//        $allPtb = $ordersMaid->getCountMoney($arrRequest['app_id']);
        $where['app_id'] = $this->app_id;
        $where['status'] = 0;
        $countMoney = $this->ordersMaid->where($where)->whereBetween('created_at', ["{$this->st_time}", "{$this->end_time}"])->sum('money'); // 葡萄比
        $allPtb = $countMoney / 10;
        return $allPtb;
    }
}