<?php
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\ApiException;
use App\Entitys\App\AppUserInfo;
use App\Entitys\Ad\AdUserInfo;

/**
 * 用户业务基类
 */
class UserAuthContoller extends Controller
{
    private $user;
    private $ad_user;
    protected $params = [];
    protected $request;
    
    /**
     * 实例初始化校验
     * @param Request $request
     * @throws ApiException
     */
    private function init(Request $request)
    {
        $this->request = $request;
        
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
        
        $this->params = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required_without:user_id',
            'user_id' => 'required_without:app_id',
        ];
        
        $validator = Validator::make($this->params, $rules);
        
        if ($validator->fails()) {
            throw new ApiException('错误认证', 401);
        }
        if ( empty($this->user) ) {
            $user_id = isset( $this->params['app_id'] ) ? $this->params['app_id'] : $this->params['user_id'];
            
            $this->user = AppUserInfo::find($user_id);
            
            if ( empty($this->user) ) {
                throw new ApiException('用户不存在');
            }
            
            if ( $this->user->status == 2 ) {
                throw new ApiException('账号已停用', 403);
            }
        }
    }
    
    /**
     * 获取APP用户ID
     * @param Request $request
     * @return int
     */
    protected function getUserId(Request $request)
    {
        if ( $this->user instanceof AppUserInfo ) {
            return $this->user->id;
        }
        
        $this->init($request);
        
        return $this->user->id;
    }
    
    /**
     * 获取APP用户实例
     * @param Request $request
     * @return AppUserInfo
     */
    protected function getUser(Request $request)
    {
        if ( $this->user instanceof AppUserInfo ) {
            return $this->user;
        }
        
        $this->init($request);
        
        return $this->user;
    }
    
    /**
     * 获取联盟用户实例
     * @param Request $request
     * @return AdUserInfo
     */
    protected function getAdUser(Request $request)
    {
        if ( $this->ad_user instanceof AdUserInfo ) {
            return $this->ad_user;
        }
        
        $this->init($request);
        
        $adUserInfo = new AdUserInfo();
        $this->ad_user = $adUserInfo->appToAdUserId($this->user->id);
        
        if ( empty($this->ad_user) ) {
            throw new ApiException('联盟用户不存在');
        }
        
        return $this->ad_user;
    }
    
    /**
     * 获取联盟用户UID
     * @param Request $request
     * @return int
     */
    protected function getAdUserId(Request $request)
    {
        if ( $this->ad_user instanceof AdUserInfo ) {
            return $this->ad_user->uid;
        }
        
        $this->init($request);
        
        $adUserInfo = new AdUserInfo();
        $this->ad_user = $adUserInfo->appToAdUserId($this->user->id);
        
        if ( empty($this->ad_user) ) {
            throw new ApiException('联盟用户不存在');
        }
        
        return $this->ad_user->uid;
    }
}