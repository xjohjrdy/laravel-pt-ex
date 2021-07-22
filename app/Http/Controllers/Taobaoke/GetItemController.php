<?php

namespace App\Http\Controllers\Taobaoke;

use App\Services\Taobaoke\Utils;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return false;
        $apiUtils = new Utils('taobao.tbk.item.get');
        $strParams = array(
            'fields'    => 'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick',
            'q'         => '连衣裙',
            'sort'      => 'tk_rate_des',
            'is_tmall'  => 'false',
            'platform'  => '2',
            'page_no'   => '1',
            'page_size'   => '5'//页大小，默认20，1~100
        );

        $apiUtils->arrange($strParams);

        $strParams['sign'] = $apiUtils->generateSign($strParams);

        $requestUrl = '?';
        foreach ($strParams as $sysParamKey => $sysParamValue)
        {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);

        print_r($apiUtils->curl($requestUrl));die;

        return $this->getResponse($apiUtils->curl($requestUrl));

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
        if(!$request->isJson())
            throw new ApiException('参数输入错误',3001);
        $apiUtils = new Utils('taobao.tbk.item.get');
        $strParams = json_decode($request->getContent(),true);
        if (empty($strParams['fields']))
            $strParams['fields'] = 'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick';

        if (empty($strParams['platform']))
            $strParams['platform'] = '2';

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
