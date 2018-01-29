<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\PointHelp;

class MemberController extends Controller
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        if(!session()->has('memberData')){
            return response()->json(array(
                'success'   =>  false,
                'result'    =>  null,
                'code'      =>  3,
                'message'   =>  'please login'));
        }
        $memberData = session()->get('memberData')[0];
        $mem_id = $memberData->member_id;
        $row = PointHelp::getPoint($mem_id);
        if(!isset($row) || is_nan($row)){
            return response()->json(array(
                'success' => false,
                'result' => '',
                'message' => "request filed"
            ));
        }
        return response()->json(array(
            'success' => true,
            'result' => $row,
            'message' => "request successful"
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
}
