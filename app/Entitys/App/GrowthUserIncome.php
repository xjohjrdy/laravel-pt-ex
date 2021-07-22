<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class GrowthUserIncome extends Model
{
    //
    protected $connection = 'app38';
    protected $table = 'lc_growth_user_income';
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

    public function getUserIncome($app_id, $month)
    {
        return $this->where(['app_id' => $app_id, 'dateline' => $month])->where('id', '>', 47976)->first();
    }

    public function clearIncomeByMonth($month)
    {
        $this->where(['dateline' => $month])->update([
            'growth_sale_one' => 0, // 销售收入记录-销售收入
            'growth_sale_two' => 0, // 销售收入记录-服务费
            'growth_circle_one' => 0, // 本人佣金记录-圈子佣金
            'growth_taobao_one' => 0, // 本人佣金记录-淘宝佣金
            'growth_jd_one' => 0, // 本人佣金记录-京东佣金
            'growth_pdd_one' => 0, // 本人佣金记录-拼多多佣金
            'growth_card_one' => 0, // 本人佣金记录-信用卡佣金
            'growth_shop_one' => 0, // 本人佣金记录-爆款商城佣金
            'growth_article_one' => 0, // 本人佣金记录-广告包佣金
            'growth_circle_two' => 0, // 团队购物服务费记录-圈子服务费
            'growth_taobao_two' => 0, // 团队购物服务费记录-淘宝预估服务费
            'growth_jd_two' => 0, // 团队购物服务费记录-京东预估服务费
            'growth_pdd_two' => 0, // 团队购物服务费记录-拼多多预估服务费
            'growth_card_two' => 0, // 团队购物服务费记录-信用卡预估服务费
            'growth_shop_two' => 0, // 团队购物服务费记录-爆款商城预估服务费
            'growth_article_two' => 0, // 团队购物服务费记录-广告包服务费
        ]);
    }

    /**
     * 查找指定数据，并更新指定列的数据
     * @param $app_id
     * @param $month
     * @param $column_title
     * @param $add_value
     * @return GrowthUserIncome|Model|null
     */
    public function createOrUpdateColumn($app_id, $month, $column_title, $add_value)
    {
        $res = $this->where(['app_id' => $app_id, 'dateline' => $month]);
        if ($res->exists()) {
            $res->update([$column_title => DB::raw($column_title . " + " . $add_value)]);
        } else {
            $this->create([
                'app_id' => $app_id,
                'dateline' => $month,
                $column_title => $add_value
            ]);
        }

    }
}
