<?php

namespace App\Classes;

use Illuminate\Support\Facades\DB;

class PointHelp
{

    private $table_member = 'member';
    private $table_anchor = 'anchor';
    private $table_contribution = 'contribution';



    public function __construct()
    {

    }

    //取得單一會員點數總數
    static function getPoint($mem_id){
        $query_member = "SELECT `point` FROM member WHERE member_id=:member_id";
        $row = DB::select($query_member,['member_id' => $mem_id]);
        return $row[0]->point;
    }

    //取得單一主播點數總數
    private static function getPoint_anc($id){
        $query= "SELECT point FROM anchor WHERE id=:id";
        $row = DB::select($query,[':id' => $id]);
        if(isset($row[0])){
            return $row[0]->point;
        }else if(!isset($row)){
            return null;
        }
    }

    //錢轉點
    public function MoneyToPoint($mem_id,$point){

        $orig_point = $this->getPoint($mem_id);
        $totalpoint = $orig_point+$point;
        DB::update("UPDATE member set point =:point WHERE member_id =:member_id",['point' => $totalpoint,'member_id' => $mem_id]);
    }


    //打賞轉點
    public function Contribution($ary){
        extract($ary);
        $member_point = $this->getPoint($mem_id);
        $anchor_point = $this->getPoint_anc($anc_id);
        if($member_point>=$point){
            $member_point -= $point;
            $anchor_point += $point;
            DB::update("UPDATE member set point =:point WHERE member_id =:member_id",['point' => $member_point,'member_id' => $mem_id]);
            DB::update("UPDATE anchor set point =:point WHERE id=:anc_id",['point' => $anchor_point,'anc_id' => $anc_id]);
            $contribution = array($anc_id, $mem_id, $point, $content);
            DB::insert('INSERT INTO contribution (anchor_id, member_id, point, contents) VALUES (?,?,?,?)',$contribution);
            $anc_name = DB::select("SELECT name FROM anchor WHERE id=:id",[':id' => $anc_id]);
            if(!isset($anc_name) || count($anc_name)==0){
                return response()->json(array(
                    'success'   =>  false,
                    'result'    =>  null,
                    'code'      =>  4,
                    'message'   =>  'Contributed failed'
                ));
            }
            $ary["member_point"] = $member_point;
            $ary["anc_name"] = $anc_name[0]->name;

            return array(
                'success' => true,
                'result' => $ary,
                'code' => 1,
                'message' => "Contributed successfully"
            );

        }else if($member_point<$point)
            $ary["member_point"] = $member_point;
            return array(
                'success' => false,
                'result' => $ary,
                'code' => 2,
                'message' => "點數餘額不足"
            );
    }

}