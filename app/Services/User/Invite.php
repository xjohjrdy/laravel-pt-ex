<?php


namespace App\Services\User;


use App\Entitys\App\AppUserInfo;
use App\Entitys\App\InviteRealUser;
use App\Services\Common\CommonFunction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Invite
{

    /**
     * 默认当年 拉新活动人数获取
     * Invite constructor.
     * @param $start_time 其实时间戳
     * @param $$end_time 结束时间戳
     */
    public function __construct()
    {

    }

    public static function getInviteUsers2($app_id, $start_time, $end_time)
    {
        $inviteModel = new InviteRealUser();
        $userModel = new AppUserInfo();
        $sql = "";
        $list = $userModel->leftJoin($inviteModel->getTable(), $inviteModel->getTable() . '.id', '=', $userModel->getTable() . '.id')
            ->where(['parent_id' => $app_id])->whereBetween('create_time', [$start_time, $end_time])->paginate(null, ['lc_user.id', $inviteModel->getTable() . '.id as real_flag', 'phone', 'real_name', 'avatar']);
        $data = $list->items();
        foreach ($data as $key => $item) {
            $item->phone = preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $item->phone);
            $item->real_flag = !empty($item->real_flag);
        }
        $result = [
            'current_page' => $list->currentPage(),
            'last_page' => $list->lastPage(),
            'data' => $data,
            'total' => $list->total(),
        ];
        return $result;
    }

    public static function getInviteUsers($app_id, $start_time, $end_time)
    {
        $sql = "select u1.id as id,
                IFNULL(u1.phone,'') as phone, 
                u1.real_name,
                u1.avatar,
                group_concat(uon.`status`) as upt, 
                group_concat(pdd.order_status) as ppt, 
                group_concat(jd.validCode) as jpt, 
                group_concat(taobao.`tk_status`) as tpt, 
                group_concat(shop.`status`) as spt
                from lc_user u1
                LEFT JOIN lc_user_order_new uon on u1.id = uon.user_id
                LEFT JOIN lc_pdd_enter_orders pdd on u1.id = pdd.app_id
                LEFT JOIN lc_jd_enter_orders jd on u1.id = jd.app_id
                LEFT JOIN lc_shop_orders_one shop on u1.id = shop.app_id
                LEFT JOIN lc_alimama_info_new alimama on u1.id = alimama.app_id
                LEFT JOIN lc_taobao_enter_order taobao on alimama.relation_id = taobao.relation_id
                where u1.create_time >= {$start_time} and u1.create_time < {$end_time} and u1.parent_id = {$app_id}
                GROUP BY id";
        $key = 'invite_seven_child_users_';
//        $res = Cache::get($key . $app_id);
//        if(empty($res)){
            $list = DB::connection('_app38')->table(DB::raw("($sql) cc"))->paginate(10000);
            $data = $list->items();
            foreach ($data as $key => $item) {
                $item->id = CommonFunction::userAppIdCompatibility($item->id);
                $item->phone = preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $item->phone);
                if (self::check($item->upt, [3, 4, 9]) ||
                    self::check($item->tpt, [3, 12, 14]) ||
                    self::check($item->spt, [1, 2, 3]) ||
                    self::check($item->ppt, [1, 2, 3, 5]) ||
                    self::checkJd($item->jpt)){
                    $item->real_flag = true;
                } else {
                    $item->real_flag = false;
                }
                unset($item->ppt,$item->spt,$item->tpt,$item->upt, $item->jpt);
            }
            $result = [
                'current_page' => $list->currentPage(),
                'last_page' => $list->lastPage(),
                'data' => $data,
                'total' => $list->total(),
            ];
//            Cache::put($key . $app_id, $res, 3);
//        } else {
//            $result = $res;
//        }

        return $result;
    }

    /**
     * (pdd.order_status in (1,2,3,5)
     * or (jd.validCode >= 17 and jd.frozenSkuNum >= 0 and jd.skuReturnNum >= 0)
     * or shop.`status` in (1,2,3)
     * or taobao.`tk_status` in (3, 12, 14)
     * or uon.`status` in (3,4,9))
     * GROUP BY id;
     * @param $arr
     * @return bool
     */
    static function check($arr, $status)
    {
        if (empty($arr)) {
            return false;
        }
        $arr = explode(',', $arr);
        return count(array_intersect($status, $arr)) > 0;
    }

    static function checkJd($arr)
    {
        if (empty($arr)) {
            return false;
        }
        $arr = explode(',', $arr);
        foreach ($arr as $item) {
            if ($item >= 17) {
                return true;
            }
        }
        return false;
    }

}