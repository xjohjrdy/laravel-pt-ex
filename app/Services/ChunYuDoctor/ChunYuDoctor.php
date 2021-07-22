<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/2
 * Time: 16:13
 */

namespace App\Services\ChunYuDoctor;

use GuzzleHttp\Client;

class ChunYuDoctor
{
    protected $partner = 'putao';
    protected $key = 'Ps6IgWBvbxDoRdrK';
    protected $url = 'https://test.chunyu.me';


    /*
     * 春雨医生账号注册1 123456
     */
    public function login($app_id, $password, $lon = '', $lat = '')
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $login_url = $this->url . '/cooperation/server/login';
        $post_api_data = [
            'user_id' => $app_id,          #用户唯一标识
            'password' => $password,       #密码
            'lon' => $lon,                 #经度 可无
            'lat' => $lat,                 #纬度 可无
            'partner' => $this->partner,   #合作方标识
            'sign' => $sign,               #签名
            'atime' => $atime,             #当前签名时间戳
        ];
        $login_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $login_url, $login_data);
        return (string)$res_login_data->getBody();

    }

    /*
     * 找医生 b092c63582871cc0
     */
    public function getClinicDoctors($app_id, $clinic_no, $famous_doctor, $start_num, $count, $province = '', $city = '', $service_type = '')
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/doctor/get_clinic_doctors';
        $post_api_data = [
            /**
             * 一次查询只能提交一个科室的对应编号
             * '1':妇科,  '2':儿科,  '3':内科,  '4':皮肤性病科,
             * '6':营养科,  '7':骨伤科,  '8':男科,  '9':外科,
             * '11':肿瘤及防治科,  '12':中医科,  '13':口腔颌面科,
             * '14':耳鼻咽喉科,'15':眼科,  '16':整形美容科,
             * '17':精神心理科,  '21':产科,
             */
            'clinic_no' => $clinic_no,          #科室编号
            'famous_doctor' => $famous_doctor,  #是否筛选名医 可无 接受值:0:否, 1:是
            'partner' => $this->partner,        #合作方标识
            'sign' => $sign,                    #签名
            'user_id' => $app_id,               #用户唯一标识
            'atime' => $atime,                  #当前签名时间戳
            'start_num' => $start_num,          #开始数 用于支持翻页功能，从0开始计数，针对该参数返回结果里的0,20,40为顺序号，非分页页码
            'count' => $count,                  #为单次请求获取的总数量，最大是20，历次获取每个科室最多累计200
            'province' => $province,            #省份 可无
            'city' => $city,                    #城市 可无
            'service_type' => $service_type,    #服务类型 可无 不填为默认获取开通图文服务的医生，值为inquiry表示获取开通电话服务的医生
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 推荐医生
     */
    public function getRecommendedDoctors($app_id, $ask)
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/doctor/get_recommended_doctors';
        $post_api_data = [
            'ask' => $ask,                      #患者首问字数请限制在10-500字
            'partner' => $this->partner,        #合作方标识
            'sign' => $sign,                    #签名
            'user_id' => $app_id,               #用户唯一标识
            'atime' => $atime,                  #当前签名时间戳
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 搜索医生
     */
    public function searchDoctor($app_id, $query_text, $page = '', $province = '', $city = '')
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/doctor/search_doctor/';
        $post_api_data = [
            'partner' => $this->partner,        #合作方标识
            'sign' => $sign,                    #签名
            'user_id' => $app_id,               #用户唯一标识
            'atime' => $atime,                  #当前签名时间戳
            'query_text	' => $query_text,       #搜索词（症状，疾病，医院，科室，医生名）
            'page	' => $page,                 #页码 可空 取值范围1-10
            'province	' => $province,         #省份 可空
            'city	' => $city,                 #城市 可空
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 春雨医生创建定向问题 442985607 442982757
     */
    public function createOrientedProblem($app_id, $doctor_ids, $content_list, $price)
    {
        $content = json_encode($content_list);
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $create_oriented_problem_url = $this->url . '/cooperation/server/problem/create_oriented_problem/';
        $post_api_data = [
            'doctor_ids' => $doctor_ids,     #购买的医生列表 使用#进行连接多个医生，不能有空格
            'content' => $content,                  #首次提问内容
            'partner' => $this->partner,            #合作方标识
            'partner_order_id' => uniqid(),    #合作方支付ID 需要是唯一标识的支持字母与数字组合
            'price' => $price,                         #订单价格 单位为分
            'user_id' => $app_id,                   #用户唯一标识
            'sign' => $sign,                        #签名
            'atime' => $atime,                      #当前签名时间戳
        ];
        $create_oriented_problem_url_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_create_oriented_problem_data = $client->request('POST', $create_oriented_problem_url, $create_oriented_problem_url_data);
        return (string)$res_create_oriented_problem_data->getBody();

    }

    /*
     * 付费问题退款
     */
    public function refund($app_id, $problem_id)
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/problem/refund';
        $post_api_data = [
            'partner' => $this->partner,        #合作方标识
            'sign' => $sign,                    #签名
            'user_id' => $app_id,               #用户唯一标识
            'atime' => $atime,                  #当前签名时间戳
            'problem_id	' => $problem_id,       #问题id
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 获取医生电话信息
     */
    public function getDoctorPhoneInfo($app_id, $doctor_id)
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/phone/get_doctor_phone_info/';
        $post_api_data = [
            'partner' => $this->partner,        #合作方标识
            'sign' => $sign,                    #签名
            'user_id' => $app_id,               #用户唯一标识
            'atime' => $atime,                  #当前签名时间戳
            'doctor_id	' => $doctor_id,        #医生id
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 创建预约电话 159
     */
    public function createOrientedRrder($app_id, $content_list, $doctor_id, $minutes, $tel_no, $price, $inquiry_time)
    {
        $content = json_encode($content_list);
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/phone/create_oriented_order/';
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
            'content' => $content,           #电话补充描述内容
            'partner_order_id' => uniqid(),  #合作方支付ID
            'doctor_id' => $doctor_id,       #医生id
            'minutes' => $minutes,           #拨打时长 共有10，15，20，30四个时间长度，具体取决于医生是否配置了相应的时间长度
            'tel_no' => $tel_no,             #用户电话
            'price' => $price,               #订单价格 单位元
            'inquiry_time' => $inquiry_time, #预约时间 格式如"2018-01-28 09:30"
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 取消订单接口
     */
    public function userCancel($app_id, $service_id, $cancel_reason)
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/phone/user_cancel/';
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
            'service_id' => $service_id,     #电话订单id
            'cancel_reason' => $cancel_reason#取消原因
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 用户追问
     */
    public function addContent($app_id, $service_id, $content_list)
    {
        $content = json_encode($content_list);
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/phone/add_content/';
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
            'service_id' => $service_id,     #电话订单id
            'content' => $content            #提问内容
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
    * 用户评价
    */
    public function assess($app_id, $service_id, $assess_info, $remark = '')
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/phone/assess/';
        /**评语信息
         * {
         * "bad": {
         * "1302": '不专业',
         * "1101": '不友好',
         * "1303": '没帮助',
         * "1301": '听不懂',
         * "1201": '信号不好',
         * "1203": '接通了没声音',
         * "1202": '没讲完就中断了',
         * "1204": '不是医生本人接电话',}
         * "good": {
         * "2101": '希望更有耐心',
         * "2102": '希望态度更友好',
         * "2201": '信号不好',
         * "2301": '希望讲得更透彻',
         * "2103": '没讲完就中断了',}
         * "best": {
         * "3101": '态度非常好',
         * "3102": '讲解很清楚',
         * "3103": '很有耐心',
         * "3301": '非常专业认真',
         * "3302": '意见很有帮助',
         * "3104": '很细心',}
         * }
         */
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
            'service_id' => $service_id,     #电话订单id
            'assess_info' => $assess_info,   #评语信息 结构如'{"level": "best", "tag_keys":["3201", "3102"]}'
            'remark' => $remark              #评语 可空
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 电话详情接口
     */
    public function phoneDetail($app_id, $service_id)
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/phone/detail/';
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
            'service_id' => $service_id,     #电话订单id
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 获取急诊图文信息
     */
    public function getEmergencyGraphInfo($app_id, $content_list)
    {
        $content = json_encode($content_list);
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/problem/get_emergency_graph_info/';
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
            'content' => $content            #问题内容
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 创建急诊问题 442985616
     */
    public function createEmergencyGraph($app_id, $content_list, $clinic_no)
    {
        $content = json_encode($content_list);
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/problem/create_emergency_graph/';
        $post_api_data = [
            'partner' => $this->partner,              #合作方标识
            'sign' => $sign,                          #签名
            'user_id' => $app_id,                     #用户唯一标识
            'atime' => $atime,                        #当前签名时间戳
            'content' => $content,                    #问题内容
            'partner_order_id' => uniqid(),           #合作方支付ID
            'clinic_no' => $clinic_no                 #科室id
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 获取快捷电话信息
     */
    public function getFastPhoneInfo($app_id)
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/phone/get_fast_phone_info/';
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 创建快捷电话 523
     */
    public function createFastPhoneOrder($app_id, $clinic_no, $phone)
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/phone/create_fast_phone_order/';
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
            'partner_order_id' => uniqid(),  #合作方支付ID
            'clinic_no' => $clinic_no,       #科室号
            'phone' => $phone,               #用手机号
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 我的提问历史
     */
    public function myListProblem($app_id, $start_num, $count)
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $get_clinic_doctors_url = $this->url . '/cooperation/server/problem/list/my';
        $post_api_data = [
            'partner' => $this->partner,     #合作方标识
            'sign' => $sign,                 #签名
            'user_id' => $app_id,            #用户唯一标识
            'atime' => $atime,               #当前签名时间戳
            'start_num' => $start_num,       #开始数  用于支持翻页功能,从 0 开始计数
            'count' => $count,               #每次取的问题数 最高200
        ];
        $clinic_doctors_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];

        $res_get_clinic_doctors_data = $client->request('POST', $get_clinic_doctors_url, $clinic_doctors_data);
        return (string)$res_get_clinic_doctors_data->getBody();
    }

    /*
     * 问题追问
     */
    public function create($app_id, $problem_id, $content_list)
    {
        $content = json_encode($content_list);
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $create_url = $this->url . '/cooperation/server/problem_content/create';
        $post_api_data = [
            'user_id' => $app_id,           #用户唯一标识
            'partner' => $this->partner,    #合作方标识
            'problem_id' => $problem_id,    #问题id
            'content' => $content,          #可以传递 patient_meta 之外的其余三种类型的contentItem。
            'sign' => $sign,                #签名
            'atime' => $atime,              #当前签名时间戳
        ];
        $create_url_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_create_oriented_problem_data = $client->request('POST', $create_url, $create_url_data);
        return (string)$res_create_oriented_problem_data->getBody();

    }

    /*
     * 问题详情
     */
    public function detail($app_id, $problem_id, $last_content_id = '')
    {
        $partner_key = $this->key;
        $atime = time();
        $sign = substr(md5($partner_key . $atime . $app_id), 8, 16);
        $client = new Client();
        $detail_url = $this->url . '/cooperation/server/problem/detail';
        $post_api_data = [
            'user_id' => $app_id,                        #用户唯一标识
            'partner' => $this->partner,                 #合作方标识
            'problem_id' => $problem_id,                 #问题id
            'sign' => $sign,                             #签名
            'atime' => $atime,                           #当前签名时间戳
            'last_content_id' => $last_content_id,       #最后一个回复编号 可空 会返回所有大于此编号的回复列表
        ];
        $detail_url_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_create_oriented_problem_data = $client->request('POST', $detail_url, $detail_url_data);
        return (string)$res_create_oriented_problem_data->getBody();

    }

    /*
     * 健康之路 预约挂号首页
     */
    public function healthBzgh($point, $thirdPartyUserId, $appId, $sourceType)
    {
        $thirdPartyUserId = $this->encodeId($thirdPartyUserId);
        $bzgh_url = "https://wxauth.yihu.com/apiweb/tp.html?point={$point}&thirdPartyUserId={$thirdPartyUserId}&appId={$appId}&sourceType={$sourceType}";

        return $bzgh_url;

    }

    /*
     * 编码id成健康之路英文登陆账号
     */
    public function encodeId($t_id)
    {
        $key = 'qwertyuiopasdfghjklzxcvbnm';
        $code_id = '0000000';
        for ($i = 6; $i >= 0; $i--) {
            $code_id[$i] = $key[$t_id % 26];
            $t_id = intval($t_id / 26);
        }
        if ($t_id) {
            return false;
        }
        return $code_id;
    }

    /*
     * 解码健康之路英文登陆账号成id
     */
    public function decodeId($t_co)
    {
        $key = 'qwertyuiopasdfghjklzxcvbnm';
        $t_id = 0;
        for ($i = 6; $i >= 0; $i--) {
            $t_id += strpos($key, $t_co[$i]) * pow(26, 6 - $i);
        }
        return $t_id;
    }


}