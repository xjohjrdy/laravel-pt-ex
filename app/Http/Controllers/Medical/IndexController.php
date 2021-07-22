<?php

namespace App\Http\Controllers\Medical;

use App\Entitys\App\MedicalSpringRainChat;
use App\Entitys\Xin\MedicalUser;
use App\Exceptions\ApiException;
use App\Services\ChunYuDoctor\ChunYuDoctor;
use App\Services\JPush\JPush;
use App\Services\ZhongKang\ZhongKangServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IndexController extends Controller
{
    /*
     * 新增我的档案
     */
    public function addFile(Request $request, MedicalUser $medicalUser)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
                'real_name' => 'required',
                'real_phone' => 'required',
                'id_card' => 'required',
                'real_sex' => 'required',
                'from_data' => 'required',
                'is_married' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];            #app_id
            $real_name = $arrRequest['real_name'];      #姓名
            $real_phone = $arrRequest['real_phone'];    #手机
            $id_card = $arrRequest['id_card'];          #身份证
            $real_sex = $arrRequest['real_sex'];        #性别
            $from_data = $arrRequest['from_data'];      #出生
            $is_married = $arrRequest['is_married'];    #婚否

            /***********************************/
            $pattern_account = '/^\d{4,20}$/i';
            if (!preg_match($pattern_account, $real_phone)) {
                return $this->getInfoResponse('1001', '您的手机号输入错误！');
            }
            $num_res = $medicalUser->where('app_id', $app_id)->count();
            if ($num_res > 14) {
                return $this->getInfoResponse('1002', '您新建的文档大于15！');
            }
            $medicalUser->app_id = $app_id;
            $medicalUser->real_name = $real_name;
            $medicalUser->real_phone = $real_phone;
            $medicalUser->id_card = $id_card;
            $medicalUser->real_sex = $real_sex;
            $medicalUser->from_data = $from_data;
            $medicalUser->is_married = $is_married;
            $medicalUser->save();

            return $this->getResponse("新增成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
    * 修改我的档案
    */
    public function updataFile(Request $request, MedicalUser $medicalUser)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'id' => 'required',
                'app_id' => 'required',
                'real_name' => 'required',
                'real_phone' => 'required',
                'id_card' => 'required',
                'real_sex' => 'required',
                'from_data' => 'required',
                'is_married' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $id = $arrRequest['id'];                    #id
            $real_name = $arrRequest['real_name'];      #姓名
            $real_phone = $arrRequest['real_phone'];    #手机
            $id_card = $arrRequest['id_card'];          #身份证
            $real_sex = $arrRequest['real_sex'];        #性别
            $from_data = $arrRequest['from_data'];      #出生
            $is_married = $arrRequest['is_married'];    #婚否

            /***********************************/
            $pattern_account = '/^\d{4,20}$/i';
            if (!preg_match($pattern_account, $real_phone)) {
                return $this->getInfoResponse('1001', '您的手机号输入错误！');
            }
            $res = $medicalUser->where('id', $id)->first();
            if ($res) {
                $res->real_name = $real_name;
                $res->real_phone = $real_phone;
                $res->id_card = $id_card;
                $res->real_sex = $real_sex;
                $res->from_data = $from_data;
                $res->is_married = $is_married;
                $res->save();
            } else {
                return $this->getInfoResponse('1002', '档案信息不存在！');
            }
            return $this->getResponse("修改成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到我的档案
     */
    public function getFile(Request $request, MedicalUser $medicalUser)
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

            $app_id = $arrRequest['app_id'];            #id

            /***********************************/
            $res = $medicalUser->where('app_id', $app_id)->get();
            foreach ($res as $v) {
                $v->age = $this->getAge($v->from_data);
                $v->from_data = date('Y-m-d H:i:s', $v->from_data);
            }

            return $this->getResponse($res);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 计算年龄 传入生日时间戳
     */
    public function getAge($birthday)
    {
        $byear = date('Y', $birthday);
        $bmonth = date('m', $birthday);
        $bday = date('d', $birthday);
        $tyear = date('Y');
        $tmonth = date('m');
        $tday = date('d');
        $age = $tyear - $byear;
        if ($bmonth > $tmonth || $bmonth == $tmonth && $bday > $tday) {
            $age--;
        }
        return $age;
    }

    /*
     * 删除我的档案
     */
    public function deleteFile(Request $request, MedicalUser $medicalUser)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
                'id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];    #用户id
            $id = $arrRequest['id'];            #档案id

            /***********************************/
            $res = $medicalUser->where(['id' => $id, 'app_id' => $app_id])->delete();
            if ($res) {
                return $this->getResponse('删除成功！');
            }
            return $this->getInfoResponse('1001', '删除失败！');
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 春雨医生得到我的提问历史
     */
    public function getIssueHistory(Request $request, ChunYuDoctor $chunYuDoctor)
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

            $app_id = $arrRequest['app_id'];                                                 #id
            $start_num = empty($arrRequest['start_num']) ? 0 : $arrRequest['start_num'] - 1; #用于翻页 重0开始
            $count = empty($arrRequest['count']) ? 10 : $arrRequest['count'];                #每页数据 最大200

            /***********************************/
            $res = $chunYuDoctor->myListProblem($app_id, $start_num * $count, $count);

            return $this->getResponse(json_decode($res, true));
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     *
     */
    public function getIndex()
    {
        $zhongkang = new ZhongKangServices();
        $city = $zhongkang->getCity();
        $units = $zhongkang->getIsvUnits();

        return $this->getResponse([
            'city' => json_decode($city, true),
            'units' => json_decode($units, true),
        ]);
    }

    /*
     * 健康之路H5
     */
    public function healthBzgh(Request $request, ChunYuDoctor $chunYuDoctor, MedicalUser $medicalUser)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
                'source_type' => Rule::in([1, 2]),
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $ssource_type = $arrRequest['source_type'];
            /***********************************/
            $point = 'gh';
            $appId = '9000825';
            $user_data = $medicalUser->where('app_id', $app_id)->first();
            if (empty($user_data)) {
                return ['code' => '-10000', "msg" => "档案信息未填写，请前往填写档案"];
            };
            $rss = $chunYuDoctor->healthBzgh($point, $app_id, $appId, $ssource_type);
            return $this->getResponse($rss);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 健康之路
     */
    public function healthJkgjqdb(Request $request, ChunYuDoctor $chunYuDoctor)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $ssource_type = 1;
            /***********************************/
            $point = 'jkgjqdb';
            $appId = '9000825';

            $rss = $chunYuDoctor->healthBzgh($point, $app_id, $appId, $ssource_type);
            return $this->getResponse($rss);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
    * 健康之路H5 临时增加
    */
    public function healthBzghT(Request $request, ChunYuDoctor $chunYuDoctor, MedicalUser $medicalUser)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
                'source_type' => Rule::in([1, 2]),
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $ssource_type = $arrRequest['source_type'];
            /***********************************/
            $point = 'ghjl';
            $appId = '9000825';
            $user_data = $medicalUser->where('app_id', $app_id)->first();
            if (empty($user_data)) {
                return ['code' => '-10000', "msg" => "账号信息不存在"];
            };
            $rss = $chunYuDoctor->healthBzgh($point, $app_id, $appId, $ssource_type);
            return $this->getResponse($rss);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 健康之路H5回调
     */
    public function healthBzghBack(ChunYuDoctor $chunYuDoctor, MedicalUser $medicalUser)
    {
        try {
            $arrRequest['thirdPartyUserId'] = \request()->input('thirdPartyUserId');   #第三方登录账号ID(必须是英文字符，且长度不能超过32位)
            $arrRequest['ts'] = \request()->input('ts');                                #时间戳（相对于1970-1-1的毫秒数）
            $arrRequest['sign'] = \request()->input('sign');                          #签名串，算法为：thirdPartyUserId的值+ts的值+appId+secre

            $rules = [
                'thirdPartyUserId' => 'required',
                'ts' => 'required',
                'sign' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                return ['code' => '-100002', "message" => "缺少必要参数"];
            }
            Storage::disk('local')->append('callback_document/the_road_to_health_h5.txt', var_export($arrRequest, true));
            /***********************************/
            $appId = '9000825';
            $secret = 'J41UTU6R5GJ9TE1MXWEX7QC572CRGP9YNWGGUMCB3Y7';
            $sige = strtolower(sha1($arrRequest['thirdPartyUserId'] . $arrRequest['ts'] . $appId . $secret));
            if ($sige != $arrRequest['sign']) {
                return ['code' => '-100001', "message" => "签名校验失败!"];
            }
            $app_id = $chunYuDoctor->decodeId($arrRequest['thirdPartyUserId']);
            $user_data = $medicalUser->where('app_id', $app_id)->first();
            if (empty($user_data)) {
                return ['code' => '-10000', "message" => "档案信息未填写，请前往填写档案"];
            };
            return [
                'code' => '10000',
                "message" => "请求成功",
                "userName" => $user_data->real_name,
                "tel" => $user_data->real_phone,
                "sex" => $user_data->real_sex,
                "cardNo" => $user_data->id_card
            ];
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                return ['code' => '-100003', "message" => $e->getMessage()];
            }
            return ['code' => '-100004', "message" => "网络开小差了！请稍后再试,错误信息：" . $e->getLine()];
        }
    }

    /*
     * 春雨医生图文回复回调
     */
    public function chunYuReplyBack(MedicalSpringRainChat $medicalSpringRainChat)
    {
        try {
            $arrRequest['problem_id'] = \request()->input('problem_id');   #问题id
            $arrRequest['user_id'] = \request()->input('user_id');         #用户id
            $arrRequest['atime'] = \request()->input('atime');             #时间戳
            $arrRequest['sign'] = \request()->input('sign');               #签名
            $arrRequest['content'] = \request()->input('content');         #医生回复的内容
            $arrRequest['doctor'] = \request()->input('doctor');           #医生信息

            $rules = [
                'user_id' => 'required',
                'problem_id' => 'required',
                'doctor' => 'required',
                'sign' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                return ['error' => '1001', "error_msg" => "缺少必要参数"];
            }

            file_put_contents('log_c.txt', var_export($arrRequest, true) . PHP_EOL, FILE_APPEND);
            /***********************************/
            $partner_key = 'Ps6IgWBvbxDoRdrK';
            $sign = substr(md5($partner_key . $arrRequest['atime'] . $arrRequest['problem_id']), 8, 16);

            if ($sign != $arrRequest['sign']) {
                return ['error' => '1002', "error_msg" => "签名校验失败!"];
            }
            $arr = [];
            $type_value = [
                'text' => 0,
                'audio' => 1,
                'image' => 2,
            ];
            $type_context = [
                'text' => 'text',
                'audio' => 'file',
                'image' => 'file',
            ];
            foreach (json_decode($arrRequest['content'], true) as $v) {
                @$arr = [
                    'problem_id' => $arrRequest['problem_id'],
                    'app_id' => $arrRequest['user_id'],
                    'doctor_id' => $arrRequest['doctor']['user_id'],
                    'context' => $v[$type_context[$v['type']]],
                    'type' => $type_value[$v['type']],
                    'from' => 1,
                ];
                $medicalSpringRainChat->doctorReply($arr);
            }
            return [
                'error' => '0',
                "error_msg" => "请求成功",
            ];
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                return ['error' => '1004', "error_msg" => $e->getMessage()];
            }
            return ['error' => '1005', "error_msg" => "网络开小差了！请稍后再试,错误信息：" . $e->getLine()];
        }
    }

    /*
     * 春雨医生图文关闭回调
     */
    public function chunYuShutBack(ChunYuDoctor $chunYuDoctor)
    {
        return [
            'error' => '0',
            "error_msg" => "请求成功",
        ];
        try {
            $arrRequest['user_id'] = \request()->input('user_id');             #用户id
            $arrRequest['problem_id'] = \request()->input('problem_id');       #问题id
            $arrRequest['msg'] = \request()->input('msg');                     #通知信息
            $arrRequest['atime'] = \request()->input('atime');                 #时间戳
            $arrRequest['sign'] = \request()->input('sign');                   #签名
            $arrRequest['status'] = \request()->input('status');               #问题状态 close 回答完毕后关闭 refund 问题退款
            $arrRequest['price'] = \request()->input('price');                 #退款金额

            $rules = [
                'user_id' => 'required',
                'problem_id' => 'required',
                'status' => 'required',
                'sign' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                return ['error' => '1001', "error_msg" => "缺少必要参数"];
            }
            /***********************************/
            $partner_key = 'Ps6IgWBvbxDoRdrK';
            $sign = substr(md5($partner_key . $arrRequest['atime'] . $arrRequest['problem_id']), 8, 16);

            if ($sign != $arrRequest['sign']) {
                return ['error' => '1002', "error_msg" => "签名校验失败!"];
            }
            file_put_contents('log_c.txt', var_export($arrRequest, true) . PHP_EOL, FILE_APPEND);

            return [
                'error' => '0',
                "error_msg" => "请求成功",
            ];
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                return ['error' => '1003', "error_msg" => $e->getMessage()];
            }
            return ['error' => '1004', "error_msg" => "网络开小差了！请稍后再试,错误信息：" . $e->getLine()];
        }
    }
}
