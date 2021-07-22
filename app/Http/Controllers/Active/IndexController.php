<?php

namespace App\Http\Controllers\Active;

use Illuminate\Http\Request;
use App\Http\Controllers\Common\UserAuthContoller;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\ApiException;
use App\Services\PutaoRealActive\PutaoRealActive;
use Illuminate\Support\Facades\Redis;
use App\Entitys\App\ActiveRealCount;

/**
 * 实时活跃值业务控制器
 */
class IndexController extends UserAuthContoller
{
    private $last_month_firstdate;//上月第一天
    private $last_month_lastdate;//上月最后一天
    private $curr_month_firstdate;//本月第一天
    private $curr_month_currdate;//今天

    public function __construct()
    {
        $this->last_month_firstdate = date('Y-m-01', mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')));
        $this->last_month_lastdate = date('Y-m-t', mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')));
        $this->curr_month_firstdate = date('Y-m-01');
        $this->curr_month_currdate = date('Y-m-d');
    }

    /**
     * 实时活跃值模块主入口
     * @return \Illuminate\Http\JsonResponse
     */
    public function main(Request $request)
    {
        $user_id = $this->getUserId($request);

        $data = [];
        $data['real_active_module'] = 0;//模块开关        
        $data['prev_active_value'] = '0.00';//上月
        $data['prev_min_date'] = '0000-00-00';//上月查询日期最小范围
        $data['prev_max_date'] = '0000-00-00';//上月查询日期最大范围
        $data['curr_active_value'] = '0.00';//本月
        $data['curr_min_data'] = '0000-00-00';//本月查询日期最小范围
        $data['curr_max_data'] = '0000-00-00';//本月查询日期最大范围

        //临时调试控制，未来可移除或根据条件按需开放
        $module_test_users = Redis::get('real_active_module_users');
        if (!empty($module_test_users) && strpos(',' . $module_test_users . ',', ',' . $user_id . ',') !== FALSE) {

            $data['real_active_module'] = 1;

            $data['prev_active_value'] = PutaoRealActive::lastMonthActive($user_id);
            $data['prev_min_date'] = $this->last_month_firstdate;
            $data['prev_max_date'] = $this->last_month_lastdate;

            $data['curr_active_value'] = PutaoRealActive::currMonthActive($user_id);
            $data['curr_min_data'] = $this->curr_month_firstdate;
            $data['curr_max_data'] = $this->curr_month_currdate;
        }

        return $this->getResponse($data);
    }

    /**
     * 按日查询活跃值统计
     * @throws ApiException
     */
    public function show(Request $request)
    {
        $user_id = $this->getUserId($request);

        //查询范围：上个月第一天至今
        $rules = [
            'day' => "required|date|before:tomorrow|after_or_equal:{$this->last_month_firstdate}",
        ];

        $validator = Validator::make($this->params, $rules);
        if ($validator->fails()) {
            throw new ApiException('查询日期参数不合法');
        }

        $day = $this->params['day'];

        $data = PutaoRealActive::getUserActivePointByDay($user_id, $day);

        //当日活跃值分类统计
        $list = [];
        $list[] = ['type' => '签到', 'value' => $data[1]];
        $list[] = ['type' => '100成长值', 'value' => $data[2]];
        $list[] = ['type' => '通讯', 'value' => $data[3]];
        $list[] = ['type' => '报销', 'value' => $data[4]];
        $list[] = ['type' => '新人', 'value' => $data[5]];
        $list[] = ['type' => '商城', 'value' => $data[6]];
        $list[] = ['type' => '圈子', 'value' => $data[7]];
        $list[] = ['type' => '附加', 'value' => $data[8]];

        $sum = array_sum($data);
        $sum = empty($sum) ? '0.00' : bcadd($sum, 0, 2);

        $res = [
            'list' => $list,
            'sum' => ['type' => '当日活跃值', 'value' => $sum],//当日合计
            'click' => 8,//隐藏点击控制，查询明细
        ];

        return $this->getResponse($res);
    }

    /**
     * 按日查询活跃值日志明细
     */
    public function showDetail(Request $request)
    {
        $user_id = $this->getUserId($request);

        //查询范围 上个月第一天 -> 至今
        $rules = [
            'day' => "required|date|before:tomorrow|after_or_equal:{$this->last_month_firstdate}",
            'type' => "required|numeric|min:1|max:8",
        ];

        $validator = Validator::make($this->params, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $activeRealCount = new ActiveRealCount();
        $day = $this->params['day'];
        $type = $this->params['type'];
        $res = $activeRealCount->where('dayline', $day)->where('type', $type)->where('user_id', $user_id)->get();

        $ret = [];
        foreach ($res as $obj) {
            $data = [];
            $data['time'] = date('Y-m-d H:i', strtotime($obj->created_at));

            switch ($obj->type) {
                case PutaoRealActive::EVENT_SIGN:
                    if ($obj->user_id == $obj->param) {
                        $data['event'] = '本人签到';
                    } else {
                        $data['event'] = '直推用户签到';
                    }
                    break;
                case PutaoRealActive::EVENT_ADD:
                    $data['event'] = '附加分 +' . $obj->param1;
                    break;
                case PutaoRealActive::EVENT_CASHBACK:
                    $data['event'] = '本人或直推用户增加报销' . $obj->param1 . '元';
                    break;
                case PutaoRealActive::EVENT_CIRCLE:
                    $data['event'] = '本人或直推用户首次购买圈子奖励2分';
                    break;
                case PutaoRealActive::EVENT_SHOP:
                    $data['event'] = '本人或直推用户购买我的包括商城购物' . $obj->param1 . '元';
                    break;
                case PutaoRealActive::EVENT_FANS:
                    $data['event'] = '直推用户奖励1分';
                    break;
                case PutaoRealActive::EVENT_VIP:
                    $data['event'] = '本人或直推用户成为超级用户奖励2分';
                    break;
                case PutaoRealActive::EVENT_VOIP:
                    $data['event'] = '本人或直推用户购买我的通讯花费1笔奖励1分';
                    break;
            }

            $ret[] = $data;
        }

        return $this->getResponse($ret);
    }
}