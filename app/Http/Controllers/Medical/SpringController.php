<?php

namespace App\Http\Controllers\Medical;

use App\Entitys\App\MedicalSpringApartment;
use App\Entitys\App\MedicalSpringUserInfo;
use App\Exceptions\ApiException;
use App\Services\ChunYuDoctor\ChunYuDoctor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SpringController extends Controller
{
    /**
     * get chat room
     */
    public function getChat(Request $request, MedicalSpringApartment $medicalSpringApartment)
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

            $res = $medicalSpringApartment->getApartment();

            return $this->getResponse($res);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * when user use search can search doctor ,but this function no use for word
     */
    public function getDoctorBySearch(Request $request, ChunYuDoctor $chunYuDoctor)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'ask' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $data = [
                'count' => 0,
                'now_page' => $arrRequest['page'],
                'doctors' => [],
            ];
            $res = $chunYuDoctor->getRecommendedDoctors($arrRequest['app_id'], $arrRequest['ask']);

            if (empty($res)) {
                return $this->getInfoResponse('5005', '信息获取失败！请稍后重试');
            }

            $res = json_decode($res, true);

            if ($res['error'] == 0) {
                $data['count'] = count($res['doctors']);
                $data['doctors'] = $res['doctors'];
            }

            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * word must is special doctor name or sickness name no user anything
     */
    public function getDoctorByWord(Request $request, ChunYuDoctor $chunYuDoctor)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'query_text' => 'required',
                'page' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $data = [
                'count' => 0,
                'now_page' => $arrRequest['page'],
                'doctors' => [],
            ];
            $res = $chunYuDoctor->searchDoctor($arrRequest['app_id'], $arrRequest['query_text'], $arrRequest['page']);
            if (empty($res)) {
                return $this->getInfoResponse('5005', '信息获取失败！请稍后重试');
            }
            $res = json_decode($res, true);

            if ($res['error'] == 0) {
                $data['count'] = count($res['doctors']);
                $data['now_page'] = $arrRequest['page'];
                $data['doctors'] = $res['doctors'];
            }

            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * this for one apartment
     */
    public function getDoctorByApartment(Request $request, ChunYuDoctor $chunYuDoctor)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'clinic_no' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $data = [
                'count' => 0,
                'now_page' => $arrRequest['page'],
                'doctors' => [],
            ];

            $start_num = empty($arrRequest['page']) ? 0 : $arrRequest['page'] - 1;
            $count = empty($arrRequest['count']) ? 10 : $arrRequest['count'];

            $res = $chunYuDoctor->getClinicDoctors($arrRequest['app_id'], $arrRequest['clinic_no'], '0', $start_num * $count, $count);
            if (empty($res)) {
                return $this->getInfoResponse('5005', '信息获取失败！请稍后重试');
            }
            $res = json_decode($res, true);

            if ($res['error'] == 0) {
                $data['count'] = count($res['doctors']);
                $data['now_page'] = $start_num;
                $data['doctors'] = $res['doctors'];
            }

            return $this->getResponse($data);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * this is first one
     * @param Request $request
     * @param MedicalSpringUserInfo $medicalSpringUserInfo
     * @param ChunYuDoctor $chunYuDoctor
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function login(Request $request, MedicalSpringUserInfo $medicalSpringUserInfo, ChunYuDoctor $chunYuDoctor)
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
            $lon = 116.3;
            $lat = 39.9;
            if (!empty($arrRequest['lon'])) {
                $lon = $arrRequest['lon'];
            }
            if (!empty($arrRequest['lat'])) {
                $lat = $arrRequest['lat'];
            }

            $user_info = $medicalSpringUserInfo->getUserInfo($arrRequest['app_id']);

            if (empty($user_info)) {
                $res = $chunYuDoctor->login($arrRequest['app_id'], 'usynchs123', $lon, $lat);
                $res = json_decode($res, true);
                if ($res['error'] == 0) {
                    $medicalSpringUserInfo->addInfo([
                        'app_id' => $arrRequest['app_id'],
                        'password' => 'usynchs123',
                        'lon' => $lon,
                        'lat' => $lat,
                    ]);
                } else {
                    return $this->getInfoResponse('5000', var_export($res, true));
                }
            }

            return $this->getResponse('校验成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
