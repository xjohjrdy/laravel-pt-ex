<?php


namespace App\Http\Controllers\EleAdmin;


use App\Entitys\App\CoinTurntable;
use App\Entitys\App\HarryAgreement;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUserGet;
use App\Http\Controllers\Controller;
use App\Services\Common\UserMoney;
use App\Services\HarryPay\Harry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SendMoneyController extends Controller
{
    private $model ;
    public function __construct()
    {
        $this->model =  new TaobaoChangeUserLog();
    }

    //
    public function getList(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['from_type', 'app_id'];
            $params['from_type'] = '99999';
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$item] = $params[$item];
                }
            }
            $list = $this->model->where($wheres);
            if (!empty($params['sort'])) { // 添加排序
                foreach ($params['sort'] as $key=>$value){
                    $item = json_decode($value, true);
                    foreach ($item as $column=>$direction) {
                        $list = $list->orderBy($column, $direction);
                    }
                }
            }
            $list = $list->paginate($limit);
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

    /**
     * 导入发放余额
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importSendMoney(Request $request)
    {
        try {
            $params = $request->input();
            $list = $params['list'];
            $userMoneyService = new UserMoney();
            foreach ($list as $item) {
                $app_id = $item['app_id'];
                $from_info = $item['from_info'];
                $money = $item['money'];
                $userMoneyService->plusCnyAndLog($app_id, $money, '99999', $from_info);
            }
            return $this->getResponse($list);
        } catch (\Exception $e) {
//            Cache::forget($cache_key);
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }
}