<?php

namespace App\Http\Controllers\EleAdmin\Live;

use App\Exceptions\ApiException;
use App\Http\Controllers\EleAdmin\BaseController;
use App\Services\Common\LiveGroupConfig;
use App\Services\Common\OssCdn;
use App\Services\Live\LiveServices;
use App\Tools\ObjectDataHandle;
use Illuminate\Http\Request;
use App\Models\EleAdmin\LiveInfo as LiveInfoModel;
use App\Models\EleAdmin\LiveShopGoods as LiveShopGoodsModel;
use App\Models\EleAdmin\ShopGoods as ShopGoodsModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class LiveInfoController extends BaseController
{
    public function lists(Request $request)
    {
        try {
            $params = $request->all();

            $query = LiveInfoModel::ofConditions($params)->orderBy('updated_at', 'desc');

            list($lives, $pagination) = $this->paginate($query);

            $records = ObjectDataHandle::handle($lives);

            $data['records'] = $records;
            $data['pagination'] = $pagination;

            return $this->getResponse($data);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function createNotice(Request $request)
    {
        try {
            $rules = [
                'title' => 'required',
                'user_name' => 'required',
                'head_image' => 'required',
                'image_url' => 'required',
                'back_url' => 'required',
                'live_url' => 'required',
                'desc' => 'required',
                'plan_time' => 'required',
                'good_ids' => 'required',
            ];

            $params = $request->all();

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if (count($params['good_ids']) > 30) {
                return ['code' => 1000, 'msg' => '最多只可选择30个关联商品'];
            }

            $dateTime = date('Y-m-d H:i:s');
            $save['title'] = $params['title'];
            $save['user_name'] = $params['user_name'];
            $save['head_image'] = $params['head_image'];
            $save['image_url'] = $params['image_url'];
            $save['back_url'] = $params['back_url'];
            $save['live_url'] = $params['live_url'];
            $save['desc'] = $params['desc'];
            $save['group_id'] = LiveGroupConfig::produceGroupId();
            $save['status'] = LiveInfoModel::STATUS_NOTICE;
            $save['plan_time'] = strtotime($params['plan_time']);
            $save['have_number'] = $params['have_number'] ?? 0;
            $save['see'] = $params['see'] ?? 0;
            $save['created_at'] = $dateTime;
            $save['updated_at'] = $dateTime;

            $liveId = LiveInfoModel::insertGetId($save);
            if (!$liveId) {
                return $this->getInfoResponse(1000, '网络异常！');
            }

            $goodsParams['live_id'] = $liveId;
            $goodsParams['good_ids'] = $params['good_ids'];
            $goodsRet = $this->relatedGoods($goodsParams);
            if ($goodsRet['code'] != 200) {
                return $this->getInfoResponse($goodsRet['code'], $goodsRet['msg']);
            }

            return $this->getResponse('操作成功！');
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    protected function relatedGoods($params)
    {
        try {
            if (empty($params['good_ids'])) {
                return ['code' => 1000, 'msg' => '请选择至少一个关联商品'];
            }

            $liveId = $params['live_id'];
            $goodIds = $params['good_ids'];
            $columns = ['id', 'title', 'volume', 'status', 'deleted_at'];

            $goods = ShopGoodsModel::select($columns)->whereIn('id', $goodIds)->get();
            if (empty($goods) || count($goods) != count($goodIds)) {
                return ['code' => 1000, 'msg' => '商品信息有误！'];
            }

            $save = [];
            $dateTime = date('Y-m-d H:i:s');

            foreach ($goods as &$good) {
                if ($good->status != ShopGoodsModel::STATUS_UP) {
                    return ['code' => 1000, 'msg' => '商品[' . $good->title . ']已下架'];
                }
                if ($good->volume <= 0) {
                    return ['code' => 1000, 'msg' => '商品[' . $good->title . ']的库存不足'];
                }

                $save[] = [
                    'good_id' => $good->id,
                    'live_id' => $liveId,
                    'read_is' => 0,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ];
            }

            $update['deleted_at'] = $dateTime;
            $update['updated_at'] = $dateTime;
            $result = LiveShopGoodsModel::where('live_id', $liveId)
                ->whereNull('deleted_at')
                ->update($update);
            if ($result === false) {
                return ['code' => 1000, 'msg' => '网络异常！'];
            }

            $result = LiveShopGoodsModel::insert($save);
            if ($result === false) {
                LiveInfoModel::where('id', $liveId)->update(['status' => LiveInfoModel::STATUS_END, 'updated_at' => date('Y-m-d H:i:s')]);
                return ['code' => 1000, 'msg' => '网络异常！'];
            }

            return ['code' => 200, 'msg' => '关联成功'];
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function liveStart(Request $request)
    {
        try {
            $liveId = $request->input('live_id', null);
            if (!$liveId) {
                return $this->getInfoResponse(1000, '参数异常！');
            }

            $live = LiveInfoModel::find($liveId);
            if (empty($live)) {
                return $this->getInfoResponse(1000, '网络异常！');
            }
            if ($live->status !== LiveInfoModel::STATUS_NOTICE) {
                return $this->getInfoResponse(1000, '无法执行此操作！');
            }

            $live->status = LiveInfoModel::STATUS_START;
            $result = $live->save();
            if ($result === false) {
                return $this->getInfoResponse(1000, '网络异常！');
            }

            return $this->getResponse('开播成功');
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function liveEnd(Request $request)
    {
        try {
            $liveId = $request->input('live_id', null);
            if (!$liveId) {
                return $this->getInfoResponse(1000, '参数异常！');
            }

            $live = LiveInfoModel::find($liveId);
            if (empty($live)) {
                return $this->getInfoResponse(1000, '网络异常！');
            }
            if ($live->status !== LiveInfoModel::STATUS_START) {
                return $this->getInfoResponse(1000, '无法执行此操作！');
            }

            $live->status = LiveInfoModel::STATUS_END;
            $result = $live->save();
            if ($result === false) {
                return $this->getInfoResponse(1000, '网络异常！');
            }

            return $this->getResponse('直播已结束');
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function getMembers(Request $request, LiveServices $liveServices)
    {
        $members = $liveServices->getLiveIngMemberAccount();

        $records['records'] = $members;
        $records['count'] = count($members);

        return $this->getResponse($records);
    }

    public function forbidMember(Request $request, LiveServices $liveServices)
    {
        $memberIds = $request->input('member_ids', []);
        $forbidAt = $request->input('forbid_at', 60);
        $forbidAt = $forbidAt ?? 60;

        if ($memberIds) {
            foreach ($memberIds as &$memberId) {
                $memberId = (string)$memberId;
            }

            $groupId = Cache::get('watch_live_number_l_i_v_e', '');
            $result = $liveServices->forbidSendMsg($groupId, $memberIds, $forbidAt);
            $resultData = json_decode($result, true);
            if ($resultData['ErrorCode'] != 0) {
                return $this->getInfoResponse($resultData['ErrorCode'], '操作失败：' . $resultData['ErrorInfo']);
            }

            return $this->getResponse('操作成功');
        }

        return $this->getInfoResponse(1000, '请选择要禁言的成员');
    }
}