<?php

namespace App\Http\Controllers\Harry;

use App\Entitys\Other\HarryAgreementCallBackOut;
use App\Entitys\Other\HarryAgreementOut;
use App\Entitys\OtherOut\AppUserInfoOut;
use App\Exceptions\ApiException;
use App\Services\HarryPayOut\Harry;
use App\Services\HarryPayOut\IdCard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class agreeMentOutController extends Controller
{
    //
    /**
     * 合同签署
     */
    public function push(Request $request, IdCard $card, Harry $harry, AppUserInfoOut $appUserInfo, HarryAgreementOut $harryAgreement)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'name' => 'required',
                'citizenship' => 'required',
                'identityId' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

//            if ($arrRequest['citizenship'] <> 0 && $arrRequest['citizenship'] <> 4) {
//                return $this->getInfoResponse('4004', '类型错误！');
//            }

            if (Cache::has('harry_out__agreement_create_' . $arrRequest['app_id'])) {
                return $this->getInfoResponse('2005', '操作太频繁！请稍候再试r...');
            }
            Cache::put('harry_out__agreement_create_' . $arrRequest['app_id'], 1, 0.5);

            if ($arrRequest['citizenship'] == 0) {
                //身份证验证
                $res = $card->isChinaIDCard($arrRequest['identityId']);
                if (!$res) {
                    return $this->getInfoResponse('4004', '身份证号码错误！');
                }
            }

            $app_user_info = $appUserInfo->getUserById($arrRequest['app_id']);
            if (empty($app_user_info)) {
                return $this->getInfoResponse('4004', '用户错误！');
            }
            $is_agreement = $harryAgreement->where(['app_id' => $arrRequest['app_id']])->first();
            if (!empty($is_agreement)) {
                return $this->getInfoResponse('4004', '提交过一次了！');
            }

            $serialNo = 'wh' . date('YmdHis', time()) . uniqid();
            $res_res = $harry->put($serialNo, $arrRequest['name'], $app_user_info->phone, $arrRequest['citizenship'], $arrRequest['identityId']);

            if ($res_res['return_code'] <> 'T') {
                return $this->getInfoResponse('1111', $res_res['return_message'] . $res_res['content']);
            }

            $harryAgreement->updateOrCreate([
                'app_id' => $arrRequest['app_id']
            ], [
                'app_id' => $arrRequest['app_id'],
                'name' => $arrRequest['name'],
                'serialNo' => $serialNo,
                'phone' => $app_user_info->phone,
                'citizenship' => $arrRequest['citizenship'],
                'identityId' => $arrRequest['identityId'],
            ]);

            return $this->getResponse('提交成功！请等待审核');

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 合同签署回调
     */
    public function callBack(Request $request, HarryAgreementCallBackOut $harryAgreementCallBack, HarryAgreementOut $harryAgreement)
    {
        $data = $request->getContent();

        $data = json_decode($data, true);


        //存下回调信息
        $harryAgreementCallBack->create([
            'companyNo' => empty($data['content']['companyNo']) ? 0 : $data['content']['companyNo'],
            'serialNo' => empty($data['content']['serialNo']) ? 0 : $data['content']['serialNo'],
            'contractResultNo' => empty($data['content']['contractResultNo']) ? 0 : $data['content']['contractResultNo'],
            'contractUrl' => empty($data['content']['contractUrl']) ? 0 : $data['content']['contractUrl'],
            'return_code' => empty($data['return_code']) ? 0 : $data['return_code'],
            'return_message' => empty($data['return_message']) ? 0 : $data['return_message'],
            'content' => empty(json_encode($data['content'])) ? 0 : json_encode($data['content']),
        ]);
        //

        if ($data['return_code'] == 'T') {
//            var_dump($data['content']['serialNo']);//更新的流水订单号
            $harryAgreement->where(['serialNo' => $data['content']['serialNo']])->update([
                'is_callback' => 1
            ]);
            //特殊更新
            $need = $harryAgreement->where(['serialNo' => $data['content']['serialNo']])->first();
            if (!empty($need)) {
                $harryAgreement->where(['identityId' => $need->identityId])->update([
                    'is_callback' => 1
                ]);
            }
        }

        return 'success';
    }

    /**
     * 校验是否已经签过合同
     */
    public function checkIsCall(Request $request, HarryAgreementOut $harryAgreement)
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


            $is_agreement = $harryAgreement->where(['app_id' => $arrRequest['app_id']])->first();
            if (empty($is_agreement)) {
                return $this->getInfoResponse('4410', '从未提交过');
            }

            if ($is_agreement->is_callback <> 1) {
                return $this->getInfoResponse('4411', '未通过签约');
            }

            return $this->getResponse($is_agreement);


        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
