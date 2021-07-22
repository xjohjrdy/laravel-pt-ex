<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\CoinUser;
use App\Entitys\App\CommandConfig;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\SignLog;
use App\Entitys\App\SpecialOption;
use App\Entitys\App\StartPageIndex;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\App\TaobaoUser;
use App\Entitys\App\UserOrderTao;
use App\Entitys\App\WechatInfo;
use App\Entitys\Xin\WorkOrder;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\Common\Time;

class UserCenterController extends Controller
{
    /*
     * 获取用户中心首页展示数据
     */
    public function getInitData(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
            ];

            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $android_is_value = empty($arrRequest['android_is_value']) ? 0 : $arrRequest['android_is_value'];
            $ios_is_value = empty($arrRequest['ios_is_value']) ? 0 : $arrRequest['ios_is_value'];
            $android_is_value = $android_is_value >= 154 ? 1 : 0;
            $ios_is_value = $ios_is_value >= 445 ? 1 : 0;

            /***********************************/
            //临时改的计算值改为读取固定值 每天计算用户个人中心数据表
            $obj_start_page_index = new StartPageIndex();
            $data_obj_start_page_index = $obj_start_page_index->where('app_id', $arrRequest['app_id'])->first();

            //取脚本运行信息 时间
            $Obj_Command_config = new CommandConfig();
            $obj_data = $Obj_Command_config->where('id', 1)->first();
            if ($obj_data->status == 0) {
                $int_command_time = $obj_data->end_time;
            } else {
                $int_command_time = $obj_data->start_time;
            }
            $str_command_time = date('Y-m-d H:i:s', $int_command_time);

            $obj_user = new AppUserInfo();
            $obj_user_info = $obj_user->getUserData($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '查询不到该用户！');
            }
            $taobaoUser = new  TaobaoUser();
            $taobaoMaidOld = new  TaobaoMaidOld();
            $taobao_user = $taobaoUser->getUser($arrRequest['app_id']);
            $two_prediction_now_2 = $taobaoMaidOld->getTime($arrRequest['app_id'], 2);
            $two_prediction_now_1 = $taobaoMaidOld->getTime($arrRequest['app_id'], 1);
            $obj_user_info->taobao_one_get = $taobao_user->money;
            $obj_user_info->taobao_two_prediction_now = $two_prediction_now_2 + $two_prediction_now_1;

            $obj_sign_log = new SignLog();
            $obj_user_info->signed = $obj_sign_log->isSign($app_id);
            $obj_user_info->sign_reward_amount = 10;
            $obj_user_order = new UserOrderTao();
            $obj_timestamp = new Time();
            $int_last_month_timestamp = $obj_timestamp->getLastMonthTimestamp();
            $int_last_month_order = $obj_user_order->getLastMonthOrder($int_last_month_timestamp, $app_id);
            $obj_user_info->order_amount = round($obj_user_info->order_amount + $int_last_month_order, 2);
            $int_next_month_timestamp = $obj_timestamp->getNextMonthTimestamp();
            $obj_user_info->next_month_cash_amount = round($obj_user_order->getUserTotalNextMonthCash($app_id, $int_next_month_timestamp), 2);
            $obj_work_order = new WorkOrder();
            $obj_user_info->reply_work_order_unread = $obj_work_order->getReplyUnread($app_id);
            $int_month_timestamp = $obj_timestamp->getMonthTimestamp();
            $obj_user_info->apply_cash_log = $obj_user_order->getMonthLog($app_id, $int_month_timestamp);


            //总分红收益改为读取脚本计算固定值 + 脚本跑完时间之后的统计值
            $obj_bonus_log = new BonusLog();
//            $fol_bonus_amount = $obj_bonus_log->getAllBonus($app_id);
            $fol_bonus_amount = (float)$obj_bonus_log->where(['user_id' => $app_id])
                ->where('create_time', '>', $int_command_time)
                ->sum('bonus_amount');
            $fol_bonus_amount_command = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->bonus_log_sum;
            $fol_bonus_amount = $fol_bonus_amount + $fol_bonus_amount_command;

            //广告联盟收益改为脚本计算固定值 + 脚本跑完时间之后的统计值
            $obj_ad_model = new AdUserInfo();
            $obj_ad_money = new RechargeCreditLog();
            $int_user_uid = $obj_ad_model->getUidById($app_id);
            if (empty($int_user_uid)) {
                return $this->getInfoResponse('1003', '未找到该用户UID数据');
            }
            $obj_credit_money = $obj_ad_money->getCreditMoney($int_user_uid, $int_command_time);
            $int_credit_money = $obj_credit_money[0]->money;
            $int_credit_money_command = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->ad_maid_sum;
            $int_credit_money = $int_credit_money + $int_credit_money_command;

            //商城分佣改为脚本计算固定值 + 脚本跑完时间之后的统计值
            $obj_maid_model = new ShopOrdersMaid();
            $obj_maid_money = $obj_maid_model->countMoney($app_id, $str_command_time);
            $int_maid_money = $obj_maid_money[0]->money;
            $int_maid_money_command = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->shop_maid_sum;
            $int_maid_money = $int_maid_money + $int_maid_money_command;

            //期权收益改为脚本计算固定值 + 脚本跑完时间之后的统计值
            $obj_special_model = new SpecialOption();
