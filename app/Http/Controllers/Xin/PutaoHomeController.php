<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\Ad\VoipAccount;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\NoShowAndroid;
use App\Entitys\Xin\Advertising;
use App\Entitys\Xin\Config;
use App\Entitys\Xin\HomeBanner;
use App\Entitys\Xin\HomeName;
use App\Entitys\Xin\HomeUrl;
use App\Entitys\Xin\Notice;
use App\Entitys\Xin\SearchUrl;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PutaoHomeController extends Controller
{
    /*
     * 得到首页弹窗广告数据
     */
    public function getAdvertising(Advertising $advertising)
    {
        try {
            $data = $advertising->first();
            if (!$data) {
                return $this->getInfoResponse('1001', '获取数据失败');
            }
            $start_time = $data->start_time;
            $end_time = $data->end_time;
            if ($start_time <= time() && time() <= $end_time) {
                $data->is_show = 1;
            } else {
                $data->is_show = 2;
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
     * 获取公告总数
     */
    public function getAnnouncementTotal(Notice $notice)
    {
        try {
            $nub_total_data['notice_total_num'] = $notice->count('id');
            if (!$nub_total_data) {
                return $this->getInfoResponse('1001', '数据获取失败');
            }
            return $this->getResponse($nub_total_data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到首页搜索URL链接数据
     */
    public function getSearchUrlData(Request $request, SearchUrl $searchUrl)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'type' => Rule::in([1, 2, 3, 4])
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $type = $arrRequest['type'];
            /***********************************/
            $obj_search_data = $searchUrl->getSearchUrlByType($type);
            if (!$obj_search_data) {
                return $this->getInfoResponse('1001', '数据获取失败');
            }
            return $this->getResponse($obj_search_data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到首页导航的一些相关数据
     */
    public function getHomeUrlData(Request $request, HomeUrl $homeUrl, HomeBanner $homeBanner, HomeName $homeName, Config $config)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'version' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $version = $arrRequest['version'];
            /***********************************/
            $obj_home_name = $homeName->getHomeName();
            $where_url = [];
            $ios_hide_version = $config->getHideConfigValue('ios_hide_version');
            if ($version == $ios_hide_version) {
                $where_url = ['任务赚钱', '福音电影', '影视优惠', '爆款商城', '全网影视', '双11红包', '支付宝红包', '淘宝报销', '葡萄红包', '葡萄通讯', '券加报销', '广告联盟', '银行合作', '办信用卡', '贷款中心', '医疗健康'];
            }

            $no_show = new NoShowAndroid();
            $version_one = $no_show->getOneVersion();
            if ($version_one == $version) {
                $where_url = ['任务赚钱', '福音电影', '全网影视', '支付宝红包', '淘宝报销', '葡萄红包', '葡萄通讯', '券加报销', '广告联盟', '银行合作', '办信用卡', '贷款中心', '医疗健康'];
            }


            // 根据app_id添加拦截 有上级才展示
            if (empty($arrRequest['app_id'])) {
                $where_url[] = '影视优惠';
            } else {
                $app_id = $arrRequest['app_id'];
                $parent_id = AppUserInfo::where('id', $app_id)->value('parent_id');
                if (empty($parent_id)) {
                    $where_url[] = '影视优惠';
                }
            }

            $obj_home_url = $homeUrl->getHomeUrl($where_url);
            if ($version == $ios_hide_version) {
                $obj_home_banner = $homeBanner->getHomeBannerY();
            } else {
                $obj_home_banner = $homeBanner->getHomeBannerN();
            }
            $is_conceal_banner = 1;
            if (!empty($obj_home_banner)) {
                foreach ($obj_home_banner as $k => $value) {
                    $start_time = $value->start_time;
                    $end_time = $value->end_time;
                    if ($start_time <= time() && time() <= $end_time) {
                        $is_conceal_banner = 0;
                    } else {
                        $is_conceal_banner = 1;
                    }
                }
            }

            if ($version == '4.6.0') {
                foreach ($obj_home_url as $i => $item) {
                    if ($item->id == 165) {
                        $obj_home_url[$i]->url = 'https://open.czb365.com/redirection/todo';
                    }
                }
            }

            return [
                'code' => 200,
                'message' => '数据获取成功',
                'data' => ['data' => $obj_home_url,
                    'name' => $obj_home_name,
                    'banner' => $obj_home_banner,
                    'is_show_function' => $is_conceal_banner,
                ]
            ];
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到首页公告列表
     */
    public function getList(Request $request, Notice $notice)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if (!empty($request->page)) {
                if ($request->page <> 1) {
                    return $this->getInfoResponse('1011', '暂无数据');
                }
            }
            /***********************************/
            $obj_list = $notice->getList();
            if (empty($obj_list->items())) {
                return $this->getInfoResponse('1001', '数据获取失败');
            }
            return $this->getResponse($obj_list);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到公告详情页
     */
    public function getDetails(Notice $notice)
    {
        $id = \request()->input('id');
        $obj_notice = $notice->find($id);
        return view('xin.notice', [
            'data' => $obj_notice
        ]);
    }

    /*
     * 广告id随机下发
     */
    public function advertisingIdIssue(Request $request)
    {
        $request_device = $request->header('Accept-Device'); //设备类型
        $data = [101149, 101457, 101458];
        if ($request_device != 'android') {
            $data = [101465, 101464, 101167];
        }
        shuffle($data);
        return $this->getResponse($data);
    }

    /*
     * 得到购物报销教程视频url
     */
    public function getShopVideoUrl()
    {
        $url = 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/1%20-%20%E8%B4%AD%E7%89%A9%E6%8A%A5%E9%94%80.mp4';
        return $this->getResponse($url);
    }
}
