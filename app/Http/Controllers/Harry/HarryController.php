<?php

namespace App\Http\Controllers\Harry;

use App\Services\HarryPay\RsaHarry;
use App\Services\HarryPayOut\Harry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HarryController extends Controller
{
    //

    public function index()
    {
        //测试方法
        $harry = new Harry();

        //合同系列接口

        $serialNo = 'wh' . date('YmdHis', time()) . uniqid();
//        $res = $harry->put($serialNo, '卞贤铃', '18695790413', '0', '352227198704131818');

//        $res = $harry->getPutResult('HKRT1572945640620');  //结果可以直接塞response

        //打款系列接口

        $res = $harry->push('卞贤铃', 18066265855, '352227198704131818', '0000000003', '1', '18066265855');

        var_dump($res);
        /**
         * array(3) {
         * ["content"]=>
         * string(15) "订单号重复"
         * ["return_code"]=>
         * string(1) "F"
         * ["return_message"]=>
         * string(15) "操作失败！"
         * }
         * array(2) {
         * ["return_code"]=>
         * string(1) "T"
         * ["return_message"]=>
         * string(15) "操作成功！"
         * }
         */

//        $res = $harry->getPushResult('0000000003');
        /**
         * array(5) {
         * ["content"]=>
         * string(1052) "{"sign":"WjyL5+FnOo410b7g3ExIb+FaZycPuYEt+rbr2DVf3eo9vPGBQE/d2mTYtb5aQ0yJ17cHCREUDRsyBRRnTNxHBKw5M/BmyXu7ycm9bL8tRdJGVUYp4YhhuIADBk6z2Fv7c7CIhPs/lcPTA2lxV6IKr2sbWUmMEgMYnBrSA848UiSQecHYWKPb7vYaXpsfKFrbuphIgcUgKcL82RnVbeCz7hV+1/HCp8mxmCBVRRE8g/QrrNrCfkFFp/FBiLArQZ5OPhb6sgXDwZdJ1VGT425by3Qf/EW+EQGDFdM2jVSlke4qxzA9OWha1hWhMGdYqHLuxeOtj+VvvAITs9vZsFvL0VK8/1feDs/3Nrb5MHWAgZ5o6VmkNFAQoJYV8auRMWBETQ17x5Sy5V2cai9bc3ZqYHJAJjFyLD52pxRqK/4Xv7U4KJEEeXxAt2hVPsgBLlVRtTynEG9T92ip6NSpyjCZqHfb2yDGyxjysJ10SjiTohEZTwjIV5aflGvKR0QkREJ7J2jWjaPFBu8aTz32mWPb2aY8uBU5NEicKeiaCkZi1KinIXdoxgP+0hR0eFh2rpOZUK1CtqpK7wPHOB84+u3TP8mfSEB67AHsOAFZWCfkG3GMdVWn4Se4ug3G+PArWu6+RCn48GCLO597Au+eSTI38OFU8D/bXHpSQ73K60OaPUUzN1bd20To9be06XC4BH95D66TwWKfMRKj2i09kXsMPoUyLeSbWk9LiAqugbO4Pv/px2FyaaKa2DVgctjr6rLKf1DgELeDozQwojKgipSQmXtNL2bUCtE59JyEG+PXN3XFTe6GjzOpF48ef8mCABsV0jwoF2d8u81TCbeA0ZfC7YN7KIpXfy1xnhSQh7Hil+ZHYokLeU/VzLdK+QYTXvnpOxSl6nTnSXGOKoCFMexEEL5VbIe9MSCofDedxBBme8OCBTSOze35DsiPcMQdPQXWGolAwTq5So+XKMXElro5r8S1g9tn7/0+USpKsjeV5s5pkl5JXQU92qiAMRYVZsvK","signType":"RSA"}"
         * ["return_code"]=>
         * string(1) "T"
         * ["return_message"]=>
         * string(15) "操作成功！"
         * ["data_json"]=>
         * string(678) "{"actualAmount":1,"additionalCharge":0,"cardAttribute":"C","cardType":"DC","certificateNo":"130423199206192818","charset":"UTF-8","companyCharge":0,"companyServiceFee":0,"companyTax":0,"createTime":"2020-03-12 11:20:18","endTime":"2020-03-12 11:20:20.0","mobile":"1234567891","name":"何亮","noticeNum":"5","notifyUrl":"http://baidu.com","orderNo":"20200312112017727456502738944","outMemberNo":"1237264584692940801","outerOrderNo":"0000000003","payAccount":"15366789145","payType":"2","personServiceFee":0,"predictAmount":1,"projectName":"余额提现","salaryType":"0","service":"bpotop.zx.pay.order","serviceCharge":0,"signType":"RSA","status":"1","taxFee":0,"version":"1.1"}"
         * ["data"]=>
         * array(30) {
         * ["actualAmount"]=>
         * int(1)
         * ["additionalCharge"]=>
         * int(0)
         * ["cardAttribute"]=>
         * string(1) "C"
         * ["cardType"]=>
         * string(2) "DC"
         * ["certificateNo"]=>
         * string(18) "130423199206192818"
         * ["charset"]=>
         * string(5) "UTF-8"
         * ["companyCharge"]=>
         * int(0)
         * ["companyServiceFee"]=>
         * int(0)
         * ["companyTax"]=>
         * int(0)
         * ["createTime"]=>
         * string(19) "2020-03-12 11:20:18"
         * ["endTime"]=>
         * string(21) "2020-03-12 11:20:20.0"
         * ["mobile"]=>
         * string(10) "1234567891"
         * ["name"]=>
         * string(6) "何亮"
         * ["noticeNum"]=>
         * string(1) "5"
         * ["notifyUrl"]=>
         * string(16) "http://baidu.com"
         * ["orderNo"]=>
         * string(29) "20200312112017727456502738944"
         * ["outMemberNo"]=>
         * string(19) "1237264584692940801"
         * ["outerOrderNo"]=>
         * string(10) "0000000003"
         * ["payAccount"]=>
         * string(11) "15366789145"
         * ["payType"]=>
         * string(1) "2"
         * ["personServiceFee"]=>
         * int(0)
         * ["predictAmount"]=>
         * int(1)
         * ["projectName"]=>
         * string(12) "余额提现"
         * ["salaryType"]=>
         * string(1) "0"
         * ["service"]=>
         * string(19) "bpotop.zx.pay.order"
         * ["serviceCharge"]=>
         * int(0)
         * ["signType"]=>
         * string(3) "RSA"
         * ["status"]=>
         * string(1) "1"
         * ["taxFee"]=>
         * int(0)
         * ["version"]=>
         * string(3) "1.1"
         * }
         * }
         */

        //原生打款接口
//        $native_harry = new NativeHarry();
//        $res = $native_harry->push('何亮', 1234567891, '130423199206192818', '0000000004', 1, 15366789145);

//        $res = $harry->getPushResult('0000000003');
//        var_dump($res);
        exit();
    }

    /**
     *
     */
    public function callBack(Request $request)
    {
        $data = $request->getContent();

        $data = json_decode($data, true);

        //存下回调信息
        if ($data['return_code'] == 'T') {
//            var_dump($data['content']['serialNo']);//更新的流水订单号
        }

        return 'success';
    }

    /**
     *
     */
    public function pushCallBack(Request $request)
    {
        $data = $request->getContent();

        $data = json_decode($data, true);

        //私钥解密
        $rsa_harry = new RsaHarry();
        $sign_de = $rsa_harry->private_decrypt($data['sign']);

        $json_de = json_decode($sign_de, true);

        //存下回调信息
        var_dump($json_de);
        exit();

        return 'success';
    }
}