//            $int_special_model = $obj_special_model->countMoney($app_id, $str_command_time);
            $int_special_model = $obj_special_model->countMoney($app_id);
//            $int_special_model_command = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->option_maid_sum;
//            $int_special_model = $int_special_model + $int_special_model_command;

            $int_user_level = $obj_user->where('id', $app_id)->value('level');
            $obj_user_info->income = [
                [
                    'title' => '总分红',
                    'value' => round($fol_bonus_amount * 1, 2),
                ], [
                    'title' => '广告联盟',
                    'value' => round($int_credit_money * 1, 2),
                ], [
                    'title' => '商城分佣',
                    'value' => round($int_maid_money * 1, 2),
                ], [
                    'title' => '期权',
                    'value' => round($int_special_model * 1, 2),
                ],
            ];
            if ($android_is_value && $int_user_level <= 2) {
                $obj_user_info->income = [
                    [
                        'title' => '广告联盟',
                        'value' => round($int_credit_money * 1, 2),
                    ], [
                        'title' => '商城分佣',
                        'value' => round($int_maid_money * 1, 2),
                    ],
                ];
            }
            if ($ios_is_value && $int_user_level <= 2) {
                $obj_user_info->income = [
                    [
                        'title' => '广告联盟',
                        'value' => round($int_credit_money * 1, 2),
                    ], [
                        'title' => '商城分佣',
                        'value' => round($int_maid_money * 1, 2),
                    ],
                ];
            }
            /*开始统计团队数据*/
            //商城订单总数改为脚本计算固定值 + 脚本跑完时间之后的统计值
            $arr_number_money = $obj_user->getThreeInFo($app_id, $str_command_time);
            $arr_number_money_number = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->shop_orders_count;
            $arr_number_money_number = $arr_number_money_number + $arr_number_money->number;

            //商城总业绩改为脚本计算固定值 + 脚本跑完时间之后的统计值
            $arr_number_money_money = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->shop_all_sum;
            $arr_number_money_money = $arr_number_money_money + $arr_number_money->money;

            //葡萄通讯总额改为脚本计算总额 + 脚本跑完时间之后的统计值
            $obj_new_voip_money = new AdUserInfo();
            $num_sum_voip_money = 0;
            $voip_money_new = $obj_new_voip_money->getNewVoipMoney($app_id, $str_command_time);
            $num_sum_voip_money += $voip_money_new;
            $num_sum_voip_money_command = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->voip_sum;
            $num_sum_voip_money = $num_sum_voip_money + $num_sum_voip_money_command;

            //团队vip数改为脚本计算值 + 脚本跑完时间之后的统计值
            $int_vip_number = $obj_new_voip_money->getVipCount($app_id);
//            $int_vip_number = empty($data_obj_start_page_index) ? 0 : $data_obj_start_page_index->team_vip_sum;

            $obj_user_info->group = [
                [
                    'title' => '商城订单数',
                    'value' => $arr_number_money_number * 1
                ], [
                    'title' => '商城总业绩',
                    'value' => round($arr_number_money_money * 1, 2)
                ], [
                    'title' => '团队超级用户数',
                    'value' => $int_vip_number * 1
                ], [
                    'title' => '新版葡萄通讯总额',
                    'value' => round($num_sum_voip_money * 1, 2)
                ],
            ];
            $obj_wechat_info = new WechatInfo();
            $is_wechat_info_value = $obj_wechat_info->where('app_id', $app_id)->first();
            if ($is_wechat_info_value) {
                $obj_user_info->is_wechat_info = 1;
            } else {
                $obj_user_info->is_wechat_info = 0;
            }
            $obj_user_info->order_can_apply_amount = round($obj_user_info->order_can_apply_amount, 2);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1004', '未得到用户该有数据！');
            }
            $obj_user_info->is_show_pull_new = 1; // is_show_pull_new, = 1 显示， =2 隐藏
            $obj_user_info->coin_number = CoinUser::where("app_id", $app_id)->value('coin') ?? 0; // 金币数量
            $obj_user_info->coin_finder = [
                'is_show' => 1,
                'url' => 'http://api.36qq.com/coin_finder/'
            ]; // is_show_pull_new, = 1 显示， =2 隐藏
            return $this->getResponse($obj_user_info);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
