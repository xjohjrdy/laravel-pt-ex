<?php

namespace App\Http\Controllers\Material;

use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\MaterialFriendLibrary;
use App\Entitys\App\MaterialTaoLibrary;
use App\Entitys\App\MaterialTeacherLibrary;
use App\Entitys\App\MaterialTeacherTopic;
use App\Entitys\App\MaterialTeacherType;
use App\Exceptions\ApiException;

use App\Services\Alimama\BigWashUser;
use App\Services\TbkCashCreate\TbkCashCreateServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{

    public function topBanner()
    {
        $img = "https://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/image/u3001.png";
        return $this->getResponse($img);
    }

    /*
     * selectness
     */
    public function selectness(Request $request, MaterialTaoLibrary $taoLibrary, AlimamaInfo $alimamaInfo, TbkCashCreateServices $cashCreateServices)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'integer',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $data_list = $taoLibrary->getValidList();

        //获取成长值比例 计算次月最大送的成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');

        $service_dataoke = new BigWashUser();
        foreach ($data_list->items() as &$v) {
            $v->growth_value_new_vip = (string)round($v->tkmoney_vip / $num_growth_value, 2);
            $v->growth_value_new_normal = (string)round($v->tkmoney_general / $num_growth_value, 2);
            $params = [
                'goodsId' => $v->good_id,
            ];
            $rid = $alimamaInfo->where('app_id', $post_data['app_id'])->value('relation_id');
            if ($rid) {
                $share_url_change = $service_dataoke->newUrlChange($params);
                $joint_share_url_change = $share_url_change . '&relationId=' . $rid;
                $tbk_command = $cashCreateServices->getTpwdCreate($v->title, $joint_share_url_change);
                $tbk_command = @$tbk_command['data']['model'];
                $v->context_key = $v->context_key . $tbk_command;
            }else{
                $v->context_key = $v->context_key . '[无]';
            }
        }

        return $this->getResponse($data_list);
    }

    /*
     * circle of friends
     */
    public function friends(Request $request, MaterialFriendLibrary $friendLibrary)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'integer',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $data_list = $friendLibrary->getValidList();

        return $this->getResponse($data_list);
    }

    /*
     * counter
     */
    public function counter(Request $request, MaterialFriendLibrary $friendLibrary, MaterialTaoLibrary $taoLibrary)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'integer',
            'type' => Rule::in([1, 2]),
            'id' => 'integer'//1 报销计数器 2 发朋友圈计数器
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        if ($post_data['type'] == 1) {
            $taoLibrary->counterAdder($post_data['id']);
        } elseif ($post_data['type'] == 2) {
            $friendLibrary->counterAdder($post_data['id']);
        }

        return $this->getResponse('ok');
    }

    /**
     * get index info
     * @param Request $request
     * @param MaterialTeacherLibrary $materialTeacherLibrary
     * @param MaterialTeacherTopic $materialTeacherTopic
     * @param MaterialTeacherType $materialTeacherType
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, MaterialTeacherLibrary $materialTeacherLibrary, MaterialTeacherTopic $materialTeacherTopic, MaterialTeacherType $materialTeacherType)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $type = $materialTeacherType->getAll();
            $topic = $materialTeacherTopic->getOne();
            $topic_info = null;
            if (!empty($topic)) {
                $topic_info = $materialTeacherLibrary->getByTopic($topic->id);
            }
            return $this->getResponse([
                'banner' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E4%BA%A4%E6%98%93%E5%B8%82%E5%9C%BA/2xsmall2e.png',
                'type' => $type,
                'topic' => $topic,
                'topic_info' => $topic_info,
                'share_url' => 'http://ax.10jz.cn/about/#/',
            ]);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    public function getDown(Request $request, MaterialTeacherLibrary $materialTeacherLibrary)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $hot = $materialTeacherLibrary->getByHot();
            return $this->getResponse($hot);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * for type
     * @param Request $request
     * @param MaterialTeacherLibrary $materialTeacherLibrary
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getTypeInfo(Request $request, MaterialTeacherLibrary $materialTeacherLibrary)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'type_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $info = $materialTeacherLibrary->getByType($arrRequest['type_id']);

            return $this->getResponse($info);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getSearch(Request $request, MaterialTeacherLibrary $materialTeacherLibrary)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'title' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $info = $materialTeacherLibrary->getBySearch($arrRequest['title']);

            return $this->getResponse($info);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * for topic all
     * @param Request $request
     * @param MaterialTeacherLibrary $materialTeacherLibrary
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function topicInfo(Request $request, MaterialTeacherLibrary $materialTeacherLibrary)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'topic_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $info = $materialTeacherLibrary->getByAllTopic($arrRequest['topic_id']);

            return $this->getResponse($info);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function libraryInfo(Request $request, MaterialTeacherLibrary $materialTeacherLibrary)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'library_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $info = $materialTeacherLibrary->getOne($arrRequest['library_id']);
            $materialTeacherLibrary->click($arrRequest['library_id']);
            $info->share_url = 'http://ax.k5it.com/about/#/';
            return $this->getResponse($info);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * it is good !
     * @param Request $request
     * @param MaterialTeacherLibrary $materialTeacherLibrary
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function good(Request $request, MaterialTeacherLibrary $materialTeacherLibrary)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'library_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $materialTeacherLibrary->good($arrRequest['library_id']);
            return $this->getResponse('点赞成功');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
