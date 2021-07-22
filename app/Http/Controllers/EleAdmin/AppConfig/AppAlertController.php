<?php


namespace App\Http\Controllers\EleAdmin\AppConfig;


use App\Entitys\App\ActivityAlertConfig;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppAlertController extends Controller
{
    private $model ;
    public function __construct()
    {
        $this->model =  new ActivityAlertConfig();
    }

    //
    public function getFirst(Request $request)
    {
        try {
            $list = $this->model->where(['id' => 1])->first();
            $list['date_range'] = [
                date('Y-m-d H:i:s', $list['begin_time']),
                date('Y-m-d H:i:s', $list['end_time'])
            ];
            return $this->getResponse($list);
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
            $params['begin_time'] = strtotime($params['date_range'][0]);
            $params['end_time'] = strtotime($params['date_range'][1]);
            unset($params['s'], $params['date_range']);
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