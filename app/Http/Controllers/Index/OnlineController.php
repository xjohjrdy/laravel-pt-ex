<?php

namespace App\Http\Controllers\Index;

use App\Entitys\App\AlwaysOnline;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OnlineController extends Controller
{
    /**
     * @param AlwaysOnline $alwaysOnline
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(AlwaysOnline $alwaysOnline)
    {
        try {
            $online = $alwaysOnline->getLeastOnline();
            if (!$online) {
                throw new ApiException('客服不存在！', '4004');
            }

            return $this->getResponse($online);

        } catch (Exception $e) {
            throw new ApiException('服务器异常', '500');
        }
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
     * {"id": "1","type":"1"}
     * @param Request $request
     * @param AlwaysOnline $alwaysOnline
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, AlwaysOnline $alwaysOnline)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('id', $arrRequest) || $arrRequest['id'] == 0) {
                throw new ApiException('传入参数错误', '3001');
            }

            $online = $alwaysOnline->getOnlineById($arrRequest['id']);
            if ($online) {
                $online->connectNumber($arrRequest['type']);
            } else {
                throw new ApiException('客服不存在', '4004');
            }

            return $this->getResponse("状态更新成功");

        } catch (Exception $e) {
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
