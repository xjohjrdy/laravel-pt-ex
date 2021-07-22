<?php

namespace App\Http\Controllers\EleAdmin\PutNew;

use App\Exceptions\ApiException;
use App\Http\Controllers\EleAdmin\BaseController;
use App\Models\EleAdmin\PutNew\Rank as RankModel;
use App\Services\EleAdmin\PutNew\RankService;
use App\Tools\ImageUrlFileHandle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RankController extends BaseController
{
    public function lists(Request $request)
    {
        try {
            $params = $request->all();

            list($users, $pagination) = $this->search($params);

            $records = [];
            if ($users) {
                foreach ($users as &$user) {
                    if ($user->avatar) {
                        $user->avatar_info = ImageUrlFileHandle::setImgUrlData($user->avatar, '头像');
                    }
                }
                $records = $users->toArray();
            }

            $data['records'] = $records;
            $data['pagination'] = $pagination;

            return $this->getResponse($data);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    protected function search($params)
    {
        $showInfo = $params['show_info'] ?? null;
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $offset = ($page - 1) * $limit;

        $recodrs = [];
        $table = 'select all_list.*, @rownum := @rownum + 1 as rank from (select @rownum := 0) r, 
         (
         select l1.*,l2.order_id,l2.reward_id,l2.title,l2.address,l2.real_name,l2.phone,l2.money 
         from lc_put_new_rank_list l1 LEFT JOIN lc_put_new_reward_real l2 on l1.app_id = l2.app_id
            GROUP BY l1.app_id
            ORDER BY l1.success_add DESC
            LIMIT 50) as all_list
        where all_list.deleted_at is null order by all_list.success_add desc';
        $count = DB::connection('_app38')
            ->table(DB::connection('_app38')->raw("({$table}) as rank_list"))
            ->select(DB::connection('_app38')->raw('rank_list.*'))
            ->where(function ($query) use ($showInfo, $params) {
                if ($showInfo) {
                    $query->whereRaw("rank_list.show_info like '{$showInfo}%'");
                }
                if (isset($params['change']) && $params['change'] !== '' && in_array($params['change'], array_keys(RankModel::$changeList))) {
                    $query->whereRaw('rank_list.change = ' . $params['change']);
                }
            })
            ->count();

        if ($count > 0) {
            $recodrs = DB::connection('_app38')
                ->table(DB::connection('_app38')->raw("({$table}) as rank_list"))
                ->select(DB::connection('_app38')->raw('rank_list.*'))
                ->where(function ($query) use ($showInfo, $params) {
                    if ($showInfo) {
                        $query->whereRaw("rank_list.show_info like '{$showInfo}%'");
                    }
                    if (isset($params['change']) && $params['change'] !== '' && in_array($params['change'], array_keys(RankModel::$changeList))) {
                        $query->whereRaw('rank_list.change = ' . $params['change']);
                    }
                })
                ->offset($offset)
                ->limit($limit)
                ->get();
        }

        $pagination = [
            'page' => $page,
            'count' => $count,
            'page_count' => ceil($count/$limit),
            'limit' => $limit
        ];

        return [$recodrs, $pagination];
    }

    public function add(Request $request)
    {
        try {
            $params = $request->all();

            $result = $this->operate($params, 'add');
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            $params = $request->all();

            $result = $this->operate($params, 'edit');
            if ($result['code'] != 200) {
                return $this->getInfoResponse($result['code'], $result['message']);
            }

            return $this->getResponse($result['message']);
        } catch (\Exception $e) {
            return $this->getInfoResponse($e->getCode(), $e->getMessage());
        }
    }

    protected function operate($params, $type)
    {
        $rules = [
            'avatar' => 'required|max:1024',
            'show_info' => 'required|max:1024',
            'success_add' => 'required',
        ];

        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        switch ($type) {
            case 'add':
                $result = RankService::add($params);
                break;
            case 'edit':
                $result = RankService::edit($params);
                break;
            default:
                $result = ['code' => 1000, 'message' => '操作异常'];
                break;
        }

        return $result;
    }
}