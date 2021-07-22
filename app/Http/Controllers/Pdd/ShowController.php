<?php

namespace App\Http\Controllers\Pdd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowController extends Controller
{
    /**
     * 规则get
     */
    public function getJdRule()
    {
        return view('pdd.rule');
    }

    /**
     * 团队规则get
     */
    public function getJdTeamRule()
    {
        return view('pdd.team');
    }
}
