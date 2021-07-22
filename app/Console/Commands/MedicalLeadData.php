<?php

namespace App\Console\Commands;

use App\Entitys\App\MedicalCity;
use App\Entitys\App\MedicalHospital;
use App\Exceptions\ApiException;
use App\Services\ZhongKang\ZhongKangServices;
use Illuminate\Console\Command;

class MedicalLeadData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MedicalLeadData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '医疗城市与机构数据导入';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $ZhongKangServices = new ZhongKangServices();
            $data_isv_citys = $ZhongKangServices->getIsvCitys();
            $arr_data_isv_citys = json_decode($data_isv_citys, true);
            if (empty($arr_data_isv_citys['data'])){
                $this->info('==城市数据获取失败==');
                die;
            }
            foreach ($arr_data_isv_citys['data'] as $v) {
                $obj_medical_city = new MedicalCity();
                $res_city = $obj_medical_city->where('area_code', $v['areacode'])->first();
                if ($res_city) {
                    $res_city->city_name = $v['cityname'];
                    $res_city->save();
                } else {
                    $obj_medical_city->area_code = $v['areacode'];
                    $obj_medical_city->city_name = $v['cityname'];
                    $obj_medical_city->save();
                }
            }
            $data_isv_units = $ZhongKangServices->getIsvUnits();
            $arr_data_isv_units = json_decode($data_isv_units, true);
            if (empty($arr_data_isv_units['data'])){
                $this->info('==机构数据获取失败==');
                die;
            }
            foreach ($arr_data_isv_units['data'] as $v) {
                $obj_medical_hospital = new MedicalHospital();
                $res_hospital = $obj_medical_hospital->where('ut_id', $v['ut_id'])->first();
                if ($res_hospital) {
                    $res_hospital->title = $v['ut_title'];
                    $res_hospital->addr = $v['ut_addr'];
                    $res_hospital->level = $v['ut_level'];
                    $res_hospital->full_areaname = $v['ut_full_areaname'];
                    $res_hospital->opentime = $v['ut_opentime'];
                    $res_hospital->lat = $v['ut_lat'];
                    $res_hospital->lon = $v['ut_lon'];
                    $res_hospital->l_img = 'https://images.viptijian.com'.$v['ut_l_img'];
                    $res_hospital->domain = $v['ut_domain'];
                    $res_hospital->areacode = $v['ut_areacode'];
                    $res_hospital->save();
                } else {
                    $obj_medical_hospital->ut_id = $v['ut_id'];
                    $obj_medical_hospital->title = $v['ut_title'];
                    $obj_medical_hospital->addr = $v['ut_addr'];
                    $obj_medical_hospital->level = $v['ut_level'];
                    $obj_medical_hospital->full_areaname = $v['ut_full_areaname'];
                    $obj_medical_hospital->opentime = $v['ut_opentime'];
                    $obj_medical_hospital->lat = $v['ut_lat'];
                    $obj_medical_hospital->lon = $v['ut_lon'];
                    $obj_medical_hospital->l_img = 'https://images.viptijian.com'.$v['ut_l_img'];
                    $obj_medical_hospital->domain = $v['ut_domain'];
                    $obj_medical_hospital->areacode = $v['ut_areacode'];
                    $obj_medical_hospital->save();
                }
            }
            $this->info('==更新数据成功==');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
