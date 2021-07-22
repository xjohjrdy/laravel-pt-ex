<?php

namespace App\Http\Controllers\Certificate;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserWork;
use App\Entitys\Ad\UserWorkRecord;
use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    /**
     *
     * get {"uid":"1"}
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, UserWork $userWork, UserWorkRecord $userWorkRecord)
    {
        try {
            //仅用于测试兼容旧版
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('uid', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $works = $userWork->getWorkByUid($arrRequest['uid']);
            $is_d = 0;
            $is_h = 0;
            $is_new_put_d = 0;
            $is_new_put_h = 0;
            $is_new_put_d_info = null;
            $is_new_put_h_info = null;
            foreach ($works as $key => $work) {
                $content = preg_replace_callback('#s:(\d+):"(.*?)";#s', function ($match) {
                    return 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
                }, $work['content']);
                $arr = unserialize($content);
                if ($arr['val'][0] == "申请代理商证书") {
                    $works[$key]['type'] = 1;
                } elseif ($arr['val'][0] == "申请优质转正证书") {
                    $works[$key]['type'] = 2;
                } else {
                    $works[$key]['type'] = 0;
                }
                $works[$key]['real_name'] = $arr['val'][1];
                $user_work_record = $userWorkRecord->getRecordByOrderId($work['id']);
                if ($user_work_record) {
                    $works[$key]['pics'] = $user_work_record->pics;
                } else {
                    $works[$key]['pics'] = '';
                }
                if ($works[$key]['type'] == 1 && $works[$key]['pics'] <> '') {
                    $is_d = 1;
                }
                if ($works[$key]['type'] == 2 && $works[$key]['pics'] <> '') {
                    $is_h = 1;
                }

                //新需求，新处理
                if ($works[$key]['type'] == 1 && $works[$key]['pics'] <> '') {
                    if (empty($user_work_record->new_put)) {
                        $appUserInfo = new AppUserInfo();
                        $adUserInfo = new AdUserInfo();
                        $ad_user = $adUserInfo->getUserById($arrRequest['uid']);
                        $user = $appUserInfo->getUserById($ad_user->pt_id);
                        if (empty($user->real_name)) {
                            return $this->getInfoResponse('4004', '您未填写真实姓名！');
                        }
                        $is_new_put_d_info = [
                            'pic_id' => $user_work_record->id,
                            'h_time' => date('Y.m.d', $user_work_record->add_time),
                            'h_id' => $user_work_record->deal_uid,
                            'real_name' => $user->real_name,
                            'phone' => $user->phone,
                        ];
                    } else {
                        $is_new_put_d = 1;
                    }
                }
                if ($works[$key]['type'] == 2 && $works[$key]['pics'] <> '') {
                    if (empty($user_work_record->new_put)) {
                        $appUserInfo = new AppUserInfo();
                        $adUserInfo = new AdUserInfo();
                        $ad_user = $adUserInfo->getUserById($arrRequest['uid']);
                        $user = $appUserInfo->getUserById($ad_user->pt_id);
                        if (empty($user->real_name)) {
                            return $this->getInfoResponse('4004', '您未填写真实姓名！');
                        }
                        $is_new_put_h_info = [
                            'pic_id' => $user_work_record->id,
                            'h_time' => date('Y.m.d', $user_work_record->add_time),
                            'h_id' => $user_work_record->deal_uid,
                            'real_name' => $user->real_name,
                            'phone' => $user->phone,
                        ];
                    } else {
                        $is_new_put_h = 1;
                    }
                }
            };

            return $this->getResponse([
                'works' => $works,
                'is_d' => $is_d,
                'is_h' => $is_h,
                'is_new_put_d' => $is_new_put_d,
                'is_new_put_h' => $is_new_put_h,
                'is_new_put_d_info' => $is_new_put_d_info,
                'is_new_put_h_info' => $is_new_put_h_info,
            ]);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     *
     * 申请证书第一步
     * post {"user_id":"1","uid":"1","real_name":"测试","pt_id":"1","phone":"13194089498","type":"1"}
     * return 授权人名，授权合同号，授权日期
     * 1:代理商，2“优质转正
     * @param Request $request
     * @param AppUserInfo $appUserInfo
     * @param AdUserInfo $adUserInfo
     * @param UserWork $userWork
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, AppUserInfo $appUserInfo, AdUserInfo $adUserInfo, UserWork $userWork, UserWorkRecord $userWorkRecord, CommonFunction $commonFunction)
    {
        try {
            //仅用于测试兼容旧版
//        if ($request->header('data')) {
//            $request->data = $request->header('data');
//        }
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('user_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            $type = 0;
            $name = '';
            if ($arrRequest['type'] == 1) {
                $type = 23;
                $name = '申请代理商证书';
            }
            if ($arrRequest['type'] == 2) {
                $type = 24;
                $name = '申请优质转正证书';
            }
            $user = $appUserInfo->getUserById($arrRequest['user_id']);
            $ad_user = $adUserInfo->getUserById($arrRequest['uid']);
            if (!$user || !$ad_user) {
                throw new ApiException('用户账号异常！', '4000');
            }
            if ($arrRequest['real_name'] <> $user->real_name) {
                return $this->getInfoResponse('4001', '注意：不要代替他人申请，请填写所绑定的支付宝处的真实姓名，如果还未设置支付宝，先前往app“设置”处填写支付宝后重新申请证书。');
            }
            if ($arrRequest['phone'] <> $user->phone) {
                return $this->getInfoResponse('4002', '注意：不要代替他人申请，请填写登录电话。');
            }
            if ($arrRequest['pt_id'] <> $user->id) {
                return $this->getInfoResponse('4003', '注意：不要代替他人申请，您的葡萄id不匹配,请前往‘我的’查看id');
            }
            if ($ad_user->groupid < $type) {
                return $this->getInfoResponse('4004', '您的等级无法满足申请条件');
            }
            $works = $userWork->getWorkByUid($arrRequest['uid']);
            $is_h = 0;
            $id_d = 0;
            foreach ($works as $key => $work) {
                $content = preg_replace_callback('#s:(\d+):"(.*?)";#s', function ($match) {
                    return 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
                }, $work['content']);
                $arr = unserialize($content);
                if ($arr['val'][0] == "申请代理商证书") {
                    $id_d = 1;
                }
                if ($arr['val'][0] == "申请优质转正证书") {
                    $is_h = 1;
                }
            };
            if ($is_h && $arrRequest['type'] == 2) {
                return $this->getInfoResponse('4005', '您的已经申请过优质转正证书');
            }
            if ($id_d && $arrRequest['type'] == 1) {
                return $this->getInfoResponse('4006', '您的已经申请过超级用户证书');
            }
            $arr_work = [
                "name" => [
                    0 => "申请证书",
                    1 => "姓名",
                    2 => "您的ID号",
                    3 => "手机号",
                ],
                "val" => [
                    0 => $name,
                    1 => $user->real_name,
                    2 => $user->id,
                    3 => $user->phone,
                ],
                "type" => [
                    0 => "select",
                    1 => "input",
                    2 => "input",
                    3 => "input",
                ]
            ];
            $deal_time = date('Y.m.d', time());
            $deal_uid = date('Ymd', time()) . $commonFunction->randomKeys(5);
            $work_id = $userWork->addNewWork($arrRequest['uid'], $ad_user->username, $arr_work);
            $pic_id = $userWorkRecord->addRecord($arrRequest['uid'], $work_id, $deal_uid);

            return $this->getResponse(['pic_id' => $pic_id, 'h_time' => $deal_time, 'h_id' => $deal_uid, 'real_name' => $user->real_name]);

        } catch (\Exception $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     *
     * {"uid":"1","pic_id":"5296","pics":"123"}
     * @param Request $request
     * @param $id
     * @param UserWorkRecord $userWorkRecord
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, UserWorkRecord $userWorkRecord)
    {
        try {
            //仅用于测试兼容旧版
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('pic_id', $arrRequest) || !array_key_exists('uid', $arrRequest) || !array_key_exists('pics', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $work_record = $userWorkRecord->getById($arrRequest['pic_id']);
            if ($work_record) {
                if ($work_record->uid <> $arrRequest['uid']) {
                    throw new ApiException('您没有权利操作这个！', '4000');
                }
            }

            if (empty($arrRequest['new_put'])) {
                $userWorkRecord->updateById($arrRequest['pic_id'], $arrRequest['pics']);
            } else {
                $userWorkRecord->where(['id' => $arrRequest['pic_id']])->update(['pics' => $arrRequest['pics'], 'new_put' => '1']);
            }


            return $this->getResponse('更新成功！');
        } catch (\Exception $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
