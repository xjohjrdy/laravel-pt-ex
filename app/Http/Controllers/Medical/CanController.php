<?php

namespace App\Http\Controllers\Medical;

use App\Entitys\App\MedicalCanShow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CanController extends Controller
{

    public function getRegular(MedicalCanShow $medicalCanShow)
    {
        $no_color = $medicalCanShow->getOneThing(4);
        $have_color = $medicalCanShow->getOneThing(5);
        return $this->getResponse([
            'no_color' => $no_color,
            'have_color' => $have_color,
        ]);
    }

    public function getIndexRegular(MedicalCanShow $medicalCanShow)
    {
        $content = $medicalCanShow->getOneThing(1);
        return view('medical.regular', ['content' => $content->context]);
    }

    public function getCanKnow(MedicalCanShow $medicalCanShow)
    {
        $content = $medicalCanShow->getOneThing(2);
        return view('medical.regular', ['content' => $content->context]);
    }

    public function getBookingKnow(MedicalCanShow $medicalCanShow)
    {
        $content = $medicalCanShow->getOneThing(3);
        return view('medical.regular', ['content' => $content->context]);
    }
}
