<?php

namespace App\Http\Controllers\Growth;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\GrowthUser;
use App\Entitys\App\GrowthUserValue;
use App\Entitys\App\GrowthUserValueChange;
use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\HtmlJumpUser;
use App\Entitys\App\JdMaidOld;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\TaobaoMaidOld;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    //
    /**
     * 首页一进去拉出东西
     */
    public function getList(Request $request, GrowthUserValue $growthUserValue, AppUserInfo $appUserInfo, GrowthUser $growthUser, RechargeOrder $rechargeOrder, AdUserInfo $adUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $now_growth = $growthUserValue->where(['app_id' => $arrRequest['app_id']])->value('growth');
            if (empty($now_growth)) {
                $now_growth = 0;
            }
            $user = $appUserInfo->getUserInfo($arrRequest['app_id']);
            $uid = $adUserInfo->appToAdUserId($arrRequest['app_id']);
//            if (!empty($uid->groupid)) {
//                if ($uid->groupid <> 10) {
//                    $now_growth = 100;
//                }
//            }
            if (empty($user)) {
                return $this->getInfoResponse('4004', '无对应用户！');
            }
            $active_value = $user->history_active_value;
            $growth_user = $growthUser->getUser($arrRequest['app_id']);

            if (empty($growth_user)) {
                if (empty($uid)) {
                    return $this->getInfoResponse('4114', '无对应用户！');
                }
                $get_time = $rechargeOrder
                    ->where(['uid' => $uid->uid, 'status' => 2])
                    ->where('price', '>', '550')->first();
                if (empty($get_time)) {
                    $agent_time = 0;
                } else {
                    $agent_time = date('Y-m-d H:i:s', $get_time->submitdate);
                }
            } else {
                $agent_time = date('Y-m-d H:i:s', $growth_user->agent_time);
            }
            $all_active_value = 97.5;
            $x_active_value = ($all_active_value - $active_value) < 0 ? 0 : round(($all_active_value - $active_value), 2);

            return $this->getResponse([
                'growth_value' => [
                    'now' => $now_growth,
                    'all' => '100',
                ],
                'active_value' => $active_value,
                'get_growth_value' => 100,
                'all_active_value' => 97.5,
                'x_active_value' => $x_active_value,
                'push_time' => $agent_time,
                'down_img' => 'https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/staticHtml/pages/up-equity-vip.html',
                'is_show' => '1',
                'groupid' => $uid->groupid,
                'level' => $user->level,
            ]);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /**
     * 临时增加：路径点击器
     * 记录H5某个位置点击次数
     */
    public function jump(Request $request, HtmlJumpUser $htmlJumpUser)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'info_one' => 'required',
                'info_two' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $htmlJumpUser->addInfo(
                [
                    'info_one' => $arrRequest['info_one'],
                    'info_two' => $arrRequest['info_two'],
                ]
            );

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

        return $this->getResponse('1');
    }

    /**
     * 成长值记录
     */
    public function getValueChange(Request $request, GrowthUserValueChange $growthUserValueChange)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $value = $growthUserValueChange->where([
                'app_id' => $arrRequest['app_id'],
            ])->paginate(10);

            return $this->getResponse($value);

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }

    }

    /**
     * 获取预估待结算成长值
     */
    public function getPastValue(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //获取成长值比例
            $obj_growth_user_value_Config = new GrowthUserValueConfig();
            $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');

            //得到本月的时间
            $this_month = date('Y-m-01 00:00:00');

            //取到本月 淘宝报销的金额
            $obj_taobao_maid_old = new TaobaoMaidOld();
            $tb_sum_data_maid_old = $obj_taobao_maid_old->where(['type' => 2, 'app_id' => $arrRequest['app_id']])->where('created_at', '>=', $this_month)->sum('maid_money');

            //取到本月 拼多多报销的金额
            $obj_pdd_maid_old = new PddMaidOld();
            $pdd_sum_data_maid_old = $obj_pdd_maid_old->where(['type' => 2, 'app_id' => $arrRequest['app_id']])->where('created_at', '>=', $this_month)->sum('maid_money');

            //取到本月 京东报销的金额
            $obj_jd_maid_old = new JdMaidOld();
            $jd_sum_data_maid_old = $obj_jd_maid_old->where(['type' => 2, 'app_id' => $arrRequest['app_id']])->where('created_at', '>=', $this_month)->sum('maid_money');

            //计算预估值
            $tb_value = round($tb_sum_data_maid_old / $num_growth_value, 2);
            $pdd_value = round($pdd_sum_data_maid_old / $num_growth_value, 2);
            $jd_value = round($jd_sum_data_maid_old / $num_growth_value, 2);

            return $this->getResponse([
                [
                    'title' => '淘宝预估成长值',
                    'value' => (string)$tb_value,
                ],
                [
                    'title' => '拼多多预估成长值',
                    'value' => (string)$pdd_value,
                ],
                [
                    'title' => '京东预估成长值',
                    'value' => (string)$jd_value,
                ],
                [
                    'title' => '爆款预估成长值',
                    'value' => '0',
                ],
            ]);

        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 获取用户下级vip用户
     */
    public function getUserNext(Request $request)
    {
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $adUserModel = new AdUserInfo();
            $growthUserModel = new GrowthUser();
            $child_users = $adUserModel->where(['pt_pid' => $arrRequest['app_id']])
                ->where(function ($query) {
                    $query->where('groupid', 23)
                        ->orWhere(function ($query) {
                            $query->where('groupid', 24);
                        });
                })->paginate(10)->toArray();
            $appUserModel = new AppUserInfo();
            $res_users = [];
            foreach ($child_users['data'] as $key => $item) {
                $growth_user = $growthUserModel->where(['app_id' => $item['pt_id']])->first();
                $app_user = $appUserModel->getUserById($item['pt_id']);
                $date = '暂无时间';
                if (!empty($growth_user['agent_time'])) {
                    $date = date('Y-m-d h:m:s', $growth_user['agent_time']);
                }
                $res_users[$key]['id'] = $item['pt_id'];
                $res_users[$key]['v1_date'] = $date;
                $res_users[$key]['phone'] = $app_user['phone'];
                $res_users[$key]['avatar'] = $app_user['avatar'];
                $res_users[$key]['group_id'] = $item['groupid'];
            }
            return $this->getResponse([
                'data' => $res_users,
                'lastPage' => $child_users['last_page'],
                'currentPage' => $child_users['current_page'],
            ]);

        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

}
