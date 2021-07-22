<?php

namespace App\Http\Controllers\Other;

use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\Other\JdMaidOldOther;
use App\Entitys\Other\ManagerMaidAutoList;
use App\Entitys\Other\ManagerPretendMaid;
use App\Entitys\Other\MtMaidOldOther;
use App\Entitys\Other\PddMaidOldOther;
use App\Entitys\Other\TaobaoMaidOldOther;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeCircleMaid;
use App\Entitys\Other\ThreeEleMaidOld;
use App\Entitys\Other\ThreeUser;
use App\Entitys\Other\UserThreeUpMaid;
use App\Exceptions\ApiException;
use App\Services\Other\Wu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Entitys\Other\CardMaid as CardMaidOther;

class WuController extends Controller
{
    //

    /**
     * 新增接口
     */
    public function getIn(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $manage_pretend_maid = new ManagerPretendMaid();

            $t = time();
            $today_time_start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
            $today_time_end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
            $date = date("Y-m-d");
            $month_time_start = date('Y-m-01 00:00:00', strtotime('-1 month'));
            $month_time_end = date("Y-m-d 23:59:59", strtotime(-date('d') . 'day'));
            $next_month_time_start = date('Y-m-01', strtotime($date));
            $next_month_time_end = date('Y-m-01 00:00:00', strtotime('+1 month'));

//            var_dump($today_time_start);
//            var_dump($today_time_end);
//            var_dump($month_time_start);
//            var_dump($month_time_end);
//            var_dump($month_time_start);
//            var_dump($next_month_time_end);

            $all_pretend_money_today = $manage_pretend_maid
                ->where('created_at', '>', date('Y-m-d H:i:s', $today_time_start))
                ->where('created_at', '<', date('Y-m-d H:i:s', $today_time_end))
                ->where([
                    'status' => 0,
                    'app_id' => $arrRequest['app_id']
                ])->sum('money');
            $all_pretend_money_month = $manage_pretend_maid
                ->where('created_at', '>', $month_time_start)
                ->where('created_at', '<', $month_time_end)
                ->where([
                    'status' => 0,
                    'app_id' => $arrRequest['app_id']
                ])->sum('money');
            $all_pretend_money_next_month = $manage_pretend_maid
                ->where('created_at', '>', $next_month_time_start)
                ->where('created_at', '<', $next_month_time_end)
                ->where([
                    'status' => 0,
                    'app_id' => $arrRequest['app_id']
                ])->sum('money');


//            var_dump($all_pretend_money_today);
//            var_dump($all_pretend_money_month);
//            var_dump($all_pretend_money_next_month);
//
//            exit();

            return $this->getResponse([
                'all_pretend_money_today' => $all_pretend_money_today,
                'all_pretend_money_month' => $all_pretend_money_month,
                'all_pretend_money_next_month' => $all_pretend_money_next_month,
            ]);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 拉出列表
     */
    public function getList(Request $request, ManagerMaidAutoList $managerMaidAutoList)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $res = $managerMaidAutoList->where(['app_id' => $arrRequest['app_id']])->paginate(10);

            $arr = [];
            foreach ($res as $re) {
                $arr['list'][] = [
                    'from_info' => $re['from_info'] . '月份公司奖励',
                    'money' => $re['money'],
                    'status' => $re['status'],
                    'created_at' => $re['created_at'],
                ];
            }

            $arr['all'] = $managerMaidAutoList->where(['app_id' => $arrRequest['app_id'], 'status' => '1'])->sum('money');
            return $this->getResponse($arr);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 获取各项数据
     */
    public function getOut(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            ///lc_user_three_up_maid爆款
            ///
            $app_id = $arrRequest['app_id'];

            //本月开始结束
            $month_start = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
            $month_end = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("t"), date("Y")));

            //上月开始结束
            $last_month_start = date('Y-m-01 00:00:00', strtotime('-1 month'));
            $last_month_end = date("Y-m-d 23:59:59", strtotime(-date('d') . 'day'));

            //当日开始结束
            $today_start = date('Y-m-d') . ' 00:00:00';
            $today_end = date('Y-m-d') . ' 23:59:59';

            //昨日开始结束
            $yesterday_start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
            $yesterday_end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';


            $all_time_start = date('Y-m-d H:i:s', '1577808000');
            $end_time_str = date('Y-m-d H:i:s', '1759248000');

            $manage_pretend_maid = new ManagerPretendMaid();

            $userMoneyModel = new ThreeUser();
            $list = $userMoneyModel->getUserMoney($app_id);
            $wu = new Wu();
            $all_pretend_money_today = $wu->getMonthData($app_id, $month_start, $month_end);
            $all_pretend_money_today_brother = $wu->getMonthData($app_id, $last_month_start, $last_month_end);
            $all_pretend_money_now_zero = $wu->getMonthData($app_id, $today_start, $today_end);
            $all_pretend_money_month = $wu->getAllData($app_id);

