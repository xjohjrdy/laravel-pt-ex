<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/19
 * Time: 09:50
 */

namespace App\Services\KaDuoFen;


use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CardMaid;

class KaDuoFenServices
{
    protected $appkey = 'putaoliulanqi';
    protected $url = 'https://icardapp.icardfinancial.com/cm-front';

    /*
     * 查询单个应用信用卡订单
     */
    public function getOrderListBysupplier($dataFlag, $startDate, $endDate, $pageNo, $pageSize)
    {
        $salt = $this->appkey;
        $url = $this->url;
        $urlpath = '/b2b/api/getOrderListBysupplier';
        $Timestamp = time() . '000';
        $data = [
            'dataFlag' => $dataFlag,    #1-根据创建时间查询,2-根据更新时间查询
            'startDate' => $startDate,  #开始时间 如:2019-06-17T00:00:00.000+0800
            'endDate' => $endDate,      #结束时间(开始时间和结束时间的时间差控制在3天内) 如:2019-06-17T00:00:00.000+0800
            'pageNo' => $pageNo,        #第几页 从1开始
            'pageSize' => $pageSize,    #分页大小，最大值500
        ];
        $res = $this->generateSigns($urlpath, $Timestamp, $salt, $data, $url);
        return $res;
    }

    /*
     * 查询单个用户信用卡订单
     */
    public function getOrderListByOpenId($dataFlag)
    {
        $salt = $this->appkey;
        $url = $this->url;
        $urlpath = '/b2b/api/getOrderListByOpenId';
        $Timestamp = time() . '000';
        $data = [
            'openId' => $dataFlag,    #合作方客户号，即userId
        ];
        $res = $this->generateSigns($urlpath, $Timestamp, $salt, $data, $url);
        return $res;
    }

    /*
     * 查询信用卡详细信息
     */
    public function getCardDetail($id)
    {
        $salt = $this->appkey;
        $url = $this->url;
        $urlpath = '/b2b/api/getCardDetail';
        $Timestamp = time() . '000';
        $data = [
            'id' => $id,    #信用卡卡片id
        ];
        $res = $this->generateSigns($urlpath, $Timestamp, $salt, $data, $url);
        return $res;
    }

    /**
     * 卡多分POST请求公共类
     * @param $urlpath @地址后缀
     * @param $Timestamp @时间戳 毫秒级
     * @param $salt @默认appKey
     * @param $data @请求数据
     * @param $url @请求地址(前缀)
     * @return array|string  返回数组数据或异常错误。
     */
    function generateSigns($urlpath, $Timestamp, $salt, $data, $url)
    {
        ksort($data);
        $datas = json_encode($data);
        $string = $urlpath . "\n" . 'x-beisheng-auth-timestamp:' . $Timestamp;
        $stringToBeSigned = base64_encode(hash_hmac('sha1', $string, $salt, TRUE));
        if ($datas) {
            $code = $string . "\n" . $datas;
            $stringToBeSigned = base64_encode(hash_hmac('sha1', $code, $salt, TRUE));
        }
        $headers = array(
            'X-BEISHENG-Auth-Timestamp:' . $Timestamp,
            'authorization:BEISHENG putaoliulanqi:' . $stringToBeSigned,
            'Content-Type:application/json;charset=UTF-8',
            'X-AjaxPro-Method:ShowList',
            'Content-Length:' . strlen($datas),
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . $urlpath);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        return $res;
    }


    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * @return 签名结果
     */
    public function rsaSign($pwd)
    {
        $st = json_encode($pwd);
        $pri = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDNLJq3TZ8UOhFYGYtuZ+5qBBPK
voL0ihaQsv7aqP6NGBpF1TlNLE2bT7MxgRtOrv+EnaCe0E1MLoAH5wW2NpqUmx/z
U3LIsh4yNOnl7RPkp/vrmMjPRoiPsbjcr+JkqHtNNWN4w3NNEi5Qa3YIQynTjEcW
yo7MbEDJ4OLqZlrvzwIDAQAB
-----END PUBLIC KEY-----';
        $pk = $pri;
        openssl_public_encrypt($st, $encrypt_data, $pk);

        return base64_encode($encrypt_data);
    }

    /*
     * 本月预估收益
     */
    public function cardEstimateEarnings($app_id, $time)
    {
        $obj_card_maid = new CardMaid();
        return $obj_card_maid->where('app_id', $app_id)
            ->whereBetween('created_at', $time)
            ->sum('maid_ptb');
    }

    /*
     * 直属预估收益
     */
    public function cardDirectlyEstimateEarnings($app_id)
    {
        $obj_card_maid = new CardMaid();
        $obj_user = new AppUserInfo();
        $all_data = $obj_card_maid->where('app_id', $app_id)
            ->where('type', 2)
            ->get();
        $ptb_directly = 0;
        foreach ($all_data as $v) {
            $from_app_id = $v->from_app_id;
            $parent_id = $obj_user->where('id', $from_app_id)->value('parent_id');
            if ($app_id == $parent_id){
                $ptb_directly += $v->maid_ptb;
            }
        }
        return $ptb_directly;
    }

    /*
     * 团队预估收益
     */
    public function cardTeamEstimateEarnings($app_id)
    {
        $obj_card_maid = new CardMaid();
        return $obj_card_maid->where('app_id', $app_id)
            ->where('type', 2)
            ->sum('maid_ptb');
    }

    /*
     * 核卡成功人数
     */
    public function cardSucceedNumber($app_id)
    {
        $obj_card_maid = new CardMaid();
        return $obj_card_maid->where('app_id', $app_id)
            ->where('type', 2)
            ->count();
    }

    /*
     * 信用卡总预估收益
     */
    public function cardAllEstimateEarnings($app_id)
    {
        $obj_card_maid = new CardMaid();
        return $obj_card_maid->where('app_id', $app_id)
            ->sum('maid_ptb');
    }
}