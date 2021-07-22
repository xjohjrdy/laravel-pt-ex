<?php


namespace App\Http\Controllers\EleAdmin\CoinPlate;


use App\Entitys\App\CoinTaskConfig;
use App\Entitys\App\CoinTurntableGetLog;
use App\Entitys\App\CoinTurntableOrders;
use App\Entitys\App\CoinTurntablePrize;
use App\Entitys\App\HomeTopCategoryChild;
use App\Http\Controllers\Controller;
use App\Services\CoinPlate\CoinConst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class PrizeOrderController extends Controller
{
    private $model ;
    private $prizeModel ;
    private $logModel ;
    private $logTable ;
    private $prizeTable ;
    private $orderTable ;
    private $listModel ;
    public function __construct()
    {
        $this->model =  new CoinTurntableOrders();
        $this->logModel =  new CoinTurntableGetLog();
        $this->prizeModel =  new CoinTurntablePrize();
        $this->orderTable = $this->model->getTable();
        $this->logTable = $this->logModel->getTable();
        $this->prizeTable = $this->prizeModel->getTable();
        $this->listModel = $this->model
            ->leftJoin($this->logTable, $this->orderTable . '.get_log_id', '=', $this->logTable . '.id')
            ->leftJoin($this->prizeTable, $this->logTable . '.award_id', '=', $this->prizeTable . '.id');
    }

    //
    public function getList(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['app_id', 'status'];
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$this->orderTable . '.' . $item] = $params[$item];
                }
            }
            $list = $this->listModel->where($wheres);
            if (!empty($params['sort'])) { // 添加排序
                foreach ($params['sort'] as $key=>$value){
                    $item = json_decode($value, true);
                    foreach ($item as $column=>$direction) {
                        $list = $list->orderBy($this->orderTable . '.' .$column, $direction);
                    }
                }
            }
            if (!empty($params['date_range'])) {
                $list = $list->whereBetween('created_at', $params['date_range']);
            }
            $list = $list->paginate($limit, [$this->orderTable . '.id as oid', $this->logTable . '.*', $this->orderTable . '.*', $this->prizeTable . '.*']);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    public function exportForPage(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['app_id', 'status'];
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$this->orderTable . '.' . $item] = $params[$item];
                }
            }
            $list = $this->listModel->where($wheres);
            if (!empty($params['sort'])) { // 添加排序
                foreach ($params['sort'] as $key=>$value){
                    $item = json_decode($value, true);
                    foreach ($item as $column=>$direction) {
                        $list = $list->orderBy($this->orderTable . '.' .$column, $direction);
                    }
                }
            }
            if (!empty($params['date_range'])) {
                $list = $list->whereBetween('created_at', $params['date_range']);
            }

            $list = $list->paginate($limit, [$this->orderTable . '.id as oid', $this->logTable . '.*', $this->orderTable . '.*', $this->prizeTable . '.*']);
            if ($list) {
                foreach ($list as &$value) {
                    unset($value->id);
                    unset($value->get_log_id);
                    unset($value->status);
                    unset($value->deleted_at);
                    unset($value->updated_at);
                    unset($value->created_at);
                }
            }

            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }

            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /**
     * harry 导入提现
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importData(Request $request)
    {
        try {
            $params = $request->input();
            $list = $params['list'];
            $need = [
                'total' => count($list),     #总共导入的数据量
                'no_apply_num' => 0,          #导入的数据中未对应到提现申请的数量
                'success' => 0,               #操作正常的数量
                'fail' => 0,                  #操作失败的数量
            ];
            foreach ($list as $item) {
                $order = $this->listModel->where([
                    'order_no' => $item['order_no'],
                ])->orWhere([
                    'track_no' => $item['track_no'],
                ])->orWhere([
                    'track_no' => $item['track_no'],
                ])->first();
                if (empty($order)) {
                    try {

                    } catch (\Exception $exception) {
                        DB::connection('app38')->rollBack();
                        return $this->getInfoResponse(1001,
                            '共：' . $need['total'] .
                            ' 提交众薪打款中:' . $need['success'] .
                            ' 提交失败:' . $need['fail'] .
                            '系统异常结束：' . $exception->getMessage()
                        );
                    }

                } else {
                    $need['no_apply_num']++;
                }
            }
            return $this->getResponse($need);
        } catch (\Exception $e) {
//            Cache::forget($cache_key);
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