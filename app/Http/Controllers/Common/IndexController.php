<?php

namespace App\Http\Controllers\Common;

use App\Services\Common\CommonFunction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    /**
     * 加密特殊的id
     */
    public function encryptCode(Request $request, CommonFunction $commonFunction)
    {
        $phone = $request->phone;
        $uid = $request->uid;
        $username = $request->username;
        $pt_id = $request->pt_id;

        if ($phone) {
            $phone = $commonFunction->easyEncode($phone);
        }
        if ($uid) {
            $uid = $commonFunction->easyEncode($uid);
        }
        if ($username) {
            $username = $commonFunction->easyEncode($username);
        }
        if ($pt_id) {
            $pt_id = $commonFunction->easyEncode($pt_id);
        }


        return $this->getResponse(['phone' => $phone, 'uid' => $uid, 'username' => $username, 'pt_id' => $pt_id]);
    }


    private $key_wwb = 'PQWE67RTYUIOA58SDFGH34JKLZX29CVBNM';

    /*
     * 编码id成邀请码
     */
    public function encodeId($t_id)
    {
        $key = $this->key_wwb;
        $code_id = '000000';
        for ($i = 6; $i >= 0; $i--) {
            $code_id[$i] = $key[$t_id % 34];
            $t_id = intval($t_id / 34);
        }
        if ($t_id) {
            return false;
        }
        return $code_id;
    }

    /*
     * 解码邀请码为id
     */
    public function decodeId($t_co)
    {
        $key = $this->key_wwb;
        $t_id = 0;
        for ($i = 5; $i >= 0; $i--) {
            $t_id += strpos($key, $t_co[$i]) * pow(34, 5 - $i);
        }
        return $t_id;
    }
}
