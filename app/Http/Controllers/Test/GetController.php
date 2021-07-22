<?php

namespace App\Http\Controllers\Test;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_id = $request->header('id');
        $start = $request->header('start');
        $end = $request->header('end');
        $groupId = $request->header('groupid');
        $time_start = time();
        for ($i=1;$i<500;$i++) {
            $sql = "
SELECT count(`uid`) as res FROM `pre_aljbgp_order` 
WHERE uid in
(
select uid  from `pre_common_member`
WHERE 
(pt_pid in
  (select pt_id from `pre_common_member` where pt_pid in
    (SELECT pt_id from `pre_common_member` where pt_pid = {$i})
  )
or pt_pid in (SELECT pt_id from `pre_common_member` where pt_pid = {$i})
or pt_pid = {$i}
)
AND `groupid` = " . $groupId . ")
and `confirmdate` between {$start} and {$end}
AND `groupid` = " . $groupId . "
        ";
            $res[] = DB::connection('a1191125678')->select($sql);
            $time_end = time();
            if ($i ==50)
            {
                dd($time_end-$time_start);
            }
        }

        dd($res);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