            /**
             *取自旧逻辑
             */
            $all_pretend_next_month = $manage_pretend_maid
                ->where('created_at', '>', $last_month_start)
                ->where('created_at', '<', $last_month_end)
                ->where([
                    'status' => 0,
                    'app_id' => $arrRequest['app_id']
                ])->sum('money');

            $all_pretend_next_month_brother = $manage_pretend_maid
                ->where('created_at', '>', $month_start)
                ->where('created_at', '<', $month_end)
                ->where([
                    'status' => 0,
                    'app_id' => $arrRequest['app_id']
                ])->sum('money');

            $all_company_money_now_zero = $manage_pretend_maid
                ->where('created_at', '>', $today_start)
                ->where('created_at', '<', $today_end)
                ->where([
                    'status' => 0,
                    'app_id' => $arrRequest['app_id']
                ])->sum('money');

            $all_pretend_money_next_month = $manage_pretend_maid
                ->where([
                    'status' => 1,
                    'app_id' => $arrRequest['app_id']
                ])->sum('money');

            /**
             * 取自旧逻辑hotShopEstimatedIncome
             * 爆款
             */
            $userThreeUpMaid = new UserThreeUpMaid();
            //开始处理逻辑问题
            //得到当日时间
            $today = date('Y-m-d 00:00:00', time());
            //得到今月时间
            $month = date('Y-m-01 00:00:00', time());
            $num_today_data_yesterday = (string)$userThreeUpMaid->where('app_id', $app_id)
                ->where('created_at', '>', $yesterday_start)
                ->where('created_at', '<', $yesterday_end)
                ->sum('money');#昨日预估收入
            $num_today_data = (string)$userThreeUpMaid->getEstimatedMoneyByTime($app_id, $today);#当日预估收入
            $num_month_data = (string)$userThreeUpMaid->getEstimatedMoneyByTime($app_id, $month);#当月预估收入
            $num_all_data = (string)$userThreeUpMaid->getEstimatedMoneyByTime($app_id);          #累计预估收入

            $shop_company_one_today = $wu->getCompanyData($app_id, 1, $today_start, $today_end);
            $shop_company_out_today = $wu->getCompanyData($app_id, 1, $yesterday_start, $yesterday_end);
            $shop_company_one_month = $wu->getCompanyData($app_id, 1, $last_month_start, $last_month_end);
            $shop_company_out_month = $wu->getCompanyData($app_id, 1, $month_start, $month_end);

            /**
             * 淘宝
             */

            $TaobaoMaidOldOther = new TaobaoMaidOldOther();
            $taobao_month = $TaobaoMaidOldOther->getMaidMoneyForMonth($app_id, 1, 0, $month_start, $month_end);#当月预估收入
            $taobao_yesterday = $TaobaoMaidOldOther->getMaidMoneyForMonth($app_id, 1, 0, $yesterday_start, $yesterday_end);#昨日预估收入
            $taobao_today = $TaobaoMaidOldOther->getMaidMoneyForMonth($app_id, 1, 0, $today_start, $today_end);#当日预估收入
            $taobao_all = $TaobaoMaidOldOther->getMaidMoneyForMonth($app_id, 1, 0, $last_month_start, $last_month_end);#累加预估收入

            $taobao_company_one_today = $wu->getCompanyData($app_id, 2, $today_start, $today_end);
            $taobao_company_out_today = $wu->getCompanyData($app_id, 2, $yesterday_start, $yesterday_end);
            $taobao_company_one_month = $wu->getCompanyData($app_id, 2, $last_month_start, $last_month_end);
            $taobao_company_out_month = $wu->getCompanyData($app_id, 2, $month_start, $month_end);

            /**
             * 京东
             */

