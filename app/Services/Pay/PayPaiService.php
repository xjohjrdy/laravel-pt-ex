<?php


namespace App\Services\Pay;

use App\Entitys\Ad\RechargeOrder;
use App\Entitys\App\ReturnBack;
use App\Entitys\App\ShopOrders;
use App\Services\Recharge\RechargeUserLevel;
use App\Services\Shop\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

/**
 * 支付派服务类
 * Class PayPaiService
 * @package App\Services\Pay
 */
class PayPaiService
{
    // 商户号：20200210122504XX6428FCCC5131420D  秘钥 ：20200210122504XX04CE7876749D4285 （福州合泰隆贸易有限公司）
    //福州梓妍嘉业贸易有限公司（对公）：商户ID：20200117144332XXD429A00296814B62  秘钥：20200117144332XXC62FA1FCA5B14345  （对私）商户ID：20200117144333XX0342E5482BB34F53 秘钥：20200117144333XX5EDCF411CBC74DB6
    //    private $business_no = '20200117144332XXD429A00296814B62'; // 商户号
    //    private $merchantKey = "20200117144332XXC62FA1FCA5B14345"; // 密钥
    //（对私）商户ID：20200117144333XX0342E5482BB34F53 秘钥：20200117144333XX5EDCF411CBC74DB6
    private $busines_nos = [
        [
            'no' => '20200210122504XX6428FCCC5131420D',
            'key' => '20200210122504XX04CE7876749D4285' // 福州合泰隆贸易有限公司
        ],
        [
            'no' => '20200117144332XXD429A00296814B62',
            'key' => '20200117144332XXC62FA1FCA5B14345' // 福州梓妍嘉业贸易有限公司 对公
        ],
        [
            'no' => '20200117144333XX0342E5482BB34F53',
            'key' => '20200117144333XX5EDCF411CBC74DB6' // 福州梓妍嘉业贸易有限公司 对私
        ]
    ];
    private $businees_map = [
        '20200210122504XX6428FCCC5131420D' => '20200210122504XX04CE7876749D4285',
        '20200117144332XXD429A00296814B62' => '20200117144332XXC62FA1FCA5B14345',
        '20200117144333XX0342E5482BB34F53' => '20200117144333XX5EDCF411CBC74DB6',
    ];
    private $business_no = ''; // 商户号
    private $merchantKey = ""; // 密钥

    private $notyfy_url = ""; // 支付回调地址
    private $TEST = 'http://test.xxx.com/trx/app/interface.action'; // 测试请求地址
    private $PAY_URL = "https://www.sys.cnjrbank.com/middle-stage/quicktransaction/pay";
    private $QUERY_URL = "https://www.sys.cnjrbank.com/middle-stage/quicktransaction/appInterface";
    private $mini_app_id = ''; // 小程序APPId
    //枚举值	说明	备注
    //AppPay	主被扫接口
    //AppPayPublic	公众号/服务窗接口/JS	微信:微信公众号
    //支付宝:支付宝服务窗
    //银联:银联JS
    //AppPayApplet	小程序接口	微信:微信小程序
    //支付宝:支付宝小程序
    //AppPaySdk	微信SDK接口
    //AppPayH5WFT	微信(WAP)H5接口
    //AppUserInfo	查询用户信息接口	如用户openid
    //AppPayQuery	统一交易查询接口
    //AppPayRefund	退款接口
    //AppPayRefundQuery	退款查询接口
    //AppPayClose	订单关闭接口
    //CouponApply	发放代金券接口
    //QueryCouponStock	查询代金券批次接口
    //QueryCouponsInfo	查询代金券信息接口
    public function __construct()
    {
//          $random = 0; //  福州合泰隆贸易有限公司
//        $random = 1; //  福州梓妍嘉业贸易有限公司 对公
        $random = 2; //  福州梓妍嘉业贸易有限公司 对私
//        $random = mt_rand(0, 2);
        $this->business_no = $this->busines_nos[$random]['no'];
        $this->merchantKey = $this->busines_nos[$random]['key'];
    }

