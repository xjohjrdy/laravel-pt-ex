<?php

namespace App\Http\Controllers\Live;

use App\Entitys\App\LiveInfo;
use App\Entitys\App\LiveShopGoods;
use App\Entitys\App\ShopGoods;
use App\Exceptions\ApiException;
use App\Services\Live\LiveServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PlayOverController extends Controller
{
    /*
     * 用户拉取直播列表数据
     */
    public function getAnchorLiveData(Request $request, LiveInfo $liveInfo, LiveShopGoods $liveShopGoods, ShopGoods $shopGoods)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',         #必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
//            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            $time = time();

            //Banner图数据
            $banners = [];

            //直播数据
            $live_ing = $liveInfo->where('start_time', '<=', $time)
                ->where('end_time', 0)
                ->orderByDesc('id')
                ->get(['id', 'image_url', 'user_name', 'head_image', 'title', 'see', 'group_id']);
            foreach ($live_ing as &$v) {
                $live_ing_good_id = $liveShopGoods->where('live_id', $v->id)->pluck('good_id')->toArray();
                $v->ing_good_num = count($live_ing_good_id);
                $live_ing_good_id = array_slice($live_ing_good_id, 0, 2);
                $live_ing_goods_data = $shopGoods->whereIn('id', $live_ing_good_id)->get(['sidle_img', 'price']);
                $v->live_goods = $live_ing_goods_data;
            }

            //预告数据
            $live_plan = $liveInfo->where('start_time', '>', $time)
                ->where('end_time', 0)
                ->orderByDesc('id')
                ->get(['id', 'image_url', 'back_url', 'user_name', 'head_image', 'title', 'have_number', 'plan_time', 'group_id']);
            foreach ($live_plan as &$v) {
                $banners[] = ['id' => $v->id, 'img' => $v->back_url];
                $live_plan_good_id = $liveShopGoods->where('live_id', $v->id)->pluck('good_id')->toArray();
                $v->plan_good_num = count($live_plan_good_id);
                $live_plan_good_id = array_slice($live_plan_good_id, 0, 2);
                $live_plan_goods_data = $shopGoods->whereIn('id', $live_plan_good_id)->get(['id', 'sidle_img', 'price']);
                $v->live_goods = $live_plan_goods_data;
                $v->plan_time = date('Y-m-d H:i:s', $v->plan_time);
            }

            if (empty($banners)) {
                $banners[] = ['id' => 0, 'img' => 'https://cdn01.36qq.com/CDN/1592288618735.png'];
            }

            $data = [
                'banners' => $banners,
                'live_ing' => $live_ing,
                'live_plan' => $live_plan,
            ];

            return $this->getResponse($data);//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到直播预告页
     */
    public function getPlanLiveData(Request $request, LiveInfo $liveInfo, LiveShopGoods $liveShopGoods)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',         #必须整数
                'live_id' => 'integer',         #必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
//            $app_id = $arrRequest['app_id'];
            $live_id = $arrRequest['live_id'];
            /***********************************/
            //得到预播详情
            $time = time();
            $live_plan = $liveInfo->where('id', $live_id)
                ->where('start_time', '>', $time)
                ->where('end_time', 0)
                ->first(['id', 'head_image', 'user_name', 'have_number', 'image_url', 'title', 'plan_time', 'desc', 'live_url']);

            if (empty($live_plan)) {
                return $this->getInfoResponse('1001', '网络异常,请刷新后再试!');//错误返回数据
            }

            //关联的商品数量
            $good_number = $liveShopGoods->where('live_id', $live_plan->id)->count();
            $live_plan->good_number = $good_number;
            $live_plan->plan_time = date('Y-m-d H:i:s', $live_plan->plan_time);

            return $this->getResponse($live_plan);//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /*
     * 预约直播
     */
    public function subscribePlanLive(Request $request, LiveInfo $liveInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',         #必须整数
                'live_id' => 'integer',         #必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
//            $app_id = $arrRequest['app_id'];
            $live_id = $arrRequest['live_id'];
            /***********************************/
            //得到预播详情
            $time = time();
            $live_plan = $liveInfo->where('id', $live_id)
                ->where('start_time', '>', $time)
                ->where('end_time', 0)
                ->increment('have_number');

            if (empty($live_plan)) {
                return $this->getInfoResponse('1001', '网络异常,请刷新后再试!');//错误返回数据
            }

            return $this->getResponse('直播订阅成功!');//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 获取推流地址
     */
    public function getPushUrl(Request $request)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',         #必须整数
                'live_id' => 'integer',         #必须整数
                'type' => Rule::in([1, 2]),     #必须1或2
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
//            $app_id = $arrRequest['app_id'];
            $live_id = $arrRequest['live_id'];
            $type = $arrRequest['type']; #1.推流 2.拉流 播放
            /***********************************/
            $liveServices = new LiveServices();

            $key = '084d42edbda43dfe90bddb2541e3ef60';
            $past_time = date('Y-m-d H:i:s', time() + 86440);
            if ($type == 1) {
                $url = $liveServices->getPushUrl("5banana.com", $live_id, $key, $past_time);//推流
            } else {
                $url = $liveServices->getPushUrl("zhibo.p89t.com", $live_id, $key, $past_time);//拉流
            }
            return $this->getResponse($url);//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 增加直播人数并拉取
     */
    public function addLiveNumber(Request $request, LiveInfo $liveInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
//                'app_id' => 'integer',         #必须整数
                'live_id' => 'integer',         #必须整数
                'number' => 'required',         #必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
//            $app_id = $arrRequest['app_id'];
            $live_id = $arrRequest['live_id'];
            $number = $arrRequest['number'];
            /***********************************/
            //增加直播人数
            $live_ing = $liveInfo->where('id', $live_id)->first();
            if (empty($live_ing)) {
                return $this->getInfoResponse('1001', '网络异常,请刷新后再试!');//错误返回数据
            }
            $live_ing->see = $live_ing->see + $number;
            $live_ing->save();

            return $this->getResponse($live_ing->see);//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 主播结束直播
     */
    public function endLive(Request $request, LiveInfo $liveInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
//                'app_id' => 'integer',         #必须整数
                'live_id' => 'integer',         #必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
//            $app_id = $arrRequest['app_id'];
            $live_id = $arrRequest['live_id'];
            /***********************************/
            $time = time();
            $live_ing = $liveInfo->where('id', $live_id)
                ->where('start_time', '<=', $time)
                ->where('end_time', 0)
                ->update(['end_time' => $time]);
            if ($live_ing == 0) {
                return $this->getInfoResponse('1001', '只有直播状态才能结束直播!');//错误返回数据
            }

            //关闭推送人数
            Cache::forget('watch_live_number_l_i_v_e');

            return $this->getResponse('已结束');//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 用户读取结束直播数据
     */
    public function userGetEndLive(Request $request, LiveInfo $liveInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',         #必须整数
                'live_id' => 'integer',         #必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
//            $app_id = $arrRequest['app_id'];
            $live_id = $arrRequest['live_id'];
            /***********************************/
            //拉取结束直播的数据
            $live_ing = $liveInfo->where('id', $live_id)
                ->where('end_time', '>', 0)
                ->first();
            if (empty($live_ing)) {
                return $this->getInfoResponse('1001', '网络异常,请刷新后再试!');//错误返回数据
            }

            //计算直播时长
            $time = $live_ing->end_time - $live_ing->start_time;
            $minute = intval($time / 60);
            $second = $time % 60;
            $live_time = sprintf("%02d", $minute) . ':' . sprintf("%02d", $second);

            $data = [
                'see' => $live_ing->see,
                'live_time' => $live_time,
                'comments_num' => floor($live_ing->see * 2.8) + 9,
            ];

            return $this->getResponse($data);//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 获取签名&sdkAppID
     */
    public function getSignOrSdkappid(Request $request)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
            $app_id = $arrRequest['app_id'];
            /***********************************/
            $identifier = $app_id;
            $liveServices = new LiveServices();
            $sign = $liveServices->genSig($identifier);

            $data = [
                'sdkappid' => '1400380722',
                'identifier' => $identifier,
                'sign' => $sign,
            ];

            return $this->getResponse($data);//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 开启直播
     */
    public function openLive(Request $request, LiveInfo $liveInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
//                'app_id' => 'integer',         #必须整数
                'live_id' => 'integer',         #必须整数
                'group_id' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取所需数据
//            $app_id = $arrRequest['app_id'];
            $live_id = $arrRequest['live_id'];
            $group_id = $arrRequest['group_id'];
            /***********************************/
            $time = time();
            //只有预告的直播才能开启
            $live_plan = $liveInfo->where('id', $live_id)
                ->where('group_id', $group_id)
                ->where('start_time', '>', $time)
                ->where('end_time', 0)
                ->update(['start_time' => $time]);

            if ($live_plan == 0) {
                return $this->getInfoResponse('1001', '只有预播状态才能开启直播!');//错误返回数据
            }

            //设置直播 用于推送人数
            Cache::forever('watch_live_number_l_i_v_e', $group_id);

            return $this->getResponse('成功开启');//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
