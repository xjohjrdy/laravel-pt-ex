<?php

namespace App\Http\Controllers\Withdrawals;

use App\Exceptions\ApiException;
use App\Services\Advertising\Withdrawalsawals;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     * 提现接口
     * @param Withdrawalsawals $withdrawalsawals
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Withdrawalsawals $withdrawalsawals, Request $request)
    {
        return $this->getInfoResponse('40004', '此功能关闭');
        $arrRequest = json_decode($request->data, true);
        if (!$arrRequest) {
            throw new ApiException('传入参数错误', '3001');
        }
        $arrResult = $withdrawalsawals->verify($arrRequest);

        $withdrawalsawals->deduction($arrRequest);
        $ptGold = 10 * ($arrRequest['bonus_amount'] + $arrRequest['order_amount']);
        $arrResult['ptGold'] = $ptGold;

        $withdrawalsawals->addPtGold($arrResult);


        return $this->getResponse('提现成功');

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
