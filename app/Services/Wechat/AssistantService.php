<?php


namespace App\Services\Wechat;


use App\Entitys\App\AppUserInfo;
use App\Entitys\App\WxAssistantOrder;
use App\Entitys\App\WxAssistantPackage;
use App\Entitys\App\WxAssistantSendGroup;
use App\Entitys\App\WxAssistantUser;
use App\Extend\DeTool;
use App\Extend\Random;
use App\Services\Itaoke\WechatServices;
use Illuminate\Support\Facades\DB;

class AssistantService
{


    const EXCEPTION_CODE = [
        1102 => '功能开发中',
        1101 => '无效的method',
        2100 => '登录失败！',
        2020 => '检测您已掉钱，请重新登录',
        2021 => '机器人已经过期，请续费后操作',
        2022 => '机器人详情获取失败，请稍后重试，或联系客服',
        2023 => '请先开通机器人套餐',
        2024 => '获取二维码失败！',
        2025 => '您已经同意过该协议了！',
        2026 => '已在线，请勿重复登录！',
        2027 => '重新登录失败，请稍后重试！',
        2028 => '群数量已达上限，最多可发送10个群',
        2029 => '群列表获取失败，请稍后重试！',
        2030 => '请确保将群保存通讯录后重新登录再尝试！',
        2031 => '退出登录失败，请稍后重试！',
        2602 => '你已添加过该群，请勿重复添加！',
        3333 => '分页每次最多查询十条数据！',
    ];
    private $app_id,
        $appUserModel,
        $wxUserModel,
        $wxOrderModel, $packageModel, $userInfo, $time, $robotService, $robot_info,
        $package_columns = ['id', 'get_month', 'normal_price', 'common_price', 'vip_price'];
    const ACCOUNT = 'PT_AS_';
    const EXPIRY_CODE = 1999;

    public function __construct($app_id)
    {
        $this->app_id = $app_id;
        $this->time = time();
        $this->appUserModel = new AppUserInfo();
        $this->wxUserModel = new WxAssistantUser();
        $this->packageModel = new WxAssistantPackage();
        $this->wxOrderModel = new WxAssistantOrder();
    }

    /**
     * 设置用户
     * @return WxAssistantUser|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null
     * @throws \Exception
     */
    public function getUserInfo()
    {
        if (!empty($this->userInfo['app_id'])) {
            return $this->userInfo;
        }
//        $userModel = $this->wxUserModel->rightJoin('lc_user', $this->wxUserModel->getTable() . '.app_id', '=', $this->appUserModel->getTable() . '.id')
//            ->where([$this->appUserModel->getTable() . '.id' => $this->app_id]);
//        $this->userInfo = $userModel->first(['lc_user.id as user_id', 'lc_user.*', $this->wxUserModel->getTable() . '.*']);
//        if (empty($this->userInfo['user_id'])) {
//            throw new \Exception('无效用户', 1000);
//        }
        $this->userInfo = $this->wxUserModel->where(['app_id' => $this->app_id])->first(['app_id', 'robot_id', 'wx_id', 'user_flag', 'group_flag', 'circle_flag',
            'wechatrobot', 'nickName', 'headUrl', 'wId', 'expiry_time']);
        if (empty($this->userInfo['app_id'])) {
            $this->wxUserModel->create([
                'app_id' => $this->app_id
            ]);
//            $this->userInfo = $userModel->first(['lc_user.id as user_id', 'lc_user.*', $this->wxUserModel->getTable() . '.*']);
            $this->userInfo = $this->wxUserModel->where(['app_id' => $this->app_id])->first(['app_id', 'robot_id', 'wx_id', 'user_flag', 'group_flag', 'circle_flag',
                'wechatrobot', 'nickName', 'headUrl', 'wId', 'expiry_time']);
        } else {
            if($this->userInfo['user_flag'] >= 2){
                if($this->userInfo['expiry_time'] < time()){
                    $this->userInfo['is_expire'] = 1;
                }
                $this->robotService = new WechatServices($this->userInfo['robot_id']);
                $res = $this->robotService->robotDetail();
                if ($res == false) {
                    $this->userInfo['robot_info'] = null;
                } else {
                    $this->userInfo['robot_info'] = $res;
                }
            }
        }
        if(empty($this->userInfo['is_expire'])){
            $this->userInfo['is_expire'] = 0;
        }
        return $this->userInfo;
    }