    public function pay(
        $orderId, $appId, $deviceInfo, $openid, $orderAmount, $orderIp,
        $goodsName, $goodsDetail = "", $desc = ""
    )
    {
        $sign_keys = [
            'P1_bizType', 'P2_orderId', 'P3_customerNumber', 'P4_payType', 'P5_appid', 'P6_deviceInfo', 'P7_isRaw',
            'P8_openid', 'P9_orderAmount', 'P10_currency', 'P11_appType', 'P12_notifyUrl', 'P13_successToUrl',
            'P14_orderIp', 'P15_goodsName', 'P16_goodsDetail', 'P17_limitCreditPay', 'P18_desc'
        ];
        $params = [
            'P1_bizType' => 'AppPayApplet', //交易类型 公众号为AppPayPublic,小程序为AppPayApplet 1
            'P2_orderId' => $orderId, //商户订单号(查询用的，限制32位，请保存由于查询数据) 1
            'P3_customerNumber' => $this->business_no, //支付派分配的商户号 1
            'P4_payType' => 'APPLET', //PUBLIC:公众号支付 APPLET:小程序 1
            'P5_appid' => $appId, //微信支付分配的公众账号ID,如果是小程序支付时填小程序appid 1
            'P6_deviceInfo' => $deviceInfo, //设备号 0
            'P7_isRaw' => '1', //是否原生态 0
            'P8_openid' => $openid, //微信用户关注商家公众号的openid 1
            'P9_orderAmount' => $orderAmount, //订单金额，以元为单位，最小金额为0.01 1
            'P10_currency' => 'CNY', //币种类型
            'P11_appType' => 'WXPAY', //客户端类型 WXPAY:微信
//            'P12_notifyUrl' => 'http://api.36qq.com/api/mini_order_pay_callback', //异步接收支付派支付结果通知的回调地址，通知url必须为外网可访问 0
            'P12_notifyUrl' => 'http://chenzhenghang.imwork.net/api/mini_order_pay_callback', //异步接收支付派支付结果通知的回调地址，通知url必须为外网可访问 0
            'P13_successToUrl' => '', //支付完成后，展示支付结果的页面地址 0
            'P14_orderIp' => $orderIp, //下单IP 1
            'P15_goodsName' => $goodsName, //商品名称 1
            'P16_goodsDetail' => $goodsDetail, //商品详情 0
            'P17_limitCreditPay' => '0', //能否使用信用卡 1，禁用
            'P18_desc' => $desc, //备注 0
            'P19_subscribeAppId' => '', //关注appId 0
            'P21_goodsTag' => '', //商品标记 0
            'P22_guid' => '', //微信进件时上送的唯一号 0
            'P23_marketingRule' => '', //营销参数规则 0
            'P24_identity' => '', //实名参数 0
            'splitBillType' => 'FIXED_AMOUNT', //分账类型 0
            'ruleJson' => '', //分账规则串 0
        ];

        $params_str = "";
        foreach ($sign_keys as $key) {
            $params_str = $params_str . '&' . $params[$key];
        }
        $sign_str = md5($params_str . '&' . $this->merchantKey);
        $params['sign'] = $sign_str;

//        dd($params);
        $client = new Client();

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $params,
        ];
        $res_login_data = $client->request('POST', $this->PAY_URL, $data);
        return (string)$res_login_data->getBody();
    }


    /**
     * 退款
     */
    public function refund(
        $refundOrderId, $orderId, $amount, $desc, $business_no
    )
    {
        $this->business_no = $this->busines_nos[$business_no]['no'];
        $this->merchantKey = $this->busines_nos[$business_no]['key'];
        $sign_keys = [
            'P1_bizType', 'P2_orderId', 'P3_customerNumber', 'P4_refundOrderId', 'P5_amount', 'P6_callbackUrl',
            'ruleJson'
        ];
        $params = [
            'P1_bizType' => 'AppPayRefund', //交易类型 公众号为AppPayPublic,小程序为AppPayApplet 1
            'P2_orderId' => $orderId, //商户订单号(查询用的，限制32位，请保存由于查询数据) 1
            'P3_customerNumber' => $this->business_no, //支付派分配的商户号 1
            'P4_refundOrderId' => $refundOrderId, //退款订单号
            'P5_amount' => $amount, //退款金额
            'P6_callbackUrl' => 'http://api.36qq.com/api/mini_refund_callback', //通知回调地址 http://api.36qq.com/api/mini_refund_callback
//            'P6_callbackUrl' => 'http://chenzhenghang.imwork.net/api/mini_refund_callback', //通知回调地址 http://api.36qq.com/api/mini_refund_callback
            'P7_desc' => $desc, //退款原因/备注
            'P8_orderSerialNumber' => '', //支付派原订单与”商户订单号”二选一
            'ruleJson' => '', //退款规则串
        ];

        $params_str = "";
        foreach ($sign_keys as $key) {
            $params_str = $params_str . '&' . $params[$key];
        }
        $sign_str = md5($params_str . '&' . $this->merchantKey);
        $params['sign'] = $sign_str;

//        dd($params);
        $client = new Client();

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $params,
        ];
        $res_login_data = $client->request('POST', $this->QUERY_URL, $data);
        return (string)$res_login_data->getBody();
    }

    /**
     * h5支付
     */
    public function h5Pay(
        $appId, $orderId, $amount, $goodsName, $desc
    )
    {
        $sign_keys = [
            'P1_bizType', 'P2_orderId', 'P3_customerNumber', 'P4_orderAmount', 'P5_currency', 'P6_appType',
            'P7_userId', 'P8_notifyUrl', 'P9_successToUrl', 'P10_falseToUrl', 'P11_goodsName', 'P12_goodsDetail'
        ];
        $params = [
            'P1_bizType' => 'AppQuickPayHtml', //交易类型 公众号为AppPayPublic,小程序为AppPayApplet 1
            'P2_orderId' => $orderId, //商户订单号(查询用的，限制32位，请保存由于查询数据) 1
            'P3_customerNumber' => $this->business_no, //支付派分配的商户号 1
            'P4_orderAmount' => $amount, //退款订单号
            'P5_currency' => 'CNY', //人民币:CNY
            'P6_appType' => 'WXPAY', //WXPAY:微信ALIPAY：支付宝
            'P7_userId' => $appId, // 用于存第三方平台的用户标识。
//            'P8_notifyUrl' => 'http://chenzhenghang.imwork.net/api/mini_order_pay_callback', //异步接收支付派支付结果通知的回调地址，通知url必须为外网可访问
            'P8_notifyUrl' => 'http://api.36qq.com/api/mini_order_pay_callback', //异步接收支付派支付结果通知的回调地址，通知url必须为外网可访问
            'P9_successToUrl' => '', //页面跳转地址
            'P10_falseToUrl' => '', //失败页面跳转地址
            'P11_goodsName' => $goodsName, //商品名称
            'P12_goodsDetail' => $desc, //商品详情
        ];

        $params_str = "";
        foreach ($sign_keys as $key) {
            $params_str = $params_str . '&' . $params[$key];
        }
        $sign_str = md5($params_str . '&' . $this->merchantKey);
        $params['sign'] = $sign_str;

//        dd($params);
        $client = new Client();

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $params,
        ];
        $res_login_data = $client->request('POST', $this->QUERY_URL, $data);
        return (string)$res_login_data->getBody();
    }

    /**
     * 查询商户余额
     */
    public function checkBusinessMoney($business_no)
    {
        $this->business_no = $this->busines_nos[$business_no]['no'];
        $this->merchantKey = $this->busines_nos[$business_no]['key'];
        $sign_keys = [
            'P1_bizType', 'P2_timestamp', 'P3_customerNumber'
        ];
        $params = [
            'P1_bizType' => 'MerchantAccountQuery', //交易类型 公众号为AppPayPublic,小程序为AppPayApplet 1
            'P2_timestamp' => date('YmdHis', time()), //时间戳
            'P3_customerNumber' => $this->business_no, //支付派分配的商户号 1
        ];

        $params_str = "";
        foreach ($sign_keys as $key) {
            $params_str = $params_str . '&' . $params[$key];
        }
        $sign_str = md5($params_str . '&' . $this->merchantKey);
        $params['sign'] = $sign_str;
        $client = new Client();

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $params,
        ];
        $res_login_data = $client->request('POST', $this->QUERY_URL, $data);
        return (string)$res_login_data->getBody();
    }

    /**
     * 支付成功回调
     *
     * 商户号    rt1_customerNumber    是    String(15)    C1800000002    支付派分配的商户号
     * 商户订单号    rt2_orderId    是    String(50)    p_20170302185347    商户系统内部订单号，要求50字符以内，同一商户号下订单号唯一
     * 平台流水号    rt3_systemSerial    否    String(50)    201702241400010002    支付派系统唯一支付流水号
     * 订单状态    rt4_status    是    String(20)    SUCCESS    INIT:已接收 DOING:处理中 SUCCESS:成功 FAIL:失败 CLOSE:关闭
     * 订单金额    rt5_orderAmount    是    Number(10.2)    0.01    订单金额，以元为单位，最小金额为0.01
     * 币种    rt6_currency    是    String(10)    CNY    CNY:人民币
     * 通知时间    rt7_timestamp    是    String(20)    1488446204985    精确到通知时间的毫秒数
     * 备注    rt8_desc    是    String(100)    备注    备注
     * 用户openId    rt10_openId    否    String(40)    o-Rj7wCKMUFF7dSbWaoPlssEtDqQ    微信用户openId(暂不用),不参与签名
     * 第三方平台订单号    rt11_channelOrderNum    否    String(40)    1234567890    第三方平台订单号,不参与签名
     * 订单完成时间    rt12_orderCompleteDate    否    String(60)    2018-03-12 00:00:00    格式:yyyy-MM-dd HH:mm:ss(不参与签名)
     * 支付卡类型    rt13_onlineCardType    是    String(20)    CFT/CREDIT    DEBIT(借记卡) CREDIT(贷记卡) UNKNOWN(未知) CFT(钱包零钱);不参与签名
     * 上游返回 :现金支付金额    rt14_cashFee    否    Number(10.2)    100    上游返回 :现金支付金额, (订单总金额-现金券金额=现金支付金额) ,不参与签名
     * 上游返回:现金券金额    rt15_couponFee    否    Number(10.2)    88    上游返回:现金券金额,不参与签名
     * 支付宝使用的资金渠道和优惠信息    rt16_fundBillList    否    String(255)    {“aaa”,”bbb”}    支付宝时返回(不参与签名)
     * 微信支付宝交易订单号    rt17_outTransactionOrderId    否    String(40)    1234567890    成功时有返回(不参与签名)
     * 用户付款银    rt18_bankType    否    String(40)    Icbc-credit    用户付款银行,成功时有返回(不参与签名),具体见附件:
     * subOpenId    rt19_subOpenId    否    String(40)    aaRj7wCKMUFF7dSbWaoPlssTsx  /20183353535353    微信子商户subOpenId.或支付宝子商户用户buyer_id (不参与签名)
     * 通道订单属性    rt20_orderAttribute    否    String(30)    UNDIRECT_DEFAULT    标记通道订单属性 UNDIRECT_DEFAULT:间连通道 DIRECT_CHANNEL 直连通道(不参与签名)
     * 营销参数规则    rt21_marketingRule    否    String(512)    {“marketingMerchantNo”:”E180000001”,”marketingAmount”:10.00,”couponMerchantNo”:”C1800000001”}    营销参数规则,JSON格式字符串，des加密传输,详见5.3营销参数规则说明(不参与签名)
     * 优惠信息详情    rt22_promotionDetail    否    String(1024)    JSON串    微信返回的优惠详情(不参与签名)
     * 实际支付金额    rt23_paymentAmount    否    Number(10,2)        用户实际支付金额-(不参与签名)
     * 入账面额    rt24_creditAmount    否    Number(10,2)        入账面额(不扣手续费) (不参与签名)
     * 子商户公众号sub_appid rt25_appId 否    String(30)    wxdeaaaa2311    子商户公众号sub_appid(不参与签名)
     * 客户端类型    rt26_appPayType    否    String(30)    WXPAY    客户端类型 (不参与签名)
     * 支付类型    rt27_payType    否    String(30)    SCAN    支付类型(不参与签名)
     * 分账规则及状态    ruleJson    否    String(512)    [{"splitBillAmount":0.01,"splitBillMerchantEmail":"123@qq.com","splitBillOrderNum":"20888584614451","splitBillOrderStatus":"SUCCESS"}]    响应分账结果规则以及对应状态(不参与签名)
     * 签名    sign    是    String(200)    d2eb1570ea8fb8d560c354fa1b5db103    MD5 签名结果，详见“第 5 章数字签名”
     * 通知结果响应
     * 支付派后台把支付结果通知商户，商户根据支付结果做业务处理，商户处理后需以字符串的形式反馈处理结果,如一直未有效接收回调和响应,该商户会进入平台通知黑名单,不再发异步通知：
     * 返回结果    结果说明
     * success    处理成功，支付派系统收到此响应结果后不会再重发通知
     * fail或其他字符    处理不成功，支付派系统收到此响应结果或没有响应，系统安装通知重发机制（见上文重发机制）重发通知。
     * @param $params
     * @return string
     */
    public function payCallBack($params)
    {
        try {
            $sign_keys = [
                'rt1_customerNumber', 'rt2_orderId', 'rt3_systemSerial', 'rt4_status', 'rt5_orderAmount', 'rt6_currency',
                'rt7_timestamp', 'rt8_desc'
            ];

            $this->payLog($params);
            $params_str = "";
            foreach ($sign_keys as $key) {
                $params_str = $params_str . '&' . $params[$key];
            }
            $sign_str = md5($params_str . '&' . $this->merchantKey);
            if (strtoupper($sign_str) == $params['sign']) {
                $this->payLog("验证成功");
                return "success";
            } else {
                $this->payLog("验证失败" . strtoupper($sign_str) . '---' . $params['sign']);
                return "success";
            }
        } catch (\Exception $e) {
            $this->payLog($e->getMessage());
            return "success";
        }

    }

