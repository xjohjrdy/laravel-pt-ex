<?php

namespace App\Http\Controllers\Alimama;

use App\Exceptions\ApiException;
use App\Services\Taobaoke\Utils;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetDgItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return false;
            if(!$request->isJson())
                throw new ApiException('参数输入错误',3001);
        $apiUtils = new Utils('taobao.tbk.dg.material.optional');
        $strParams = json_decode($request->getContent(),true);
		$strParams['adzone_id'] = '91593200288';

        $apiUtils->arrange($strParams);

        print_r($strParams);

        $strParams['sign'] = $apiUtils->generateSign($strParams);
        $requestUrl = '?';
        foreach ($strParams as $sysParamKey => $sysParamValue)
        {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);

        $arrPerams = json_decode($apiUtils->curl($requestUrl));
        if (empty($arrPerams)){
            throw new ApiException('查询失败',4001);
        }

        return $this->getResponse($arrPerams);
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

		parse_str($request->getContent(), $strParams);

        if(empty($strParams))
            throw new ApiException('参数输入错误',3001);
		
        $apiUtils = new Utils('taobao.tbk.dg.material.optional');
		$strParams['adzone_id'] = '91593200288';
        $apiUtils->arrange($strParams);

        $strParams['sign'] = $apiUtils->generateSign($strParams);
        $requestUrl = '?';
        foreach ($strParams as $sysParamKey => $sysParamValue)
        {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);

        $arrParams = json_decode($apiUtils->curl($requestUrl));

        if (@$arrParams->error_response)
            throw new ApiException($arrParams->error_response->msg,4001);


        return $this->getResponse($arrParams);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
