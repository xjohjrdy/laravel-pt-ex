<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\Xin\Config;
use App\Entitys\Xin\Poster;
use App\Entitys\Xin\Suggestion;
use App\Entitys\Xin\WebShow;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ShareGatherController extends Controller
{
    /*
     * 得到全部海报接口
     */
    public function getPosterImages(Request $request, Poster $poster, Config $config)
    {
        try {
            /***********************************/

            //分享海报
            //拦截版本
            $request_device = $request->header('Accept-Device'); //设备类型
            $request_appversion = $request->header('Accept-Appversion'); //版本号
//            if (($request_device == 'android' && $request_appversion >= 202) || ($request_device == 'ios' && version_compare($request_appversion, '4.6.8', '>='))) {
//                $obj_porter_data = [
////                    [
////                        "poster_id" => 50,
////                        "title" => "芒种",
////                        "content" => "二十四节气之芒种\r\n“时雨及芒种，四野皆插秧。”",
////                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/%E8%8A%92%E7%A7%8D.jpg"
////                    ],
//                    [
//                        "poster_id" => 50,
//                        "title" => "天猫618活动来袭",
//                        "content" => "爆款尖货 6.1开抢\r\n抢最高618元红包",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/00.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "办信用卡来我的",
//                        "content" => "在线就能一键办卡\r\n自办返佣金 分享能赚钱",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/01.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "肯德基6折起",
//                        "content" => "特惠点餐尽情开吃\r\n我的浏览器请你吃KFC啦~",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/022.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "饿了么请你吃大餐",
//                        "content" => "天天领优惠\r\n最高66元霸王餐\r\n大餐吃饱 又省又好",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/03.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "优惠外卖 美团买单",
//                        "content" => "最高18元红包\r\n天天抢红包吃大餐",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/044.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "我的圈子 人脉增值",
//                        "content" => "精准吸引人脉\r\n具备投资价值\r\n准确营销+投放",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/05.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "全网隐藏优惠",
//                        "content" => "汇聚各大平台\r\n亿万隐藏优惠\r\n做你的省赚后盾",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/06.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "刷脸支付红利",
//                        "content" => "降成本 增收入\r\n全场景安全适用\r\n领取时代风口红利",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/07.png"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "一键加油 单单立省",
//                        "content" => "全国400+城市\r\n10000+加油站\r\n特惠加油8.5折起",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/08.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "一键加油 单单立省",
//                        "content" => "全国400+城市\r\n10000+加油站\r\n特惠加油8.5折起",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/088.jpg"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "免费预约 体检特惠",
//                        "content" => "3000+医院免费预约\r\n1W+三甲专家线上问诊\r\n超级用户全国特惠体检",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/09.png"
//                    ], [
//                        "poster_id" => 50,
//                        "title" => "视频会员4.3折起",
//                        "content" => "会员在手 追剧不愁\r\n热门影视会员超值购",
//                        "img_url" => "http://cdn01.36qq.com/CDN/%E6%B5%B7%E6%8A%A5%E5%88%86%E4%BA%AB/10.png"
//                    ]
//                ];
//            } else {
                $obj_porter_data = $poster->getPoster();
//            }


            $obj_back_ground = $config->getConfig();
            $data = [
                'data' => $obj_porter_data,
                'poster_background' => $obj_back_ground['poster_background'],
                'url_background' => $obj_back_ground['url_background']
            ];
            if (empty($data)) {
                return $this->getInfoResponse('1001', '获取海报数据失败！');
            }
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 展示,关于我们
     */
    public function aboutUs(WebShow $webShow)
    {
        $obj_about_info = $webShow->aboutInfo();
        return view('xin.aboutus', [
            'data' => $obj_about_info
        ]);

    }

    /*
     * 提交意见反馈
     */
    function submitSuggestion(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'type' => Rule::in([1, 2, 3]),
                'content' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if (!empty($arrRequest['contact_phone'])) {
                if (!(preg_match("/^1[3456789]\d{9}$/", $arrRequest['contact_phone']))) {
                    return $this->getInfoResponse('1001', '请检查手机号是否有误！');
                }
            }
            $type = $arrRequest['type'];
            $content = $arrRequest['content'];
            $contact_phone = $arrRequest['contact_phone'];
            /***********************************/
            $obj_suggestion = new Suggestion();
            $res = $obj_suggestion->addtSuggestion($type, $content, $contact_phone);
            if (empty($res)) {
                return $this->getInfoResponse('1002', '提交失败！');
            }
            return $this->getResponse("提交成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
    * 提交意见反馈（小程序版）
    */
    function submitSuggestionMini(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'type' => Rule::in([1, 2, 3]),
                'content' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if (!empty($arrRequest['contact_phone'])) {
                if (!(preg_match("/^1[3456789]\d{9}$/", $arrRequest['contact_phone']))) {
                    return $this->getInfoResponse('1001', '请检查手机号是否有误！');
                }
            }
            $type = $arrRequest['type'];
            $content = $arrRequest['content'];
            $contact_phone = $arrRequest['contact_phone'] ?? 0 ?: 0;
//            $url_img2 = empty($arrRequest['url_img']) ? 0 : $arrRequest['url_img'];
            $url_img = $arrRequest['url_img'] ?? 0 ?: 0;
            /***********************************/
            $obj_suggestion = new Suggestion();
            $res = $obj_suggestion->addNew([
                'type' => $type,
                'content' => $content,
                'contact_phone' => $contact_phone,
                'create_time' => time(),
                'url_img' => $url_img,
                'from' => 1,
            ]);
            if (empty($res)) {
                return $this->getInfoResponse('1002', '提交失败！');
            }
            return $this->getResponse("提交成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
