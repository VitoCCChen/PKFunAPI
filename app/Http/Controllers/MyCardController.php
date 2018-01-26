<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\MyCardHelper;
use Illuminate\Support\Facades\DB;

class MyCardController extends Controller
{
    public function getAuthCode(Request $request){
        //
        if(!session()->has('memberData')){
            return response()->json(array(
                'success'   =>  false,
                'result'    =>  null,
                'code'      =>  3,
                'message'   =>  'please login'));
        }

        if(!isset($request->Amount) || $request->Amount == "" || !is_numeric($request->Amount)
                    || !isset($request->Currency) || $request->Currency == ""
                    || !isset($request->ProductName) || $request->ProductName == ""){

            return response()->json(array(
                'success' => false,
                'result' => '',
                'code' => 2,
                'message' => 'Failed to get AuthCode .'));
        }
        $customerId = session()->get('memberData')[0]->member_id;
        $amount = $request->Amount;
        $currency = $request->Currency;
        $productName = $request->ProductName;
        $agent_id = "";
        if(isset($request->agent_id) && $request->agent_id != ""){
            $agent_id = $request->agent_id;
            $count_agent = DB::table('agent')->where('agent_id', '=', $agent_id)->count();
            if($count_agent == 0){
                return response()->json(array(
                    'success' => false,
                    'result' => '',
                    'code' => 4,
                    'message' => 'Failed to get agent id .'
                ));
            }
        }
        $getauth_ary = array(
            'FacServiceId' => 'luckySG',
            'FacTradeSeq' => uniqid('MC'),
            'TradeType' => "2",
            'ServerId' => "",
            'CustomerId' => $customerId,
            'PaymentType' => "",
            'ItemCode' => "",
            'ProductName' => $productName,
            'Amount' => $amount,
            'Currency' => $currency,
            'SandBoxMode' => true,
            'FacKey' => "B8sqJqY3QFQg8wE2LZ4AxcWQ69v3RUyy",
            'Created_date' => date('Y-m-d H:i:s',time()),
            'agent_id' => $agent_id
        );
        $mycard = new MyCardHelper();
        $result = $mycard->getAuthCode($getauth_ary);
        return $result;
        if($result["success"]== true){
            return response()->json(array(
                'success' => true,
                'result' => $result['result'],
                'code' => 1,
                'message' => 'Get AuthCode successfully.'
            ));
        }else if($result["success"]== false){
            return response()->json(array(
                'success' => false,
                'result' => $result["msg"],
                'code' => 2,
                'message' => 'Failed to get AuthCode .'
            ));
        }
    }
}
