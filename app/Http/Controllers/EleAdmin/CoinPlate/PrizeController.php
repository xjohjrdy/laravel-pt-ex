<?php


namespace App\Http\Controllers\EleAdmin\CoinPlate;


use App\Entitys\App\CoinTaskConfig;
use App\Entitys\App\CoinTurntable;
use App\Entitys\App\CoinTurntableGetLog;
use App\Entitys\App\CoinTurntablePrize;
use App\Entitys\App\HomeTopCategoryChild;
use App\Http\Controllers\Controller;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrizeController extends Controller
{
    private $model ;
    public function __construct()
    {
        $this->model =  new CoinTurntablePrize();
    }

    //
    public function getList(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['turntable_id'];
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$item] = $params[$item];
                }
            }
            $list = $this->model->where($wheres)->paginate($limit);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    public function getUserLogList(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['app_id', 'turntable_id', 'award_id'];
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$item] = $params[$item];
                }
            }
            if(!empty($wheres['turntable_id'])){
                $wheres['lc_coin_turntable_get_log.turntable_id'] = $wheres['turntable_id'];
                unset($wheres['turntable_id']);
            }
            $model = new CoinTurntableGetLog();
            $list = $model->leftJoin('lc_coin_turntable', 'lc_coin_turntable_get_log.turntable_id', '=', 'lc_coin_turntable.id')
                ->leftJoin('lc_coin_turntable_prize', 'award_id', '=', 'lc_coin_turntable_prize.id')->where($wheres)->orderByDesc('created_at')->paginate($limit,
                    [
                        'lc_coin_turntable.title as turntable_tile',
                        'lc_coin_turntable_prize.title as prize_title',
                        'lc_coin_turntable_prize.type as type',
                        'lc_coin_turntable_prize.luck_draw_get as luck_draw_get',
                        'lc_coin_turntable_prize.win_probability as win_probability',
                        'lc_coin_turntable_prize.img as img',
                        'app_id',
                        'lc_coin_turntable_get_log.created_at'
                    ]);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    public function getPriceCategory(Request $request)
    {
        try {
            $sql = "select c1.title,c2.turntable_id,GROUP_CONCAT(CONCAT(c2.id,'@@',c2.title)) as prizes from lc_coin_turntable c1,lc_coin_turntable_prize c2
                    where c1.id = c2.turntable_id
                    GROUP BY turntable_id";
            $list = DB::connection('_app38')->select($sql);
            collect($list)->map(function($item, $key){
                if(empty($item->prizes)){
                    $item->prizes = [];
                }else {
                    $prizes = explode(',',$item->prizes);
                    foreach ($prizes as $key=>$prize){
                        $prizes[$key] = explode('@@', $prize);
                    }
                    $item->prizes = $prizes;
                }
                return $item;
            });
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /**
     * 删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $audit_info = $this->model->where(['id' => $params['id']])->first();
            if(empty($audit_info)){
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $this->model->where(['id' => $params['id']])->delete();
            }
            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }


    /**
     * 新增或更新
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operate(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
            ];
            unset($params['s']);
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            if(empty($params['id'])){
                $this->model->create($params);
            } else {
                $audit_info = $this->model->where(['id' => $params['id']])->first();
                if(empty($audit_info)){
                    return $this->getInfoResponse(2000, '为查找到该记录');
                } else {
                    $this->model->where(['id' => $params['id']])->update($params);
                }
            }

            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }
}