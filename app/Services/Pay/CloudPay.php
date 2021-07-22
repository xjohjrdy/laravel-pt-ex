<?php


namespace App\Services\Pay;


use App\Exceptions\ApiException;
use com\unionpay\acp\sdk\AcpService;
use com\unionpay\acp\sdk\LogUtil;
use com\unionpay\acp\sdk\SDKConfig;
use function Symfony\Component\VarDumper\Dumper\esc;

/**
 * 云闪付支付服务类
 * Class CloudPay
 * @package App\Services\Pay
 *
 * 重要：联调测试时请仔细阅读注释！
 *
 * 产品：跳转网关支付产品<br>
 * 交易：交易状态查询交易：只有同步应答 <br>
 * 日期： 2015-09<br>
 * 版权： 中国银联<br>
 * 说明：以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己需要，按照技术文档编写。该代码仅供参考，不提供编码性能及规范性等方面的保障<br>
 * 该接口参考文档位置：open.unionpay.com帮助中心 下载  产品接口规范  《网关支付产品接口规范》，<br>
 *              《平台接入接口规范-第5部分-附录》（内包含应答码接口规范，全渠道平台银行名称-简码对照表）<br>
 * 测试过程中的如果遇到疑问或问题您可以：1）优先在open平台中查找答案：
 *                                    调试过程中的问题或其他问题请在 https://open.unionpay.com/ajweb/help/faq/list 帮助中心 FAQ 搜索解决方案
 *                             测试过程中产生的7位应答码问题疑问请在https://open.unionpay.com/ajweb/help/respCode/respCodeList 输入应答码搜索解决方案
 *                           2） 咨询在线人工支持： open.unionpay.com注册一个用户并登陆在右上角点击“在线客服”，咨询人工QQ测试支持。
 * 交易说明： 1）对前台交易发起交易状态查询：前台类交易建议间隔（5分、10分、30分、60分、120分）发起交易查询，如果查询到结果成功，则不用再查询。（失败，处理中，查询不到订单均可能为中间状态）。也可以建议商户使用payTimeout（支付超时时间），过了这个时间点查询，得到的结果为最终结果。
 *        2）对后台交易发起交易状态查询：后台类资金类交易同步返回00，成功银联有后台通知，商户也可以发起 查询交易，可查询N次（不超过6次），每次时间间隔2N秒发起,即间隔1，2，4，8，16，32S查询（查询到03，04，05继续查询，否则终止查询）。
 *                                     后台类资金类同步返03 04 05响应码及未得到银联响应（读超时）需发起查询交易，可查询N次（不超过6次），每次时间间隔2N秒发起,即间隔1，2，4，8，16，32S查询（查询到03，04，05继续查询，否则终止查询）。
 */
class CloudPay
{
    protected $config = [
        "callback" => "http://api.36qq.com/api/cloud_notify",
        "merId" => "822391057340118"

    ];

    /**
     * 用户支付接口请求
     * @param string $orderNo 商户订单号
     * @param integer $money 交易金额，单位为分
     * @param string $subject 订单描述
     * @param string $txnTime 订单发送时间，YYYYMMDDhhmmss格式
     * @return string 订单流水号
     */
    public function pay($orderNo, $money, $txnTime, $subject)
    {
        $logger = LogUtil::getLogger();
        $params = [
            'version' => SDKConfig::getSDKConfig()->version,
            'encoding' => 'utf-8',
            'txnType' => '01',
            'txnSubType' => '01',
            'bizType' => '000201',
            'backUrl' => $this->config['callback'],
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,
            'channelType' => '08',
            'accessType' => '0',
            'currencyCode' => '156',
            'orderDesc' => $subject,
            'merId' => $this->config['merId'],
            'orderId' => $orderNo,
            'txnTime' => $txnTime,
            'txnAmt' => $money * 100,
        ];
        AcpService::sign($params);
        $url = SDKConfig::getSDKConfig()->appTransUrl;


        $result_arr = AcpService::post($params, $url);
        if (count($result_arr) <= 0) {
            return "";
        }
        if (AcpService::validate($result_arr)) {
            if($result_arr['respCode'] != '00'){
                $logger->LogInfo('流水号获取失败：' . var_export($result_arr, true));
            }
            return @$result_arr['tn'];
        } else {
            return '';
        }
    }

    /**
     * 订单交易状态查询接口
     * @param $orderNo string 订单号
     * @param $time string 订单交易时间
     * @param integer $money 交易金额，单位为分
     * @return string
     */
    public function singleQuery($orderNo)
    {
        $logger = LogUtil::getLogger();
        $return_obj = [];
        $params = [
            'version' => SDKConfig::getSDKConfig()->version,
            'encoding' => 'utf-8',
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,
            'txnType' => '00',
            'txnSubType' => '00',
            'bizType' => '000000',
            'accessType' => '0',
            'channelType' => '08',
            'orderId' => $orderNo,
            'merId' => $this->config['merId'],
            'txnTime' => date('YmdHis'),
        ];

        AcpService::sign($params);
        $url = SDKConfig::getSDKConfig()->singleQueryUrl;


        $result_arr = AcpService::post($params, $url);
        if (count($result_arr) <= 0) {
            $return_obj['msg'] = '没收到200应答的情况！';
            $return_obj['result'] = false;
            $return_obj['code'] = '';
        }
        if (AcpService::validate($result_arr)) {
            $logger->LogInfo('----订单支付回调状态查询结果->订单号：' . $orderNo . 'code:' . $result_arr["respCode"] . @$result_arr["origRespCode"]);
            if ($result_arr["respCode"] == "00") {
                if ($result_arr["origRespCode"] == "00") {
                    $return_obj['msg'] = '交易成功！';
                    $return_obj['result'] = true;
                    $return_obj['code'] = $result_arr["respCode"] . $result_arr["origRespCode"];
                } else if ($result_arr["origRespCode"] == "03"
                    || $result_arr["origRespCode"] == "04"
                    || $result_arr["origRespCode"] == "05") {
                    $return_obj['msg'] = '交易处理中，请稍微查询。';
                    $return_obj['result'] = false;
                    $return_obj['code'] = $result_arr["respCode"] . $result_arr["origRespCode"];
                } else {
                    $return_obj['msg'] = $result_arr["origRespMsg"];
                    $return_obj['result'] = false;
                    $return_obj['code'] = $result_arr["respCode"] . $result_arr["origRespCode"];
                }
            } else if ($result_arr["respCode"] == "03"
                || $result_arr["respCode"] == "04"
                || $result_arr["respCode"] == "05") {
                $return_obj['msg'] = $result_arr["respMsg"];
                $return_obj['result'] = false;
                $return_obj['code'] = $result_arr["respCode"];
            } else {
                $return_obj['msg'] = $result_arr["respMsg"];
                $return_obj['result'] = false;
                $return_obj['code'] = $result_arr["respCode"];
            }
        } else {
            $return_obj['msg'] = '应答报文验签失败';
            $return_obj['result'] = false;
            $return_obj['code'] = '';
        }
        return $return_obj;
    }
}