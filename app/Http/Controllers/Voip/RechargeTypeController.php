<?php

namespace App\Http\Controllers\Voip;

use App\Entitys\Ad\VoipType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RechargeTypeController extends Controller
{
    /**
     * 获取购买列表
     * @param Request $request
     * @param VoipType $voipType
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request,VoipType $voipType)
    {
        $res = $voipType->getAllType();
        return $this->getResponse(['title_img'=>'http://a119112.oss-cn-beijing.aliyuncs.com/%E5%85%85%E5%80%BC/hfcz_banner@2x.png','list'=>$res]);
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
