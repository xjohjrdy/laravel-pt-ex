<?php
/**
 * User: Administrator
 * Date: 2020/5/20/020
 * Time: 14:39
 */

namespace App\Services\Itaoke;

use Faker\Provider\Uuid;
use Illuminate\Support\Facades\Storage;


class WechatServices
{

    private $obj_Assistant = null;
    private $robot_id = null;

    public function __construct($robot_id = null)
    {
        $this->obj_Assistant = new AssistantServices();
        $this->robot_id = $robot_id;
    }

    public function getRobotId()
    {
        return $this->robot_id;
    }

    public function setRobotId($robot_id)
    {
        $this->robot_id = $robot_id;
    }

    /**
     * 创建机器人
     * 返回参数
     * {
     * "id": 9221, //机器人id
     * "uid": 1736,
     * "wechatrobot": "fc7d8f0c-e9ca-37b1-99aa-2aeeb32ea3d3", //创建生成的微信号，用做标识
     * "wx_id": "",
     * "amount_used": 0,
     * "group_num": 20,  //发单群数量上限
     * "passwd": "",
     * "nickname": "",
     * "c_uid": 1736,
     * "login_status": 0,
     * "end_time": 1621582764, //机器人过期时间
     * "remark": null,
     * "wc_id": "",
     * "agent_uid": null,
     * "is_enabled": 0,
     * "robot_type": 1, //机器人类型
     * "ip": "http://106.52.59.134:28081/"
     * }
     * @param null $wechat
     * @return bool|mixed
     */
    public function createRobot($wechat = null, $month = 1)
    {
        $wechat = empty($wechat) ? Uuid::uuid() : $wechat;
        $req = $this->obj_Assistant->load_api('ItaokeRobotCreateGetRequest');
        $par['month'] = $month;
        $par['robot_type'] = 4; //全能机器人
        $par['wechatrobot'] = $wechat;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }
        return @$resp['data'];
    }

    /**
     * 获取机器人详情
     * array:17 [▼
     * "id" => 9221 //机器人id
     * "uid" => 1736
     * "wechatrobot" => "fc7d8f0c-e9ca-37b1-99aa-2aeeb32ea3d3"
     * "wx_id" => ""
     * "amount_used" => 0
     * "group_num" => 20 //发单群数量上限
     * "passwd" => ""
     * "nickname" => ""
     * "c_uid" => 1736
     * "login_status" => 0
     * "end_time" => 1621582764 //机器人过期时间
     * "remark" => null
     * "wc_id" => "" // 实例id 登录后有
     * "agent_uid" => null
     * "is_enabled" => 0 //0 正常 1暂停
     * "robot_type" => 1
     * "ip" => "http://106.52.59.134:28081/"
     * ]
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotDetail()
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotDetailGetRequest');
        $par['robot_id'] = $this->robot_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }
        return @$resp['data'];
    }

    /**
     * 机器人续费 一次一个月
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotChange($month = 1)
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotChangeGetRequest');
        $par['robot_id'] = $this->robot_id;
        $par['month'] = $month; //续费一个月
//        $par['group_num'] = 20; //群个数 无用参数
//        $par['wechatrobot'] = Uuid::uuid(); //替换微信 无用参数
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }
        return @$resp['data'];
    }

    /**
     * 获取登陆二维码
     * array:2 [▼
     * "wId" => "00000172-7a8f-a99a-0001-03ac5668b7cd" //实例id
     * "qrCodeUrl" => "https://uhuidog-1251179306.cos.ap-chengdu.myqcloud.com//imgQrCode/2d9b7cac-eef0-48c7-ad62-aa024bf576db-1591194135056.png" //二维码图片
     * ]
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotQrcodeLogin()
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotQrcodeMacloginRequest');
        $par['robot_id'] = $this->robot_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }
        return @$resp['data'];
    }

    /**
     * !!废弃接口
     * 检查是否扫码
     * fasle 未扫码。
     * true 已扫码
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotQrcodeStatus()
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotQrcodeStatusRequest');
        $par['robot_id'] = $this->robot_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
//        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }
        if (@$resp['data']['status'] != 1) {
            return false;
        }
        return true;
    }

    /**
     * !!废弃接口
     * 跳转实例
     * 返回 null 或者false为失败
     *array:6 [▼
     * "wcId" => "wxid_bbx" //微信id
     * "wAccount" => "wxid_bbx" //账户
     * "wId" => "00000172-7cf3-234d-0001-29271b83669d" //实例id
     * "data" => "VpIPcJiAf90N4zb/C+aeLtVWSK ◀" //目前用不到
     * "nickName" => "安心" //昵称
     * "headUrl" => "http://wx.qlogo.cn/mmhead/ver_1/"//微信头像
     * ]
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotCheckLogin($wc_id)
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotCheckMacloginRequest');
        $par['robot_id'] = $this->robot_id;
        $par['uuid'] = $wc_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return @$resp['data'];
    }


    /**
     *
     * array:3 [▼
     * "status" => "1234"
     * "msg" => "未登录"
     * "data" => array:1 [▼
     * "status" => 1
     * ]
     * ]
     *
     * array:3 [▼
     * "status" => "0000"
     * "msg" => "登录成功"
     * "data" => array:10 [▼ //return返回数据 data内
     * "wcId" => "wxid_nxdx40dz5g1j33" //个人id
     * "wAccount" => "wxid_nxdx40dz5g1j33" // 个人微信号
     * "country" => ""
     * "city" => ""
     * "signature" => ""
     * "nickName" => "安心" //昵称
     * "sex" => 0
     * "headUrl" => "http://wx.qlogo.cn/mmhead/ver_1/HWsiampXiac4uGOBd7ZVBPiavr76bm6H5URVYB8xHtVs1QrG21RIzrGQIxaUdZVSN4cMJic6hlwFVwxwxGuMYrKwrkegsbJgdMQkpPhunCYdlYg/0" //大头像
     * "smallHeadImgUrl" => "http://wx.qlogo.cn/mmhead/ver_1/HWsiampXiac4uGOBd7ZVBPiavr76bm6H5URVYB8xHtVs1QrG21RIzrGQIxaUdZVSN4cMJic6hlwFVwxwxGuMYrKwrkegsbJgdMQkpPhunCYdlYg/132" //小头像
     * "status" => 3
     * ]
     * ]
     *
     * @param $wc_id
     * @return bool|mixed
     */
    public function robotAsyncMlogin($wc_id)
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotAsyncMloginRequest');
        $par['robot_id'] = $this->robot_id;
        $par['uuid'] = $wc_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return @$resp['data'];
    }

    /**
     * !! 废弃接口
     * array:3 [▼
     * "public" => []
     * "friend" => array:88 [▶]
     * "group" => array:3 [▼
     * 0 => array:20 [▼
     * "userName" => "22314850331@chatroom" //群标签 通过该标签发送群消息
     * "aliasName" => null
     * "nickName" => "商业探讨" //群名称
     * "bigHead" => null
     * "smallHead" => null  //微信群头像
     * "sex" => 0
     * "type" => 3
     * "chatroomAccessType" => 0
     * "signature" => null
     * "remark" => null
     * "description" => null
     * "country" => null
     * "province" => null
     * "city" => null
     * "v1" => null
     * "v2" => null
     * "labelIDList" => null
     * "chatRoomOwner" => "wxid_bbx9w9g3tvz922" //群主微信号
     * "chatRoomData" => null
     * "roomInfoList" => []
     * ]
     * 1 => array:20 [▶]
     * 2 => array:20 [▶]
     * ]
     * ]
     * 获取好友群列表 // 需要将群存入下来
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotMacGet()
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotMacGetContract');
        $par['robot_id'] = $this->robot_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return @$resp['data'];
    }

    /**
     *array:8 [▼
     * 0 => array:11 [▶]
     * 1 => array:11 [▶]
     * 2 => array:11 [▶]
     * 3 => array:11 [▶]
     * 4 => array:11 [▶]
     * 5 => array:11 [▶]
     * 6 => array:11 [▼
     * "userName" => "24132566449@chatroom"
     * "nikeName" => "测试群聊004"
     * "remark" => ""
     * "signature" => ""
     * "sex" => 0
     * "aliasName" => ""
     * "country" => ""
     * "bigHead" => ""
     * "smallHead" => "http://wx.qlogo.cn/mmcrhead/cKmkygnppmV9TiawlBx3IiaicqHnyRlK2TaUBGNQiadQI2MXMnyXzYYBMLQicTjibXthjAybicf7v18fKHLdOvtzagmVdyIXv2Xc2CE/0"
     * "labelList" => ""
     * "v1" => "v1_20b28d52b1d6f79e2023db527f365c725ec8bcfb277fccd457bbf3a82ad5ed3008638a6b97df197253ede2888e07aab2@stranger"
     * ]
     * 7 => array:11 [▼
     * "userName" => "24779652059@chatroom"
     * "nikeName" => "测试群聊005"
     * "remark" => ""
     * "signature" => ""
     * "sex" => 0
     * "aliasName" => ""
     * "country" => ""
     * "bigHead" => ""
     * "smallHead" => "http://wx.qlogo.cn/mmcrhead/11rVsiazLzcL3I7W1rgq8qLhDdZWXIshTV8HfLP8ALho85m7RXr32klcRGbyj1cQ8jg9pF6W0Z2PMvtgKcJ5WQLCfxc3MVW2R/0"
     * "labelList" => ""
     * "v1" => "v1_10b07dd1166d9508360a5044178c251dc3ebe42a4b427e64363bfe6e0cf10d5f1a5a31d67368a6132a248a4ed906e791@stranger"
     * @return bool|mixed
     */
    public function robotRoomList()
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotRoomListRequest');
        $par['robot_id'] = $this->robot_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return @$resp['data'];
    }

    /**
     * 查看群详情
     *
     *  0 => array:9 [▼
     * "chatRoomId" => ""
     * "userName" => null
     * "nickName" => ""
     * "chatRoomOwner" => ""
     * "bigHeadImgUrl" => null
     * "smallHeadImgUrl" => ""
     * "v1" => ""
     * "memberCount" => 0
     * "chatRoomMembers" => []
     * ]
     *
     *  0 => array:9 [▼
     * "chatRoomId" => "21973181239@chatroom" //群ID
     * "userName" => null
     * "nickName" => "测试群聊002" //群昵称
     * "chatRoomOwner" => "wxid_nxdx40dz5g1j22"
     * "bigHeadImgUrl" => null
     * "smallHeadImgUrl" => "http://wx.qlog G7kPaO6TmuM/0" //群头像
     * "v1" => "v1@stranger"
     * "memberCount" => 3
     * "chatRoomMembers" => array:3 [▶]
     * ]
     * @return bool|mixed
     */
    public function robotRoomDetail($room_id)
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotRoomDetailRequest');
        $par['robot_id'] = $this->robot_id;
        $par['room_id'] = $room_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        //dd($resp);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return @$resp['data'];
    }


    /**
     *  "message" => "二次登录失败，请重新扫码登录"
     * "code" => "1001"
     * "data" => null
     *
     * array:11 [▼
     * "wcId" => "wxid_nxdx40dz5g1j33"
     * "wAccount" => "wxid_nxdx40dz5g1j33"
     * "country" => ""
     * "wId" => "e0b39d11-22f1-47de-8082-329a3729c942"
     * "city" => ""
     * "signature" => ""
     * "nickName" => "安心"
     * "sex" => 0
     * "headUrl" => "http://wx.qlogo.cn/mmhead/ver_1/HWsiampXiac4uGOBd7ZVBPiavr76bm6H5URVYB8xHtVs1QrG21RIzrGQIxaUdZVSN4cMJic6hlwFVwxwxGuMYrKwrkegsbJgdMQkpPhunCYdlYg/0"
     * "smallHeadImgUrl" => "http://wx.qlogo.cn/mmhead/ver_1/HWsiampXiac4uGOBd7ZVBPiavr76bm6H5URVYB8xHtVs1QrG21RIzrGQIxaUdZVSN4cMJic6hlwFVwxwxGuMYrKwrkegsbJgdMQkpPhunCYdlYg/132"
     * "status" => 3
     * ]
     *
     * 二次登录接口
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotSecondLogin()
    {
        try {
            $req = $this->obj_Assistant->load_api('ItaokeRobotSecondLogin');
            $par['robot_id'] = $this->robot_id;
            $req->setApiParas($par);
            $resp = (array)$this->obj_Assistant->execute($req);
            self::rlog($resp, __FUNCTION__);
            if (@$resp['status'] != "0000") {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return @$resp['data'];
    }

    /**
     * !! 废弃接口 2020年6月6日18:09:48
     * 掉线重连
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotLoseReconnet()
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotLoseReconnet');
        $par['robot_id'] = $this->robot_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        //dd($resp);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return @$resp['data'];
    }

    /**
     * 先检测是否在线，
     * true在线
     * false 离线
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotCheckOnline()
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotCheckOnline');
        $par['robot_id'] = $this->robot_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
//        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return true;
    }

    /**
     * 发送群消息
     * $wx_id = 群消息ID $content = 需要发送的消息
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotSendText($wx_id, $content)
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotMacSendTextRequest');
        $par['robot_id'] = $this->robot_id;
        $par['toWxId'] = $wx_id;
        $par['content'] = $content;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return true;
    }

    /**
     *
     * (
     * 'id' => '13348366274533666927',
     * 'userName' => 'wxid_bbx9w9g3tvz922',
     * 'nickName' => NULL,
     * 'createTime' => 1591249260, //发送时间
     * 'objectDesc' => '这是一个商品', //文案内容
     * 'commentUserList' => NULL,
     * 'likeUserList' => NULL,
     * )
     * // 改版后
     * "status": 1,
     * "object": {
     * "id": "13352xxxxx", //朋友圈id
     * "userName": "wxid_nxdxxxxx", //发送id
     * "createTime": 1591771266, //发送时间
     * "objectDesc": "xxxxxxxx"
     * }
     * 发送朋友圈
     * @param $robot_id
     * @return bool|mixed
     */
    public function robotSendCircle($content, $pic_url = null)
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotMacSendCircleRequest');
        $par['robot_id'] = $this->robot_id;
        $par['pic_url'] = $pic_url;
        $par['content'] = $content;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }
        return @$resp['data'];
    }

    /**
     * 朋友圈评论
     * @param $wx_id
     * @param $msg_id
     * @param $content
     * @param int $comment_id
     * @return bool
     */
    public function robotSendCircleComment($wx_id, $msg_id, $content, $comment_id = "0")
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotMacSendCircleCommentRequest');
        $par['robot_id'] = $this->robot_id;
        $par['wx_id'] = $wx_id;
        $par['msg_id'] = $msg_id;
        $par['content'] = $content;
        $par['comment_id'] = $comment_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        //dd($resp);

        if (@$resp['status'] != "0000") {
            return false;
        }

        return true;
    }

    /**
     * 群发送图片
     * @param $wx_id
     * @param $pic_url
     * @return bool
     */
    public function robotSendImg($wx_id, $pic_url)
    {
        try {
            $base64_data = $this->imgToBase64($pic_url);
        } catch (\Exception $e) {
            return false;
        }
//        dd($base64_data);
        $req = $this->obj_Assistant->load_api('ItaokeRobotMacsendImgRequest');
        $par['robot_id'] = $this->robot_id;
        $par['base64_data'] = $base64_data;
        $par['toWxId'] = $wx_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return true;
    }

    /**
     * 群发送图片
     * @param $wx_id
     * @param $pic_url
     * @return bool
     */
    public function robotSendUrlImg($wx_id, $pic_url)
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotMacSendImageRequest');
        $par['robot_id'] = $this->robot_id;
        $par['pic_url'] = $pic_url;
        $par['toWxId'] = $wx_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return true;
    }

    public function imgToBase64($img_file)
    {
        if (empty($img_file)) {
            return '';
        }
        $img_info = getimagesize($img_file); // 取得图片的大小，类型等
        if (!$img_info) {
            return '';
        }
        $img_base64 = '';
        if (strpos($img_file, 'http') === false) {
            if (!file_exists($img_file)) {
                return '';
            }

            $fp = fopen($img_file, "r"); // 图片是否可读权限
            if ($fp) {
                $filesize = filesize($img_file);
                $content = fread($fp, $filesize);
                $file_content = chunk_split(base64_encode($content)); // base64编码
            }
            fclose($fp);
        } else {
            $file_content = base64_encode(file_get_contents($img_file));
        }

        switch ($img_info[2]) {           //判读图片类型
            case 1:
                $img_type = "gif";
                break;
            case 2:
                $img_type = "jpg";
                break;
            case 3:
                $img_type = "png";
                break;
        }
//        $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;//合成图片的base64编码
        $img_base64 = $file_content;//合成图片的base64编码

        return $img_base64; //返回图片的base64
    }

    /**
     * 强制机器人下线
     * false 失败
     * true 成功
     * @return bool
     */
    public function robotForceOffline()
    {
        $req = $this->obj_Assistant->load_api('ItaokeRobotForceOfflineRequest');
        $par['robot_id'] = $this->robot_id;
        $req->setApiParas($par);
        $resp = (array)$this->obj_Assistant->execute($req);
        self::rlog($resp, __FUNCTION__);
        if (@$resp['status'] != "0000") {
            return false;
        }

        return true;
    }


    /**
     * 记录回调日志
     */
    static public function rlog($msg, $fun_name)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/itk_robot/' . $date . '_' . $fun_name . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }


}