<?php

namespace App\Http\Controllers\Web;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\JdMoneyGetIn;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JdController extends Controller
{

    public function index(Request $request, JdMoneyGetIn $jdMoneyGetIn, Client $client)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return "<h1>请更新版本</h1>";
        }
        $obj_jd_data = $jdMoneyGetIn->getJdData($id);
        if (empty($obj_jd_data)) {
            $obj_jd_data['money'] = 0;
            $obj_jd_data['number'] = 0;
        }
        $post_api_data = [
            'data' => '{"app_id":' . $id . '}',
        ];
        $url = "http://api.36qq.com/jd_get_user_phone";
        $group_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data
        ];
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $arr_res = json_decode($json_res, true);
        return view('jd.jd_active', ['good' => $obj_jd_data,'arr_res'=>$arr_res,'app_id'=>$id]);
    }

    /*
     * 活动规则
     */
    public function jdActivity()
    {
        return view('jd.jd_activity', ['img' => ""]);
    }

    /*
    * 邀请好友
    */
    public function jdInvite()
    {
        return view('jd.jd_invite', ['img' => ""]);
    }

    /*
   * 活动倒计时
   */
    public function activityCount()
    {
        return view('web_shop.activity_count', ['img' => ""]);
    }

    /*
     * 提交手机号
     */
    public function submitPhone(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return "<h1>请更新版本</h1>";
        }
        $arrRequest = $request->toArray();
        $phone = $arrRequest['phone'];
        $post_api_data = [
            'data' => '{"app_id":'.$id.' ,"phone":' . $phone .'}',
        ];
        $url = "http://api.36qq.com/jd_push_user_phone";
        $group_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data
        ];
        $client = new Client();
        $res = $client->request('POST', $url, $group_data);
        $json_res = (string)$res->getBody();
        $arr_res = json_decode($json_res, true);
        if (@$arr_res['code'] != 200){
            throw new ApiException('京东错误:'.var_export($json_res,true), '3001');
        }
        return $this->getInfoResponse('200', $arr_res['data']);
    }
}
