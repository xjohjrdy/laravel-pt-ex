<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\App\ShopAddress;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    /**
     * 列出用户所有地址
     * @param Request $request
     * @param ShopAddress $shopAddress
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, ShopAddress $shopAddress)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            $address = $shopAddress->getAllAddress($arrRequest['app_id']);
            return $this->getResponse($address);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 新增用户的地址
     * get {"app_id":"","collection":"xxx","phone":"12321321","zone":"\u798f\u5efa\u7701\u798f\u5dde\u5e02\u53f0\u6c5f\u533a","detail":"xxxxxxx","is_default":"1"}
     * @param Request $request
     * @param ShopAddress $shopAddress
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function create(Request $request, ShopAddress $shopAddress)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            $count = $shopAddress->getAllAddress($arrRequest['app_id'])->count();
            if ($count >= 10) {
                return $this->getInfoResponse('3002', '您填写的地址超过了10个！');
            }
            if ($arrRequest['is_default']) {
                $shopAddress->unsetAllDefault($arrRequest['app_id']);
            }
            $pattern = '/^[\x{00}-\x{ff}\x{4e00}-\x{9fa5}\x{3010}\x{3011}\x{ff08}\x{ff09}\x{201c}\x{201d}\x{2018}\x{2019}\x{ff0c}\x{ff01}\x{ff0b}\x{3002}\x{ff1f}\x{3001}\x{ff1b}\x{ff1a}\x{300a}\x{300b}]+$/u';
            if (!preg_match($pattern, $arrRequest['collection'])) {
                return $this->getInfoResponse('3003', '您的名字请不要输入特殊符号！');
            }
            if (!preg_match($pattern, $arrRequest['detail'])) {
                return $this->getInfoResponse('3005', '您的详细地址请不要输入特殊符号！');
            }

            $res = $shopAddress->addShopAddress($arrRequest['app_id'], $arrRequest['collection'], $arrRequest['phone'], $arrRequest['zone'], $arrRequest['detail'], $arrRequest['is_default']);

            return $this->getResponse(['msg' => '新增成功！', 'id' => $res->id]);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * 列出用户的单个地址
     * get {"app_id":"1"}
     * @param Request $request
     * @param $id
     * @param ShopAddress $shopAddress
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function show(Request $request, $id, ShopAddress $shopAddress)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            $address = $shopAddress->getOneAddress($id);
            if (!$address) {
                return $this->getInfoResponse('4004', '地址不存在！');
            }

            if ($address->app_id <> $arrRequest['app_id']) {
                return $this->getInfoResponse('3002', '这不是您的地址！');
            }

            return $this->getResponse($address);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
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
     * 更新用户的地址
     * put {"app_id":"","collection":"xxx","phone":"12321321","zone":"\u798f\u5efa\u7701\u798f\u5dde\u5e02\u53f0\u6c5f\u533a","detail":"xxxxxxx","is_default":"1"}
     * @param Request $request
     * @param $id
     * @param ShopAddress $shopAddress
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function update(Request $request, $id, ShopAddress $shopAddress)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            return $this->getInfoResponse('5000', '请直接新增地址！或者删除地址，暂时无法修改！');
            $address = $shopAddress->getOneAddress($id);
            if ($address) {
                if ($address->app_id == $arrRequest['app_id']) {
                    if ($arrRequest['is_default']) {
                        $shopAddress->unsetAllDefault($arrRequest['app_id']);
                    }
                    $shopAddress->updateOneAddress($id, $arrRequest['collection'], $arrRequest['phone'], $arrRequest['zone'], $arrRequest['detail'], $arrRequest['is_default']);
                    return $this->getResponse('更新成功！');
                }
                throw new ApiException('这不是您的地址！', '3002');
            }
            throw new ApiException('地址不存在！', '4004');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 删除收货地址
     * {"app_id":"1"}
     * @param $id
     * @param Request $request
     * @param ShopAddress $shopAddress
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function destroy($id, Request $request, ShopAddress $shopAddress)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('app_id', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            $address = $shopAddress->getOneAddress($id);
            if ($address) {
                if ($address->app_id == $arrRequest['app_id']) {
                    $shopAddress->deleteAddress($id);
                    return $this->getResponse('删除成功！');
                }
                throw new ApiException('这不是您的地址！', '3002');
            }
            throw new ApiException('地址不存在！', '4004');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }
}
