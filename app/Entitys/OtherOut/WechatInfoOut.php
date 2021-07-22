<?php


namespace App\Entitys\OtherOut;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatInfoOut extends Model
{
    //
    protected $connection = 'app38_out';
    protected $table = 'lc_wechat_info';
    use SoftDeletes;

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 用户第一次进入插入的记录
     * @param $access_token
     * @param $expires_in
     * @param $refresh_token
     * @param $openid
     * @param $scope
     * @return $this|Model
     */
    public function loginInsert($access_token, $expires_in, $refresh_token, $openid, $scope)
    {
        $res = $this->create([
            'access_token' => $access_token,
            'expires_in' => $expires_in,
            'refresh_token' => $refresh_token,
            'openid' => $openid,
            'scope' => $scope,
        ]);

        return $res;
    }

    /**
     * 第一次进入，但是已经授权了
     * @param $access_token
     * @param $expires_in
     * @param $refresh_token
     * @param $openid
     * @param $scope
     * @param $union_id
     * @return $this|Model
     */
    public function loginInsertUnion($access_token, $expires_in, $refresh_token, $openid, $scope, $union_id)
    {
        $res = $this->create([
            'access_token' => $access_token,
            'expires_in' => $expires_in,
            'refresh_token' => $refresh_token,
            'openid' => $openid,
            'scope' => $scope,
            'unionid' => $union_id,
        ]);

        return $res;
    }

    /**
     * 利用微信提供的唯一id找到这个已经授权的用户
     * @param $union_id
     * @return Model|null|static
     */
    public function getByUnionId($union_id)
    {
        $res = $this->where(['unionid' => $union_id])->first(['app_id']);
        return $res;
    }

    /**
     * 利用微信提供的单系统唯一id找到这个已经授权的用户
     * @param $open_id
     * @return Model|null|static
     */
    public function getByOpenId($open_id)
    {
        $res = $this->where(['openid' => $open_id])->first(['id', 'app_id']);
        return $res;
    }


    /**
     * 利用id更新绑定信息
     * @param $id
     * @param $app_id
     * @return bool
     */
    public function updateById($id, $app_id)
    {
        return $this->where(['id' => $id])
            ->update([
                'app_id' => $app_id
            ]);
    }

    /**
     * 防止一个账号绑定多个微信
     * @param $app_id
     * @return Model|null|static
     */
    public function getAppId($app_id)
    {
        $res = $this->where(['app_id' => $app_id])->first(['id', 'app_id']);
        return $res;
    }
}
