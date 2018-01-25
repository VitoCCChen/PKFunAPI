<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class EpisodeController extends Controller
{
    public function getEpisode(Request $request)
    {
        isset($request->id)?$id=$_GET["id"]:$id=null;
        isset($request->page_num)?$lim=$request->page_num:$lim=5;
        isset($request->page)?$page=$request->page:$page=1;

        $result =array(
            'success' => "true",
            'result' => '',
            'message' => "request successful"
        );
        try{
            isset($id) ?
                $row_count = count( DB::table('program_episode')->select('ep_id')->where('ep_pgram_id', $id)->get() ):
                $row_count = count( DB::table('program_episode')->select('ep_id')->get() );

            $row['data_count'] =  strval($row_count);
            $row['page_num'] = $lim;
            ($row_count%$lim > 0)?$row['total_page']=strval(ceil($row_count/$lim)):$row['total_page']=strval($row_count/$lim);
            $row['page'] = $page;
            $begin=$lim*($page-1);

            $queryString = DB::table('program_episode')->select('ep_id', 'pgram_name', 'ep_anchors', 'ep_start_time', 'ep_end_time' ,
                DB::raw('SEC_TO_TIME( TIMESTAMPDIFF(SECOND, ep_start_time, ep_end_time) ) as episode_length'),
                DB::raw('(SELECT COUNT(*) FROM program_chatroom WHERE cl_record_id=ep_id) as chat_count'),
                'ep_updatetime', 'ep_lastmanage')
                ->leftjoin('program', 'ep_pgram_id', '=', 'pgram_id')
                ->orderBy('ep_createtime')
                ->offset($begin)
                ->limit($lim);

            if (isset($id)) {
                $row['data'] = $queryString
                    ->where('ep_pgram_id', $id)->get();
            } else {
                $row['data'] = $queryString->get();
            }

            $anchors = DB::table('anchor')->select('id as value', 'name')->get()->toArray();

            for($i=0; $i<count($row['data']); $i++){
                $ary_numbers = explode(",", $row['data'][$i]->ep_anchors);
                $newary = array();
                foreach ($ary_numbers as $val) {
                    $tmpary = array();
                    $key = array_search( $val, array_column($anchors, 'value') );
                    $tmpary['id'] = $val;
                    $tmpary['name'] = $anchors[$key]->name;
                    array_push($newary, $tmpary);
                }
                $row['data'][$i]->ep_anchors = $newary;
            }

            $result["result"]=$row;
            return $result;
        }
        catch(Exception $e) {
            $result['success'] = "false";
            $result['message'] = $e->getMessage();
            return $result;
        }
    }
}
