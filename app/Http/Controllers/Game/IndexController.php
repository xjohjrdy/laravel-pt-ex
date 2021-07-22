<?php

namespace App\Http\Controllers\Game;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\OutsideOrders;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\Crypt\RsaUtils;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
     * 充值接口
     * Store a newly created resource in storage.
     * post {"order_id":"123","money":"1.00","uid":1,"remark":"商品1","ext":"12312"}
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request,AdUserInfo $adUserInfo, UserAccount $userAccount, OutsideOrders $outsideOrders,UserAboutLog $aboutLog,UserCreditLog $creditLog,CommonFunction $commonFunction)
    {

        try {
            DB::beginTransaction();
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('uid', $arrRequest) || !array_key_exists('order_id', $arrRequest) || !array_key_exists('money', $arrRequest) || !array_key_exists('remark', $arrRequest) || !array_key_exists('ext', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            $uid = $commonFunction->easyDecode($arrRequest['uid']);
            if (!$uid){
                throw new ApiException('用户信息错误！', '3000');
            }
            $ptb = $arrRequest['money'] * 10;
            $account = $userAccount->getUserAccount($uid);
            $user = $adUserInfo->getUserById($uid);
            if ($account->extcredits4 < $ptb) {
                throw new ApiException('用户的余额不足！', '3002');
            }
            $res = $outsideOrders->createNewOrder($uid, $arrRequest['order_id'], $arrRequest['remark'], $arrRequest['money']);
            if (!$res) {
                throw new ApiException('网络异常！！', '3005');
            }

            $userAccount->subtractPTBMoney($ptb,$uid);
            $insert_id = $creditLog->addLog($uid, "GRE", ['extcredits4' => -$ptb]);
            $extcredits4_change = $account->extcredits4 - $ptb;
            $aboutLog->addLog($insert_id, $user->uid, $user->username, $user->pt_id, ["extcredits4" => $account->extcredits4], ["extcredits4" => $extcredits4_change]);


            DB::commit();
            return $this->getResponse($arrRequest['ext']);
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试', '500');
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
