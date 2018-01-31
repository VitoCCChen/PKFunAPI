<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class ProgramController extends Controller
{
    public function GetProgram(Request $request)
    {
        $result = array(
            'success' => true,
            'result' => '',
            'message' => "request successful"
        );

        try {
            isset($request->id) ?
                $row = DB::table('program')->select('*')->where('pgram_id', $request->id)->get() :
                $row = DB::table('program')->select('*')->get();

            $result["result"] = $row;
            return $result;
        } catch (Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
    }
}
