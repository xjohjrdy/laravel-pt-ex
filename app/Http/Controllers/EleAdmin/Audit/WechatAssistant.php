<?php

namespace App\Http\Controllers\EleAdmin\Audit;

use App\Entitys\App\WechatAssistantAudit;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WechatAssistant extends Controller
{
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
                    $wheres[$item] = $params[$item];
                }
            }

            $model = new WechatAssistantAudit();
            $list = $model->where($wheres);
            if (!empty($params['sort'])) { // 添加排序
                foreach ($params['sort'] as $key=>$value){
                    $item = json_decode($value, true);
                    foreach ($item as $column=>$direction) {
                        $list = $list->orderBy($column, $direction);
                    }
                }
            }
            if (!empty($params['date_range'])) {
                $list = $list->whereBetween('created_at', $params['date_range']);
            }
            $list = $list->orderBy('id', 'desc')->paginate($limit);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    /**
     * 审核
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function audit(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'id' => 'required',
                'status' => Rule::in([0,1])
            ];
            $model = new WechatAssistantAudit();
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $audit_info = $model->where(['id' => $params['id'], 'status' => 1])->first();
            if(empty($audit_info)){
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $audit_info->update([
                    'status' => $params['status'] == 0 ? 4 : 2,
                    'reason' => $params['reason'],
                ]);
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
     * 批量审核
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchAudit(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'list' => 'required',
                'status' => Rule::in([0,1])
            ];
            $model = new WechatAssistantAudit();
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $list = $params['list'];
            $results = [
                'success' => 0,
                'total' => count($list),
                'unSearch' => 0,
                'fail' => 0
            ];
            foreach ($list as $item){
                try{
                    $audit_info = $model->where(['id' => $item['id'], 'status' => 1])->first();
                    if(empty($audit_info)){
                        $results['unSearch'] += 1;
                    } else {
                        $audit_info->update([
                            'status' => $params['status'] == 0 ? 4 : 2,
                            'reason' => $params['reason'],
                        ]);
                        $results['success'] += 1;
                    }
                } catch (\Exception $e){
                    $results['fail'] += 1;
                }
            }
            return $this->getResponse($results);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }
}
