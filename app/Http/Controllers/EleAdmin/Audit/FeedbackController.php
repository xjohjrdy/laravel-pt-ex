<?php

namespace App\Http\Controllers\EleAdmin\Audit;

use App\Entitys\App\CommentList;
use App\Entitys\App\OpinionReply;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{

    /*
     * 拉取意见反馈列表
     */
    public function getFeedbackList(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['id'];
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$item] = $params[$item];
                }
            }
            $commentList = new CommentList();
            $list = $commentList->where($wheres)->orderBy('id', 'desc')->paginate($limit);

            $opinionReply = new OpinionReply();
            foreach ($list->items() as &$v) {
                $v->img = explode(',', $v->img);
                $v->opinion = $opinionReply->orderBy('id', 'desc')->where('opinion_id', $v->id)->get();
            }

            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }

    /*
     * 新增回复
     */
    public function sendReply(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'id' => 'required',
                'content' => 'required',
            ];

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }

            $opinionReply = new OpinionReply();
            $commentList = new CommentList();
            $name = $params['from'] ? '心选购小助手' : '我的小助手';
            $opinionReply->create([
                'opinion_id' => $params['id'],
                'content' => $params['content'],
                'name' => $name,
                'app_id' => $params['app_id'],
                'header' => 'http://cdnwhwy.36qq.com/circle/images/logo.png'
            ]);

            $commentList->where('id', $params['id'])->update(['status' => 4, 'ok_time' => time(), 'start' => 0]);

            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }
    }
}