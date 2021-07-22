<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class MaterialTaoLibrary extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_material_tao_library';
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

    public function getValidList()
    {
        $int_current_time = time();
        $results = $this->orderBy('order', 'desc')
            ->where('start_time', '<', $int_current_time)
            ->where('end_time', '>', $int_current_time)
            ->paginate(20, ["id", "img", "name", "from_type", "start_time", "order", "title", "forward", "context", "context_img", "good_id", "share_tkmoney_general", "share_tkmoney_vip", "tkmoney_general", "tkmoney_vip", "price", "coupon_price", "coupon", "sale_number", "created_at", "context_key"]);

        $results->map(function ($model) {
            $model->created_at_v = date('Y-m-d H:i', $model->start_time);
        });

        return $results;
    }

    public function counterAdder($id)
    {
        $this->where('id', $id)->update([
            'forward' => DB::raw('forward + 1'),
            'real_forward' => DB::raw('real_forward + 1'),
        ]);
        return true;
    }
}
