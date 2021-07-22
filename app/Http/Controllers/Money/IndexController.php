<?php

namespace App\Http\Controllers\Money;

use App\Entitys\App\BankMoney;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    /**
     * 拉出银行与网贷的内容
     * Display a listing of the resource.
     *
     * 光大银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 浦发银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 交通银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 兴业银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     *             http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     *             http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 兴业银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 广发银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 民生银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 平安银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 建设银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 上海银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 华夏银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 招商银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 温州银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 花旗银行    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 兴业小白    http://www.51ley.com/front/Apply/1?where=card&doEnter=yes&rm=0&id=&bid=&uid=a7b97aac208f48ddbe1f1e8076150167
     *
     * 点点小贷    http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 信富优贷    http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 还呗        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 彩票        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=1004557937588305920&uid=a7b97aac208f48ddbe1f1e8076150167
     * 51人品贷    http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 白领贷        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 信用飞        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 及急贷        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 拍拍贷        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 豆豆钱        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 省呗        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * 宜人贷        http://www.51ley.com/front/apply/2?&where=loan&doEnter=yes&rm=0&id=&uid=a7b97aac208f48ddbe1f1e8076150167
     * https://www.51ley.com/apis/p/ley/reg/loan/13105468541/?uid=a7b97aac208f48ddbe1f1e8076150167&id=1004661002731520001&name=%E6%B5%8B%E8%AF%95877&idCard=&bid=
     *
     * https://www.51ley.com/apis/p/ley/reg/card/13195626568/?uid=a7b97aac208f48ddbe1f1e8076150167&id=1015132698303266816&bid=907080162711961601&name=%E6%B5%8B%E8%AF%95&idCard=360822198609284091
     * @return \Illuminate\Http\Response
     */
    public function index(BankMoney $bankMoney)
    {
        $bank = $bankMoney->getAllType(1);
        $money = $bankMoney->getAllType(0);
        $arr = [
            'bank' => [
                'header_img' => 'https://a119112.oss-cn-beijing.aliyuncs.com/%E9%A6%96%E9%A1%B5/%E4%BF%A1%E7%94%A8%E5%8D%A1/bg_banner%403x.png',
                'header_title' => '多种靓卡，快速办理',
                'data' => $bank
            ],
            'money' => [
                'header_img' => 'https://a119112.oss-cn-beijing.aliyuncs.com/%E9%A6%96%E9%A1%B5/%E7%BD%91%E8%B4%B7/bg_banner%403x.png',
                'header_title' => '轻松贷款，快速审批',
                'data' => $money
            ],
        ];
        return $this->getResponse($arr);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * post {"type_id":"1","phone":"13194569898","real_name":"%E6%B5%8B%E8%AF%95","id_card":"360822198609284091"}
     * @param Request $request
     * @param Client $http
     * @param BankMoney $bankMoney
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, Client $http, BankMoney $bankMoney)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('phone', $arrRequest) || !array_key_exists('real_name', $arrRequest) || !array_key_exists('type_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if ($arrRequest['type_id'] == 26) {
                $data['result']['url'] = 'https://wyh.fanzhoutech.com/mobile/phoneverification?par=3C51A259FF204C8B8772D29F60E06F4B';
                return $this->getResponse($data['result']['url']);
            }

            if ($arrRequest['type_id'] == 5) {
                $data['result']['url'] = 'https://creditcard.bankcomm.com/content/dam/phone/activity/card/kpsq/banka.html?actUrl=https://creditcardapp.bankcomm.com/applynew/front/apply/track/record.html?recomId=22909314&trackType=0&trackCode=A040215029798&commercial_id=';
                return $this->getResponse($data['result']['url']);
            }

            if ($arrRequest['type_id'] == 7) {
                $data['result']['url'] = 'http://t.cn/EVT1FD6';
                return $this->getResponse($data['result']['url']);
            }

            if ($arrRequest['type_id'] == 10) {
                $data['result']['url'] = 'http://gkekj.cn/ZO3';
                return $this->getResponse($data['result']['url']);
            }

            if ($arrRequest['type_id'] == 1) {
                $data['result']['url'] = 'https://res.cc.cmbimg.com/itafront/taf/formapi/index.html#/login3/api3xxfhwt/remote?webAddress=M17PCGW1066K990100LK';
                return $this->getResponse($data['result']['url']);
            }


            $res = $bankMoney->getById($arrRequest['type_id']);
            if (!$res) {
                return $this->getInfoResponse('4004', '此通道暂时关闭申请！');
            }
            if (array_key_exists('id_card', $arrRequest)) {
                $url = 'https://www.51ley.com/apis/p/ley/reg/card/' . $arrRequest['phone'] . '/?uid=a7b97aac208f48ddbe1f1e8076150167&id=' . $res->type_id . '&bid=' . $res->type_b_id . '&name=' . $arrRequest['real_name'] . '&idCard=' . $arrRequest['id_card'];
            } else {
                $url = 'https://www.51ley.com/apis/p/ley/reg/loan/' . $arrRequest['phone'] . '/?uid=a7b97aac208f48ddbe1f1e8076150167&id=' . $res->type_id . '&name=' . $arrRequest['real_name'] . '&idCard=&bid=';
            }
            $response = $http->request('post', $url, ['verify' => false]);
            $data = json_decode((string)$response->getBody(), true);
            if (!array_key_exists('status', $data) || !array_key_exists('result', $data) || !array_key_exists('url', $data['result'])) {
                return $this->getInfoResponse('3002', '您的身份证手机号填写不正确！请重新填写');
            }
            if ($arrRequest['type_id'] == 1) {
                $data['result']['url'] = 'https://www.51ley.com' . $data['result']['url'];
            }


            return $this->getResponse($data['result']['url']);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！错误信息：' . $e->getLine(), '500');
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
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
