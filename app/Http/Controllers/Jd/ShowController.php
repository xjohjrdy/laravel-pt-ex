<?php

namespace App\Http\Controllers\Jd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowController extends Controller
{
    /**
     * 规则get
     */
    public function getJdRule()
    {
        return view('jd.rule');
    }

    /**
     * 团队规则get
     */
    public function getJdTeamRule()
    {
        return view('jd.team');
    }
}
