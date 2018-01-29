<?php

namespace App\Http\Controllers;

use App\Classes\PointHelp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ContributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        if(!session()->has('memberData')){
            return response()->json(array(
                'success'   =>  false,
                'result'    =>  null,
                'code'      =>  3,
                'message'   =>  'please login'));
        }
        $member_id = session()->get('memberData')[0]->member_id;
        $point = $request->point;
        $anchor_id = $request->anchor_id;
        $contents = $request->contents;
        if((!isset($point)) || $point<=0 || $point=='' || (!isset($anchor_id)) || $anchor_id == "" || (!isset($contents)) || $contents == ""){
            return response()->json(array(
                'success'   =>  false,
                'result'    =>  null,
                'code'      =>  4,
                'message'   =>  'Contributed failed'
            ));
        }
        $contribute_ary = array(
            'mem_id'    => $member_id,
            'anc_id'    => $anchor_id,
            'anc_name'  => "",
            'point'     => $point,
            'content'   => $contents
        );
        $contribute = new PointHelp();
        $result = $contribute->Contribution($contribute_ary);

        return $result;






    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
        if(!session()->has('memberData')){
            return response()->json(array(
                'success'   =>  false,
                'count'     =>  null,
                'result'    =>  null,
                'message'   =>  'please login'));
        }
        $memberData = session()->get('memberData');
        $mem_id = $memberData[0]->member_id;
        $contributions = DB::table('contribution')->leftJoin('anchor', 'contribution.anchor_id', '=' , 'anchor.id')->select('contribution.*', 'anchor.name')->where('member_id','=',$mem_id)->get();
        $contir_count = DB::table('contribution')->where('member_id', '=', $mem_id)->count();
        $contribution_ary = [];
        foreach($contributions as $contribution){
            $contribution_ary[] = $contribution;
        }
        return response()->json(array(
            'success'   =>  true,
            'count'     =>  $contir_count,
            'result'    =>  $contribution_ary,
            'message'   =>  'request successful',
            ));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function contribute($mem_id, $anc_id, $point)
    {
//        return response()->json()
    }
}