            $jdMaidOldModel = new JdMaidOldOther();
            $jd_month = $jdMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $month_start, $month_end);
            $jd_yesterday = $jdMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $yesterday_start, $yesterday_end);
            $jd_today = $jdMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $today_start, $today_end);
            $jd_all = $jdMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $last_month_start, $last_month_end);


            $jd_company_one_today = $wu->getCompanyData($app_id, 3, $today_start, $today_end);
            $jd_company_out_today = $wu->getCompanyData($app_id, 3, $yesterday_start, $yesterday_end);
            $jd_company_one_month = $wu->getCompanyData($app_id, 3, $last_month_start, $last_month_end);
            $jd_company_out_month = $wu->getCompanyData($app_id, 3, $month_start, $month_end);
            /**
             * 拼多多
             */


            $pddMaidOldModel = new PddMaidOldOther();
            $pdd_month = $pddMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $month_start, $month_end);
            $pdd_yesterday = $pddMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $yesterday_start, $yesterday_end);
            $pdd_today = $pddMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $today_start, $today_end);
            $pdd_all = $pddMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $last_month_start, $last_month_end);


            $pdd_company_one_today = $wu->getCompanyData($app_id, 4, $today_start, $today_end);
            $pdd_company_out_today = $wu->getCompanyData($app_id, 4, $yesterday_start, $yesterday_end);
            $pdd_company_one_month = $wu->getCompanyData($app_id, 4, $last_month_start, $last_month_end);
            $pdd_company_out_month = $wu->getCompanyData($app_id, 4, $month_start, $month_end);

            /**
             * 圈子
             */


            $circleMaidModel = new ThreeCircleMaid();
            $circle_normal_one_today = $circleMaidModel->where(['app_id' => $app_id])->whereBetween('created_at', [$today_start, $today_end])->sum('money');
            $circle_normal_out_today = $circleMaidModel->where(['app_id' => $app_id])->whereBetween('created_at', [$yesterday_start, $yesterday_end])->sum('money');
            $circle_normal_one_month = $circleMaidModel->where(['app_id' => $app_id])->whereBetween('created_at', [$month_start, $month_end])->sum('money');
            $circle_normal_out_month = $circleMaidModel->where(['app_id' => $app_id])->sum('money');

            /**
             * 饿了么
             */

            $eleMaidOldModel = new ThreeEleMaidOld();
            $ele_month = $eleMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $month_start, $month_end);
            $ele_yesterday = $eleMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $yesterday_start, $yesterday_end);
            $ele_today = $eleMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $today_start, $today_end);
            $ele_all = $eleMaidOldModel->getMaidMoneyForMonth($app_id, 1, 0, $last_month_start, $last_month_end);


            $ele_company_one_today = $wu->getCompanyData($app_id, 6, $today_start, $today_end);
            $ele_company_out_today = $wu->getCompanyData($app_id, 6, $yesterday_start, $yesterday_end);
            $ele_company_one_month = $wu->getCompanyData($app_id, 6, $last_month_start, $last_month_end);
            $ele_company_out_month = $wu->getCompanyData($app_id, 6, $month_start, $month_end);

            /**
             * 信用卡
             */

            $cardMaidModel = new CardMaidOther();
            $card_month = $cardMaidModel->getMaidMoneyForMonth($app_id, 2, $month_start, $month_end);
            $card_yesterday = $cardMaidModel->getMaidMoneyForMonth($app_id, 2, $yesterday_start, $yesterday_end);
            $card_today = $cardMaidModel->getMaidMoneyForMonth($app_id, 2, $today_start, $today_end);
            $card_all = $cardMaidModel->getMaidMoneyForMonth($app_id, 2, $all_time_start, $end_time_str);


            $card_company_one_today = $wu->getCompanyData($app_id, 7, $today_start, $today_end);
            $card_company_out_today = $wu->getCompanyData($app_id, 7, $yesterday_start, $yesterday_end);
            $card_company_one_month = $wu->getCompanyData($app_id, 7, $last_month_start, $last_month_end);
            $card_company_out_month = $wu->getCompanyData($app_id, 7, $month_start, $month_end);


            return $this->getResponse([
                'data' => $list,//以前旧接口的data:/other_user_money
                'all_pretend_money_today_one' => $all_pretend_money_today,
                'all_pretend_money_today_one_brother' => $all_pretend_money_today_brother,
                'all_pretend_money_now_zero' => $all_pretend_money_now_zero,
                'all_company_money_now_zero' => $all_company_money_now_zero,
                'all_pretend_money_month_two' => $all_pretend_money_month,
                'all_pretend_next_month_three' => $all_pretend_next_month,
                'all_pretend_next_month_three_brother' => $all_pretend_next_month_brother,
                'all_pretend_money_next_month_four' => $all_pretend_money_next_month,
                'list' => [
                    'taobao' => [
                        'normal' => [
                            'one_today' => $taobao_today,
                            'out_today' => $taobao_yesterday,
                            'one_month' => $taobao_month,
                            'out_month' => $taobao_all,
                        ],
                        'company' => [
                            'one_today' => $taobao_company_one_today,
                            'out_today' => $taobao_company_out_today,
                            'one_month' => $taobao_company_one_month,
                            'out_month' => $taobao_company_out_month,
                        ],
                    ],
                    'jd' => [
                        'normal' => [
                            'one_today' => $jd_today,
                            'out_today' => $jd_yesterday,
                            'one_month' => $jd_month,
                            'out_month' => $jd_all,
                        ],
                        'company' => [
                            'one_today' => $jd_company_one_today,
                            'out_today' => $jd_company_out_today,
                            'one_month' => $jd_company_one_month,
                            'out_month' => $jd_company_out_month,
                        ],
                    ],
                    'pdd' => [
                        'normal' => [
                            'one_today' => $pdd_today,
                            'out_today' => $pdd_yesterday,
                            'one_month' => $pdd_month,
                            'out_month' => $pdd_all,
                        ],
                        'company' => [
                            'one_today' => $pdd_company_one_today,
                            'out_today' => $pdd_company_out_today,
                            'one_month' => $pdd_company_one_month,
                            'out_month' => $pdd_company_out_month,
                        ],
                    ],
                    'shop' => [
                        'normal' => [
                            'one_today' => $num_today_data,
                            'out_today' => $num_today_data_yesterday,
                            'one_month' => $num_month_data,
                            'out_month' => $num_all_data,
                        ],
                        'company' => [
                            'one_today' => $shop_company_one_today,
                            'out_today' => $shop_company_out_today,
                            'one_month' => $shop_company_one_month,
                            'out_month' => $shop_company_out_month,
                        ],
                    ],
                    'circle' => [
                        'normal' => [
                            'one_today' => $circle_normal_one_today,
                            'out_today' => $circle_normal_out_today,
                            'one_month' => $circle_normal_one_month,
                            'out_month' => $circle_normal_out_month,
                        ],
                    ],
                    'ele' => [
                        'normal' => [
                            'one_today' => $ele_today,
                            'out_today' => $ele_yesterday,
                            'one_month' => $ele_month,
                            'out_month' => $ele_all,
                        ],
                        'company' => [
                            'one_today' => $ele_company_one_today,
                            'out_today' => $ele_company_out_today,
                            'one_month' => $ele_company_one_month,
                            'out_month' => $ele_company_out_month,
                        ],
                    ],
                    'card' => [
                        'normal' => [
                            'one_today' => $card_today,
                            'out_today' => $card_yesterday,
                            'one_month' => $card_month,
                            'out_month' => $card_all,
                        ],
                        'company' => [
                            'one_today' => $card_company_one_today,
                            'out_today' => $card_company_out_today,
                            'one_month' => $card_company_one_month,
                            'out_month' => $card_company_out_month,
                        ],
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 获取奖励记录
     */
    public function getOutList(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $start_year = date('Y');
            $dates = array();
            $wu = new Wu();
            $ManagerMaidAutoList = new ManagerMaidAutoList();
            for ($year = 2020; $year <= $start_year; $year++) {
                $yearOfDates = array();
                if ($year == $start_year) {
                    $end_month = date('m');
                } else {
                    $end_month = 12;
                }
                for ($month = 1; $month <= $end_month; $month++) {
                    $begin = strtotime($year . "-" . $month . "-01 12:00:00AM");
                    $end = strtotime($year . "-" . ($month + 1) . "-01 12:00:00AM") - 1;
                    $key = date('Y-m', $begin); // January, 2017
                    $start_str = date('Y-m-d H:i:s', $begin);
                    $end_str = date('Y-m-d H:i:s', $end);
                    $reward = $wu->getMonthEndData($arrRequest['app_id'], $start_str, $end_str);
                    $company_reward = $ManagerMaidAutoList->where(
                        [
                            'app_id' => $arrRequest['app_id'],
                        ])
                        ->where('created_at', '>=', $start_str)
                        ->where('created_at', '<=', $end_str)
                        ->first(['money', 'status']);
                    $yearOfDates[$key] = array(
//                        'start' => $begin,
//                        'end' => $end,
//                        'start_str' => $start_str,
//                        'end_str' => $end_str,
                        'reward' => $reward,
                        'company_reward' => $company_reward,
                    );
                }
                $yearOfDates = array_reverse($yearOfDates);
                $dates[$year] = $yearOfDates;
            }


            return $this->getResponse($dates);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 拉出详细信息
     */
    public function getDetail(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'type' => 'required',
                /**
                 * 1：爆款商城
                 * 2：淘报销
                 * 3：京东
                 * 4：拼多多
                 * 5：美团
                 * 6：饿了么
                 * 7：信用卡
                 * 8：圈子
                 */
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            if (empty($arrRequest['type'])) {
                $arrRequest['type'] = 1;
            }

            $manage_pretend_maid = new ManagerPretendMaid();

            $res = $manage_pretend_maid->where([
                'app_id' => $arrRequest['app_id'],
                'type' => $arrRequest['type'],
            ])->orderBy('created_at', 'desc')->paginate(20, [
                'status',
                'money',
                'created_at',
            ]);

            return $this->getResponse($res);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }
}
