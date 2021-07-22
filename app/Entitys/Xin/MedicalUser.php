<?php

namespace App\Entitys\Xin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalUser extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_medical_user';
    use SoftDeletes;

    /**
     * ��Ҫ��ת�������ڵ����ԡ�
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * ���ɱ�������ֵ�����ԡ�
     *
     * @var array
     */
    protected $guarded = [];
}