//    public function refundCallBack($params)
//    {
//        try {
//            $sign_keys = [
//                'rt1_customerNumber', 'rt2_orderId', 'rt3_systemSerial', 'rt4_status', 'rt5_orderAmount', 'rt6_currency',
//                'rt7_timestamp', 'rt8_desc'
//            ];
//
//            $this->payLog($params);
//            $params_str = "";
//            foreach ($sign_keys as $key) {
//                $params_str = $params_str . '&' . $params[$key];
//            }
//            $sign_str = md5($params_str . '&' . $this->merchantKey);
//            if (strtoupper($sign_str) == $params['sign']) {
//                $this->refundLog("验证成功");
//                return "success";
//            } else {
//                $this->refundLog("验证失败" . strtoupper($sign_str) . '---' . $params['sign']);
//                return "success";
//            }
//        } catch (\Exception $e) {
//            $this->payLog($e->getMessage());
//            return "success";
//        }
//
//    }

    public function callValidate($params){
        $sign_keys = [
            'rt1_customerNumber', 'rt2_orderId', 'rt3_systemSerial', 'rt4_status', 'rt5_orderAmount', 'rt6_currency',
            'rt7_timestamp', 'rt8_desc'
        ];
        $this->payLog($params);
        $params_str = "";
        foreach ($sign_keys as $key) {
            $params_str = $params_str . '&' . $params[$key];
        }
        $sign_str = md5($params_str . '&' . $this->businees_map[$params['rt1_customerNumber']]);
        if (strtoupper($sign_str) == $params['sign']) {
            $this->payLog("验证成功");
            return true;
        } else {
            $this->payLog("验证失败");
            return false;
        }
    }
    public function h5CallBack($params, $rechargeOrder, $rechargeUserLevel, $shopOrders, $order_model)
    {
        try {
            $sign_keys = [
                'rt1_customerNumber', 'rt2_orderId', 'rt3_systemSerial', 'rt4_status', 'rt5_orderAmount', 'rt6_currency',
                'rt7_timestamp', 'rt8_desc'
            ];
            $this->payLog($params);
            $params_str = "";
            foreach ($sign_keys as $key) {
                $params_str = $params_str . '&' . $params[$key];
            }
            $sign_str = md5($params_str . '&' . $this->businees_map[$params['rt1_customerNumber']]);
            if (strtoupper($sign_str) == $params['sign']) {
                $this->payLog("验证成功");
                if ($params['rt4_status'] <> "SUCCESS") {
                    $this->payLog($params['rt2_orderId'] . '---' . $params['rt4_status'] . '----' . $params['rt8_desc']);
                } else {
                    //拿到订单
                    //  // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
                    $out_trade_no = $params['rt2_orderId'];
                    $order = $rechargeOrder->getOrdersById($out_trade_no);
                    // 第二种订单情况，如果存在则进入商品回调
                    $shop_order = $shopOrders->getByOrderId($out_trade_no);
                    if (!empty($shop_order)) {
                        if ($shop_order->app_id == 1569840) {
                            $shop_order->real_price = 0.01;
                        }
                        $computer_price = $shop_order->real_price * 100;
//                if ($data->total_fee == $computer_price) {
//                file_put_contents('wechat_pay_notify_shop.txt', $data->out_trade_no . PHP_EOL, FILE_APPEND);
//                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);
                        $this->payLog('处理订单开始！');
                        $res_maid = $order_model->processOrder($shop_order->order_id);
                        $this->payLog('处理订单结束！');
//                }
                    }

                    if (!empty($order)) {
                        if ($order->uid == 1499531) {
                            $order->price = 0.01;
                        }
//                if (($order->price * 100) <> $data->total_fee) {
//                    file_put_contents('wechat_pay_notify_shop.txt', '金额不对等' . PHP_EOL, FILE_APPEND);
//                    file_put_contents('wechat_pay_notify_shop.txt', $data->total_fee . PHP_EOL, FILE_APPEND);
//                    file_put_contents('wechat_pay_notify_shop.txt', "订单金额：" . $order->price . PHP_EOL, FILE_APPEND);
//                    exit();
//                }
//                file_put_contents('wechat_pay_notify_shop.txt', "run" . PHP_EOL, FILE_APPEND);
                        // 5、其它业务逻辑情况
                        $arr = [
                            'uid' => $order->uid,
                            'money' => $order->price,
                            'orderid' => $out_trade_no,
                        ];
                        if ($shop_order) {
                            $arr = [
                                'uid' => $order->uid,
                                'money' => 800,
                                'orderid' => $out_trade_no,
                            ];
                        }
//                $AdUserInfo = new AdUserInfo();
//                $x = $AdUserInfo->getUserById($order->uid);
//                if ($x->groupid <= 22) {
                        $rechargeUserLevel->initOrder($arr);
                        $rechargeUserLevel->updateExt(); //升级
                        $rechargeUserLevel->returnCommission(); //返佣
                        $rechargeUserLevel->handleArticle(); //更新文章
                        $rechargeOrder->updateOrderStatus($out_trade_no);//更新订单
//                }
                    }
                }
            } else {
                $this->payLog("验证失败" . strtoupper($sign_str) . '---' . $params['sign']);
            }
            return "success";
        } catch (\Throwable $e) {
            $this->payLog($e->getMessage());
            return "success";
        }

    }

    /**
     * 退款成功回调
     * 商户号    rt1_customerNumber    是    String(15)    C1800000002    支付派分配的商户号
     * 商户订单号    rt2_orderId    是    String(50)    p_20170302185347    商户系统内部订单号，要求50字符以内，同一商户号下订单号唯一
     * 商户退款订单号    rt3_refundOrderId    是    String(50)    201702241400010002    商户系统唯一退款订单号
     * 平台退款流水号    rt4_systemSerial    是    String(20)    1233455    支付派平台唯一退款流水号
     * 退款订单状态    rt5_status    是    String(20)    SUCCESS    INIT:已接收 DOING:处理中 SUCCESS:成功FAIL:失败CLOSE:关闭
     * 退款订单金额    rt6_amount    是    Number(10.2)    0.01    退款订单金额，以元为单位，最小金额为0.01
     * 币种    rt7_currency    是    String(10)    CNY    CNY:人民币
     * 通知时间    rt8_timestamp    是    String(20)    1488446204985    精确到通知时间的毫秒数
     * 订单完成时间    rt9_refundOrderCompleteDate    否    String(60)    2018-03-12 00:00:00    格式:yyyy-MM-dd HH:mm:ss(不参与签名)
     * 第三方平台退款订单号    rt10_refundChannelOrderNum    否    String(50)    12345678    第三方平台退款订单号(排除签名)
     * 退款原因/备注    rt11_desc    否    String(80)    不想要了,退款    若商户传入，会在下发给用户的退款账单消息中体现退款原因(不参与签名)
     * 通道订单属性    rt12_refundOrderAttribute    否    String(30)    UNDIRECT_DEFAULT 标记通道订单属性 UNDIRECT_DEFAULT:间连通道 DIRECT_CHANNEL 直连通道(不参与签名)
     * 客户端类型    rt13_appPayType    否    String(30)    WXPAY    客户端类型 (不参与签名)
     * 支付类型    rt14_payType    否    String(30)    SCAN    支付类型(不参与签名)
     * 签名    sign    是    String(200)    d2eb1570ea8fb8d560c354fa1b5db103    MD5 签名结果，详见“第 5 章数字签名”
     * @param $params
     * @return string
     */
    public function refundCallBack($params)
    {
        $sign_keys = [
            'rt1_customerNumber', 'rt2_orderId', 'rt3_refundOrderId', 'rt4_systemSerial', 'rt5_status', 'rt6_amount',
            'rt7_currency', 'rt8_timestamp'
        ];

        $this->refundLog($params['rt2_orderId'] . '---' . $params['rt5_status'] . '---' . $params['rt1_customerNumber']);
        $params_str = "";
        foreach ($sign_keys as $key) {
            $params_str = $params_str . '&' . $params[$key];
        }
        $sign_str = md5($params_str . '&' . $this->businees_map[$params['rt1_customerNumber']]);

        if (strtoupper($sign_str)  == $params['sign']) {
            $this->refundLog($params['rt2_orderId'] . ' ok');
            if($params['rt5_status'] == 'FAIL'){
                $this->refundLog2('退款失败' . $params['rt2_orderId']);
                $shopReurunModel = new ReturnBack();
                $refund_order = $shopReurunModel->where([
                    'id' => $params['rt3_refundOrderId']
                ]);
                if($refund_order->exists()){
                    $refund_order->update(['status' => 10]); // 更新回等待退款的状态。
                }
            }

        } else {
            $this->refundLog("验证失败");
        }
    }

    /**
     * 支付记录日志
     */
    private function payLog($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/PayPai/pay/' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }

    /**
     * 记录日志
     */
    private function refundLog($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/PayPai/refund/' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }
    /**
     * 记录日志
     */
    private function refundLog2($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/PayPai/refund2/' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }
}