    function throwException($code){
        throw new \Exception(self::EXCEPTION_CODE[$code], $code);
    }
    /**
     * 校验用户是否有权限执行相关操作
     * @return bool
     * @throws \Exception
     */
    public function checkPermission()
    {
        $user = $this->getUserInfo();
        if ($user['user_flag'] >= 2) {
            $res = $this->userInfo['robot_info'];
            if (empty($res)) {
                $this->throwException(2022);
            }
            if ($res['end_time'] >= $this->time) {
                if ($res['login_status'] == 1) {
                    $permission = true;
                } else {
                    $this->throwException(2020);
                }
            } else {
                if($user['user_flag'] == 2){
                    $this->wxUserModel->where(['app_id' => $this->app_id])->update([
                        'user_flag' => 3
                    ]);
                }
                $this->throwException(2021);
            }
        }else {
            $this->throwException(2023);
        }
    }

    /**
     * 同意协议
     */
    public function agreeTip()
    {
        try {
            $this->getUserInfo();
            if($this->userInfo['user_flag'] == 0){
                $this->wxUserModel->where(['app_id' => $this->app_id])->update([
                    'user_flag' => 1
                ]);
            } else {
                $this->throwException(2025);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * 二次登录
     */
    public function secondOnline()
    {
        try {
            $user = $this->getUserInfo();
            if ($user['user_flag'] >= 2) {
                $res = $this->userInfo['robot_info'];
                if (empty($res)) {
                    $this->throwException(2022);
                }
                if ($res['end_time'] >= $this->time) {
                    if ($res['login_status'] == 0) {
                        $robot_id = $this->userInfo['robot_id']; // 机器人id
                        $this->robotService = new WechatServices($robot_id);
                        $res = $this->robotService->robotSecondLogin();
                        if($res == false){
                            $this->throwException(2027);
                        }
                        $this->wxUserModel->where(['app_id' => $this->app_id])->update([
                            'user_flag' => 2
                        ]);
                        return $res;
                    } else {
                        $this->throwException(2026);
                    }
                } else {
                    if($user['user_flag'] == 2){
                        $this->wxUserModel->where(['app_id' => $this->app_id])->update([
                            'user_flag' => 3
                        ]);
                    }
                    $this->throwException(2021);
                }
            }else {
                $this->throwException(2023);
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /** 下线
     */
    public function offLine()
    {
        try {
            $user = $this->getUserInfo();
            if ($user['user_flag'] >= 2) {
                $res = $this->userInfo['robot_info'];
                if (empty($res)) {
                    $this->throwException(2022);
                }
                $robot_id = $this->userInfo['robot_id']; // 机器人id
                $this->robotService = new WechatServices($robot_id);
                $res = $this->robotService->robotForceOffline();
                if($res == false){
                    $this->throwException(2031);
                }
                return $res;
            }else {
                $this->throwException(2023);
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }



    /**
     * 获取二维码
     * @return mixed
     */
    public function loginQrCode()
    {
        try {
            $this->getUserInfo();
            if(empty($this->userInfo['robot_info'])){
                $this->throwException(2023);
            }
            $robot_id = $this->userInfo['robot_id']; // 机器人id
            $this->robotService = new WechatServices($robot_id);
            $res = $this->robotService->robotQrcodeLogin();
            if($res == false){
                $this->throwException(2024);
            }
            return $res;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 检测是否已经扫码
     * @return mixed
     */
    public function inspectScan()
    {
        try {
            $this->getUserInfo();
            if(empty($this->userInfo['robot_info'])){
                $this->throwException(2023);
            }
            $robot_id = $this->userInfo['robot_id']; // 机器人id
            $this->robotService = new WechatServices($robot_id);
            $res = $this->robotService->robotQrcodeStatus();
            return $res;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /** 上线
     * $wId 二维码实例ID
     * 返回结果
     * "wcId" => "wxid_bbx" //微信id
     * "wAccount" => "wxid_bbx" //账户
     * "wId" => "00000172-7cf3-234d-0001-29271b83669d" //实例id
     * "data" => "VpIPcJiAf90N4zb/C+aeLtVWSK ◀" //目前用不到
     * "nickName" => "安心" //昵称
     * "headUrl" => "http://wx.qlogo.cn/mmhead/ver_1/"//微信头像
     */
    public function onLine($wId)
    {
        try {
            $this->getUserInfo();
            $robot_id = $this->userInfo['robot_id']; // 机器人id
            $this->robotService = new WechatServices($robot_id);
            $res = $this->robotService->robotAsyncMlogin($wId);
            if ($res == false) {
                $this->throwException(2100);
            } else {
                $this->wxUserModel->where(['app_id' => $this->app_id])->update([
                    'wx_id' => $res['wcId'],
                    'wAccount' => $res['wAccount'],
//                    'wId' => $res['wId'],
                    'nickName' => $res['nickName'],
                    'headUrl' => $res['headUrl'],
                    'user_flag' => 2
                ]);
            }
            return $res;
        } catch (\Exception $e) {
            throw $e;
        }
    }



    /**
     * 检测用户机器人状态
     * @param $type 0 关闭 1 开启
     */
    public function inspectRobotDetail()
    {
        try {
            $this->getUserInfo();
            if(empty($this->userInfo['robot_info'])){
                $this->throwException(1100);
            }
            return $this->userInfo['robot_info'];
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * 开启/关闭智能发群
     * @param $type 0 关闭 1 开启
     */
    public function changeGroup($type)
    {
        try {
            $this->checkPermission();
            $this->wxUserModel->where(['app_id' => $this->app_id])->update([
                'group_flag' => $type
            ]);
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * 开启/关闭智能发圈
     * @param $type 0 关闭 1 开启
     */
    public function changeCircle($type)
    {
        try {
            $this->checkPermission();
            $this->wxUserModel->where(['app_id' => $this->app_id])->update([
                'circle_flag' => $type
            ]);
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * 获取群列表
     */
    public function groupList()
    {
        try {
            $this->getUserInfo();
            if(empty($this->userInfo['robot_info'])){
                $this->throwException(2023);
            }
            $robot_id = $this->userInfo['robot_id']; // 机器人id
            $this->robotService = new WechatServices($robot_id);
            $res = $this->robotService->robotRoomList();
            if(is_null($res) || count($res) == 0){
                $this->throwException(2030);
            }
            if($res == false){
                $this->throwException(2029);
            }
            return $res;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 用户选群
     */
    public function setGroup($group_id, $header_img, $name, $wx_id,  $tb = 1, $jd = 0, $pdd = 0)
    {
        try {
            $userInfo = $this->wxUserModel->where(['app_id' => $this->app_id])->first(['wx_id']);
            if($userInfo['wx_id'] != $wx_id){
                throw new \Exception('无效的wx_id', '2601');
            }
            $groupModel = new WxAssistantSendGroup();
            $count = $groupModel->where(['app_id' => $this->app_id, 'wx_id' => $wx_id])->count();
            if($count >= 10){
                $this->throwException(2028);
            }
            $exit =  $groupModel->where(['app_id' => $this->app_id, 'wx_id' => $wx_id, 'user_name' => $group_id])->exists();
            if($exit){
                $this->throwException(2602);
            }
            $group_info = $groupModel->where(['app_id' => $this->app_id, 'user_name' => $group_id])->first(['id']);
            $entity = [
                'app_id' => $this->app_id,
                'user_name' => $group_id,
                'tb_flag' => $tb,
                'jd_flag' => $jd,
                'pdd_flag' => $pdd,
                'nike_name' => $name,
                'header_img' => $header_img,
                'wx_id' => $wx_id,
            ];
            if(empty($group_info)){
                $groupModel->create($entity);
            } else {
                $groupModel->where(['app_id' => $this->app_id, 'user_name' => $group_id])->update($entity);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 选中群列表
     */
    public function groupListDetails($user_names)
    {
        try {
            if(count($user_names) >= 10){
                $this->throwException(3333);
            }
            $userInfo = $this->wxUserModel->where(['app_id' => $this->app_id])->first(['robot_id']);
            if(empty($userInfo['robot_id'])){
                $this->throwException(2023);
            }
            $robot_id = $userInfo['robot_id'];
            $this->robotService = new WechatServices($robot_id);
            $info = [];
            foreach ($user_names as $key=>$item){
                $res = $this->robotService->robotRoomDetail($item);
                $index = count($info);
                if(!empty($res) && !empty($res[0])){
                    $detail = $res[0];
                    $info[$index]['userName'] = $detail['chatRoomId'];
                    $info[$index]['nikeName'] = $detail['nickName'];
                    $info[$index]['smallHead'] = $detail['smallHeadImgUrl'];
                }
            }
            return $info;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 选中群列表
     */
    public function checkGroupList($wx_id)
    {
        try {
            $groupModel = new WxAssistantSendGroup();
            $group_list = $groupModel->where(['app_id' => $this->app_id, 'wx_id' => $wx_id])->get(['app_id', 'tb_flag', 'jd_flag', 'pdd_flag', 'header_img', 'user_name', 'nike_name', 'wx_id']);
            return $group_list;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 删除群
     */
    public function removeGroup($group_id, $wx_id)
    {
        try {
            $groupModel = WxAssistantSendGroup::withTrashed();
            $groupModel->where(['app_id' => $this->app_id, 'wx_id' => $wx_id, 'user_name' => $group_id])->forceDelete();
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function getPackageInfo()
    {
        $res = $this->packageModel->where(['get_month' => 1])->first($this->package_columns);
        return $res;
    }

    public function getPackageList()
    {
        $res = $this->packageModel->get($this->package_columns);
        return $res;
    }
}