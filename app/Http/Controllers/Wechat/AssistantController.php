<?php

namespace App\Http\Controllers\Wechat;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\WechatAssistantAudit;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AssistantController extends Controller
{
    /**
     * 群助手
     * @param Request $request
     * @param WechatAssistantAudit $assistantAudit
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, WechatAssistantAudit $assistantAudit)
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

            $app_id = $arrRequest['app_id'];
            $app_img = empty($arrRequest['img']) ? '' : $arrRequest['img'];
            $app_contact = empty($arrRequest['contact']) ? '' : $arrRequest['contact'];
            $app_read = empty($arrRequest['read']) ? '' : $arrRequest['read'];

            $user_assistant_obj = $assistantAudit->where(['app_id' => $app_id])->first();
            if (empty($user_assistant_obj)) {
                $user_assistant_obj = new WechatAssistantAudit();
                $user_assistant_obj->app_id = $app_id;
                $user_assistant_obj->save();
                $user_assistant_obj->refresh();
                $user_assistant_obj->title = '京东内购优惠群' . (10000000 + $user_assistant_obj->id);
                $user_assistant_obj->save();
            }
//            dd($user_assistant_obj->toArray());

            if ($user_assistant_obj->status != 9) {
                return $this->getInfoResponse(2001, '智能助理功能升级中');
            }

            switch ($user_assistant_obj->status) {
                case 0: //待提交
                    if (!empty($app_img) && !empty($app_contact)) {
                        $user_assistant_obj->img = $app_img;
                        $user_assistant_obj->contact = $app_contact;
                        $user_assistant_obj->status = 1; //状态更改为待审核
                        $user_assistant_obj->save();
                        $this->getResponse('申请成功');
                    } elseif (!empty($app_img) || !empty($app_contact)) {
                        return $this->getInfoResponse('3001', '参数异常');
                    }
                    return $this->getResponse(collect($user_assistant_obj)->only(['title', 'status']));
                    break;
                case 1: //待审核
                    return $this->getResponse(collect($user_assistant_obj)->only(['title', 'status', 'contact', 'img']));
                    break;
                case 2: //审核成功
                    if (!empty($app_read)) {
                        $user_assistant_obj->status = 9;
                        $user_assistant_obj->save();
                        $url = "https://www.91fyt.com/app/html/assistant/senior.html?bot_pf=3&uid={$app_id}&key=584831d33eaca654&time=" . time() . "000&hongbao=false&fans=false&groupsend=false&help=false&caiji=false";
                        return $this->getResponse(['url' => $url, 'status' => 9]);
                    }
                    return $this->getResponse(collect($user_assistant_obj->getOriginal())->only(['title', 'status']));
                    break;
                case 4: //审核失败
                    if (!empty($app_img) && !empty($app_contact)) {
                        $user_assistant_obj->img = $app_img;
                        $user_assistant_obj->contact = $app_contact;
                        $user_assistant_obj->status = 1; //状态更改为待审核
                        $user_assistant_obj->save();
                        $this->getResponse('申请成功');
                    } elseif (!empty($app_img) || !empty($app_contact)) {
                        return $this->getInfoResponse('3001', '参数异常');
                    }
                    return $this->getResponse(collect($user_assistant_obj)->only(['title', 'status', 'contact', 'img', 'reason']));
                    break;
                case 9: //用户已创建群助手
                    $url = "https://www.91fyt.com/app/html/assistant/senior.html?bot_pf=3&uid={$app_id}&key=584831d33eaca654&time=" . time() . "000&hongbao=false&fans=false&groupsend=false&help=false&caiji=false";
                    Storage::disk('local')->append('callback_document/w_assistant.txt', date('H:i:s') . '#### ' . var_export($url, true) . ' ####');
                    return $this->getResponse(['url' => $url, 'status' => 9]);
                    break;
                default:
                    return $this->getInfoResponse('5001', '状态异常');
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getV2(Request $request, WechatAssistantAudit $assistantAudit)
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
//            return $this->getInfoResponse('5001', '内测中，请耐心等待正式上线！');
            $app_id = $arrRequest['app_id'];

            $groupid = AdUserInfo::where(['pt_id' => $app_id])->value('groupid');

            if ($groupid != 23 && $groupid != 24) {

                return $this->getInfoResponse('5001', '智能助理内侧中，后期会逐步开放，敬请期待！');

                $user_assistant_obj = $assistantAudit->where(['app_id' => $app_id])->first();

                if (empty($user_assistant_obj) || $user_assistant_obj->status != 9) {
                    return $this->getInfoResponse('5001', '内测中，仅限超级用户体验，请耐心等待正式上线！');
                }

                $url = "https://www.91fyt.com/app/html/assistant/senior.html?bot_pf=3&uid={$app_id}&key=584831d33eaca654&time=" . time() . "000&hongbao=false&fans=false&groupsend=false&help=false&caiji=false";

                return $this->getResponse($url);
            }

            return $this->getResponse('http://api.36qq.com/assistant_v2/');
        } catch
        (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
