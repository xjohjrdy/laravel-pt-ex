<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\TaobaoClassification;
use App\Entitys\App\TaobaoH5CashGit;
use App\Entitys\App\TaobaoIndex;
use App\Exceptions\ApiException;
use App\Services\Alimama\BigWashUser;
use App\Services\Taobaoke\My;
use App\Services\Taobaoke\Utils;
use App\Services\TbkCashCreate\TbkCashCreateServices;
use ETaobao\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    /**
     * 获取首页的信息接口
     */
    public function getIndexInfo(Request $request, TaobaoIndex $taobaoIndex)
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
            $header = $taobaoIndex->getTaoIndex(1);
            $middle = $taobaoIndex->getTaoIndex(2);
            $down = $taobaoIndex->getTaoIndex(3);
            return $this->getResponse([
                'header' => $header,
                'middle' => $middle,
                'down' => $down,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 首页分类接口
     */
    public function getIndexType(Request $request, TaobaoClassification $taobaoClassification)
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

            $one = $taobaoClassification->getByFather(0);

            foreach ($one as $k => $item) {
                $one[$k]['detail'] = $taobaoClassification->getByFather($item->id);
            }

            return $this->getResponse($one);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 本月预估收入，上月预估收入
     */
    public function getIndexMy(Request $request, My $my, AppUserInfo $appUserInfo)
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
            $user = $appUserInfo->getUserById($arrRequest['app_id']);

            $team_next_month_cash_amount = $my->teamNextMonthCash($arrRequest['app_id']);
            $team_cash_amount =
                $user['order_amount'] + $my->teamLastMonthOrderAmount($arrRequest['app_id']);
            $team_cash_amount = number_format($team_cash_amount, 2);

            return $this->getResponse([
                'team_next_month_cash_amount' => $team_next_month_cash_amount,
                'team_cash_amount' => $team_cash_amount,
            ]);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取排行
     */
    public function getRank(Request $request)
    {

        parse_str($request->getContent(), $strParams);

        if (empty($strParams))
            throw new ApiException('参数输入错误', 3001);

        $apiUtils = new Utils('taobao.tbk.dg.material.optional');
        $strParams['adzone_id'] = '91593200288';
        $strParams['sort'] = 'total_sales_des';
        $strParams['q'] = '包邮';
        $apiUtils->arrange($strParams);

        $strParams['sign'] = $apiUtils->generateSign($strParams);
        $requestUrl = '?';
        foreach ($strParams as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);

        $arrParams = json_decode($apiUtils->curl($requestUrl));

        if (@$arrParams->error_response)
            throw new ApiException($arrParams->error_response->msg, 4001);


        return $this->getResponse($arrParams);
    }

    /**
     * 获取不同类型的列表
     */
    public function getTypeList(Request $request)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'type_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        parse_str($request->getContent(), $strParams);

        unset($strParams['type_id']);
        $strParams['q'] = '包邮';
        $strParams['sort'] = 'total_sales_des';
        if ($arrRequest['type_id'] == 1) {
            $strParams['sort'] = 'total_sales_des';
        }
        if ($arrRequest['type_id'] == 2) {
            $strParams['sort'] = 'tk_rate_des';
        }
        if ($arrRequest['type_id'] == 3) {
            $strParams['sort'] = 'tk_total_sales_des';
        }
        if ($arrRequest['type_id'] == 4) {
            $strParams['sort'] = 'tk_total_commi_des';
        }
        if ($arrRequest['type_id'] == 5) {
            $strParams['sort'] = 'price_des';
        }

        if (empty($strParams))
            throw new ApiException('参数输入错误', 3001);


        $strParams['sort'] = 'total_sales';
        $apiUtils = new Utils('taobao.tbk.dg.material.optional');
        $strParams['adzone_id'] = '91593200288';
        $apiUtils->arrange($strParams);

        $strParams['sign'] = $apiUtils->generateSign($strParams);
        $requestUrl = '?';
        foreach ($strParams as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);

        $arrParams = json_decode($apiUtils->curl($requestUrl));

        if (@$arrParams->error_response)
            throw new ApiException($arrParams->error_response->msg, 4001);


        return $this->getResponse($arrParams);
    }

    /**
     * 获取商品的详情
     */
    public function getInfoShop(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'num_iids' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $config = config('taobao.config_taobao_one');

            $app = Factory::Tbk($config);
            $param = [
                'num_iids' => $arrRequest['num_iids'],
            ];
            $res = $app->item->getInfo($param);
            $strParams['sort'] = 'total_sales_asc';
            $apiUtils = new Utils('taobao.tbk.dg.material.optional');
            $strParams['adzone_id'] = '91593200288';
            $strParams['q'] = '睡衣';
            $apiUtils->arrange($strParams);

            $strParams['sign'] = $apiUtils->generateSign($strParams);
            $requestUrl = '?';
            foreach ($strParams as $sysParamKey => $sysParamValue) {
                $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
            }
            $requestUrl = substr($requestUrl, 0, -1);

            $guess_like = json_decode($apiUtils->curl($requestUrl));

            return $this->getResponse([
                'detail' => $res,
                'guess_like' => $guess_like,
            ]);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }

    /**
     * 搜索
     */
    public function getSearchUrl(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'word' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $apiUtils = new Utils('taobao.tbk.dg.material.optional');
            $strParams['adzone_id'] = '91593200288';
            $strParams['q'] = $arrRequest['word'];
            if (!empty($arrRequest['sort'])) {
                $strParams['sort'] = $arrRequest['sort'];
            }
            $apiUtils->arrange($strParams);

            $strParams['sign'] = $apiUtils->generateSign($strParams);
            $requestUrl = '?';
            foreach ($strParams as $sysParamKey => $sysParamValue) {
                $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
            }
            $requestUrl = substr($requestUrl, 0, -1);

            $res = json_decode($apiUtils->curl($requestUrl));

            return $this->getResponse($res);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 一元购 淘礼金
     */
    public function oneShoppingGift(Request $request, TaobaoH5CashGit $taobaoH5CashGit, BigWashUser $bigWashUser, TbkCashCreateServices $tbkCashCreateServices, AppUserInfo $appUserInfo, AdUserInfo $adUserInfo)
    {
        try {
            $post_data = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $data = [];
            $time = date('Y-m-d H:i:s');
            $all_res = $taobaoH5CashGit->get();
            $create_time = $appUserInfo->where('id', $post_data['app_id'])->value('create_time');
            $groupid = $adUserInfo->where('pt_id', $post_data['app_id'])->value('groupid');
            $time_day = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

            foreach ($all_res as $v) {
                $welfare_type = [
                    1 => 'new',
                    2 => 'vip',
                    3 => 'all',
                ];
                if ($time < $v->send_end_time) {
                    $goods_details_present = $bigWashUser->shareGoodsDetails(['goodsId' => $v->item_id]);
                    $arr_goods_details_present = $goods_details_present;

                    if ($v->special_id == 2 && $groupid < 23) {
                        $arr_goods_details_present['send_url'] = 0;
                        $arr_goods_details_present['rights_id'] = 0;
                        $arr_goods_details_present['per_face'] = $v->per_face;
                        $arr_goods_details_present['send_start_time'] = $v->send_start_time;
                        $arr_goods_details_present['send_end_time'] = $v->send_end_time;
                        $v->send_url = 0;
                        $v->rights_id = 0;
                        $v->save();
                    } elseif ($v->special_id == 1 && $create_time < $time_day) {
                        $arr_goods_details_present['send_url'] = 0;
                        $arr_goods_details_present['rights_id'] = 0;
                        $arr_goods_details_present['per_face'] = $v->per_face;
                        $arr_goods_details_present['send_start_time'] = $v->send_start_time;
                        $arr_goods_details_present['send_end_time'] = $v->send_end_time;
                        $v->send_url = 0;
                        $v->rights_id = 0;
                        $v->save();
                    } else {
                        if ($v->send_url && $v->rights_id) {
                            $arr_goods_details_present['send_url'] = $v->send_url;
                            $arr_goods_details_present['rights_id'] = $v->rights_id;
                            $arr_goods_details_present['per_face'] = $v->per_face;
                            $arr_goods_details_present['send_start_time'] = $v->send_start_time;
                            $arr_goods_details_present['send_end_time'] = $v->send_end_time;
                        } else {
                            $cash_create_present = $tbkCashCreateServices->cashCreate(
                                $v->adzone_id, $v->item_id, $v->total_num, $v->name,
                                $v->user_total_win_num_limit, $v->security_switch,
                                $v->per_face, $v->send_start_time, "",
                                $v->send_end_time, $v->use_end_time, $v->use_end_time_mode);

                            if ($cash_create_present['result']['success'] != 'true') {
                                return $this->getInfoResponse('1001', $cash_create_present['result']['msg_info']);
                            }
                            $arr_goods_details_present['send_url'] = $cash_create_present['result']['model']['send_url'];
                            $arr_goods_details_present['rights_id'] = $cash_create_present['result']['model']['rights_id'];
                            $arr_goods_details_present['per_face'] = $v->per_face;
                            $arr_goods_details_present['send_start_time'] = $v->send_start_time;
                            $arr_goods_details_present['send_end_time'] = $v->send_end_time;
                            $v->send_url = $cash_create_present['result']['model']['send_url'];
                            $v->rights_id = $cash_create_present['result']['model']['rights_id'];
                            $v->save();
                        }
                    }
                    $data['present'][$welfare_type[$v->special_id]][] = $arr_goods_details_present;
                }
                if ($v->send_end_time <= $time) {
                    $goods_details_formerly = $bigWashUser->shareGoodsDetails(['goodsId' => $v->item_id]);
                    $arr_goods_details_formerly = $goods_details_formerly;
                    $arr_goods_details_formerly['send_url'] = '-1';
                    $arr_goods_details_formerly['rights_id'] = 0;
                    $arr_goods_details_formerly['per_face'] = $v->per_face;
                    $arr_goods_details_formerly['send_start_time'] = $v->send_start_time;
                    $arr_goods_details_formerly['send_end_time'] = $v->send_end_time;
                    $v->send_url = '-1';
                    $v->rights_id = 0;
                    $v->save();

                    $data['formerly'][$welfare_type[$v->special_id]][] = $arr_goods_details_formerly;
                }
            }
            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 淘礼金镜像
     */
    public function giftMirror(Request $request, TaobaoH5CashGit $taobaoH5CashGit, BigWashUser $bigWashUser)
    {
        try {
            $post_data = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($post_data, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $data = [];
            $time = date('Y-m-d H:i:s');
            $all_res = $taobaoH5CashGit->get();

            foreach ($all_res as $v) {
                $welfare_type = [
                    1 => 'new',
                    2 => 'vip',
                    3 => 'all',
                ];
                if ($v->send_start_time <= $time && $time < $v->send_end_time) {
                    $goods_details_present = $bigWashUser->shareGoodsDetails(['goodsId' => $v->item_id]);
                    $arr_goods_details_present = $goods_details_present;

                    $arr_goods_details_present['send_url'] = 2;
                    $arr_goods_details_present['rights_id'] = 0;
                    $arr_goods_details_present['per_face'] = $v->per_face;
                    $v->send_url = 2;
                    $v->rights_id = 2;
                    $v->save();

                    $data['present'][$welfare_type[$v->special_id]][] = $arr_goods_details_present;
                }
                if ($v->send_end_time <= $time) {
                    $goods_details_formerly = $bigWashUser->shareGoodsDetails(['goodsId' => $v->item_id]);
                    $arr_goods_details_formerly = $goods_details_formerly;

                    $arr_goods_details_formerly['send_url'] = '2';
                    $arr_goods_details_formerly['rights_id'] = 0;
                    $arr_goods_details_formerly['per_face'] = $v->per_face;
                    $v->send_url = '2';
                    $v->rights_id = 0;
                    $v->save();

                    $data['formerly'][$welfare_type[$v->special_id]][] = $arr_goods_details_formerly;
                }
            }
            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
