<?php

namespace App\Http\Controllers\News;

use App\Exceptions\ApiException;
use App\Services\UCNews\UCNewsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class UCNewsController extends Controller
{
    /**
     * 获取频道列表信息
     * @param Request $request
     * @param UCNewsService $newsService
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getChannels(Request $request, UCNewsService $newsService)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'dn' => 'required',
                'fr' => 'required',
                've' => 'required',
                'nt' => 'required',
                'client_ip' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $res = json_decode($newsService->getChannels($arrRequest), true);
            $data = [];
            $message = empty($res['message']) ? '请求失败！' : $res['message'];
            if (@$res['status'] == 0) {
                foreach ($res['data']['channel'] as $key => $item) {
                    $data[$key]['id'] = $item['id'];
                    $data[$key]['name'] = $item['name'];
                }
                return $this->getResponse($data);
            } else {
                return $this->getInfoResponse(@$res['status'], $message);
            }
        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * 根据频道ID获取当前频道新闻详情
     * @param Request $request
     * @param UCNewsService $newsService
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getChannelDetails(Request $request, UCNewsService $newsService)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'dn' => 'required',
                'fr' => 'required',
                've' => 'required',
                'nt' => 'required',
                'client_ip' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if (empty($arrRequest['cid'])) {
                $arrRequest['cid'] = $newsService->getFirstChannelId($arrRequest);
            }
            if(!empty($arrRequest['city_name'])){
                $arrRequest['city_name'] = str_replace('市', '', $arrRequest['city_name']);
            }
            $res = json_decode($newsService->getChannelDetails($arrRequest), true);
            $message = empty($res['message']) ? '请求失败！' : $res['message'];
            if (@$res['status'] == 0) {
                $articles = [];
                foreach ($res['data']['items'] as $key => $item) {
                    $articles[$key] = $res['data'][$item['map']][$item['id']];
                    if (!empty($articles[$key]['publish_time'])) {
                        $articles[$key]['from_time'] = date('m-d H:i', $articles[$key]['publish_time'] / 1000);
                    }
                    $articles[$key]['cmt_cnt'] = $articles[$key]['cmt_cnt'] + 999;
                    $articles[$key]['share_url'] = $articles[$key]['url'];
                }
                $res_data = [
                    'articles' => $articles,
                    'banners' => empty($res['data']['banners']) ? [] : $res['data']['banners'],
                    'specials' => empty($res['data']['specials']) ? [] : $res['data']['specials']
                ];
                return $this->getResponse($res_data);
            } else {
                return $this->getInfoResponse(@$res['status'], $message);
            }
        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
        }

    }

    /**
     * UC 头条 token 获取回调函数
     * @param Request $request
     * @return string
     */
    public function NotifyUCTokenUpdate(Request $request, UCNewsService $newsService)
    {
        $params = Input::all();
        $newsService->log($params);
        if (!empty($params['access_token'])) {
            Cache::put('PU_TAO_UC_ACCESS_TOKEN', $params['access_token'], 60 * 24 * 6);
            return 'success';
        } else {
            return 'false';
        }
    }
}
