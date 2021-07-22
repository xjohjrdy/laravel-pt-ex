<?php

namespace App\Http\Controllers\News;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowController extends Controller
{
    /**
     * 规则get
     */
    public function getRule()
    {
        return view('news.rule');
    }

    /**
     * 团队规则get
     */
    public function getTeamRule()
    {
        return view('news.team');
    }
}
