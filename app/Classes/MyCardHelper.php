<?php
namespace App\Classes;

use Illuminate\Support\Facades\DB;
use App\Classes\PointHelp;

class MyCardHelper {



    public function __construct()
    {

    }


    public function AddMycard($ary){
        extract($ary);
        $mycard_data = array(
            'FacTradeSeq' => $FacTradeSeq,
            'TradeSeq' => $TradeSeq,
            'ServerId' => $ServerId,
            'member_id' => $member_id,
            'PaymentType' => $PaymentType,
            'ItemCode' => $ItemCode,
            'product_id' => $product_id,
            'Amount' => $Amount,
            'Currency' => $Currency,
            'Created_date' => $Created_date,
            'agent_id' => $agent_id,
            'AuthCode' => $AuthCode,
            'ReturnCode' => $ReturnCode
        );
        DB::insert('INSERT INTO mycard (FacTradeSeq, TradeSeq, ServerId, member_id, PaymentType, ItemCode, product_id, Amount, Currency, Created_date, agent_id, AuthCode, ReturnCode) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',$mycard_data);

    }


    function MycardProcess($ary){


        $table = 'mycard';
        $datetime = date('Y-m-d H:i:s',time());
        extract($ary);
        if(!isset($Redeposit)){
            $Redeposit = 0;
            $db = Database::DB();
            $data = array(
                'ReturnCode' => $ReturnCode,
                'PaymentType' => $PaymentType,
                'MyCardTradeNo' => $MyCardTradeNo,
                'MyCardType' => $MyCardType,
                'PromoCode' => $PromoCode,
                'Pay_time' => $datetime,
                'Redeposit' => $Redeposit
            );
        }else{
            $db = Database::DB();
            $data = array(
                'ReturnCode' => $ReturnCode,
                'PaymentType' => $PaymentType,
                'Pay_time' => $datetime,
                'Redeposit' => $Redeposit
            );
        }

        //更新交易狀態
        $db->query_update($table, $data, " FacTradeSeq='$FacTradeSeq'");

        $query_confirm = "SELECT AuthCode, member_id, product_id, Amount, agent_id FROM mycard WHERE FacTradeSeq=:FacTradeSeq";
        $row = $db->query_first($query_confirm,[':FacTradeSeq' => $FacTradeSeq]);

        //mycard請款
        $Payresult = $this->paymentConfirm($row['AuthCode']);

        if($Payresult==1){

            //取得會員所屬平台
            $mem_id = $row['member_id'];
            $query_member = "SELECT platform_id FROM member WHERE member_id=:member_id";
            $member_row = $db->query_first($query_member,[':member_id' => $mem_id]);
            $platform_id = $member_row['platform_id'];


            //取得產品總點數
            $ProductId = $row['product_id'];
            $query_point = "SELECT point, bonus FROM product WHERE product_id=:product_id";
            $rows = $db->query_first($query_point,[':product_id' => $ProductId]);
            $totalpoint = $rows['point']+$rows['bonus'];

            $pointhelp = new PointHelp();
            $pointhelp->MoneyToPoint($mem_id,$totalpoint);

            //更新mycard狀態
            $mycard_data = array(
                'PayResult' =>  $Payresult,
                'Check_time' => $datetime,
            );

            $db->query_update($table,$mycard_data," FacTradeSeq='$FacTradeSeq'");


            $depo_data = array(
                'member_id' => $mem_id,
                'platform_id' => $platform_id,
                'product_id' => $ProductId,
                'money' => $row["Amount"],
                'point' => $totalpoint,
                'deposit_pay_id' => 1,
                'pay_code' => $FacTradeSeq,
                'pay_id' => null,
                'status' => 1,
                'updated_time' => $datetime,
                'check_time' => $datetime,
                'agent_id' => $row["agent_id"]
            );
            //插入入款管理資料表
            $table_deposit = 'deposit';
            $db->query_insert($table_deposit,$depo_data);
            $db->close();
            return "PaymentOK";
        }else{
            $db->close();
            return "error";
        }


    }

    //取得mycard交易授權碼
    function getAuthCode($ary){

        extract($ary);

        $Encodedata = substr(urlencode($ProductName),strpos(urlencode($ProductName),"%"));
        $data = $FacServiceId.$FacTradeSeq.$TradeType.$ServerId.$CustomerId.$PaymentType.$ItemCode.$Encodedata.$Amount.$Currency.$SandBoxMode.$FacKey;
        $Hash = hash('sha256', $data);
        $datatosent = array('FacServiceId'=>$FacServiceId,'FacTradeSeq'=>$FacTradeSeq,'TradeType'=>$TradeType,'ServerId'=>$ServerId,'CustomerId'=>$CustomerId,'PaymentType'=>$PaymentType,'ItemCode'=>$ItemCode,'ProductName'=>$ProductName,'Amount'=>$Amount,'Currency'=>$Currency,'SandBoxMode'=>$SandBoxMode,'Hash'=>$Hash);
        $url = "https://test.b2b.mycard520.com.tw/MyBillingPay/api/AuthGlobal";

        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datatosent));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);

        $output = curl_exec($ch);
        curl_close($ch);


        $opt=json_decode($output);
        echo $data."<br>";
        echo $Hash."<br>";

        $Result = $opt->ReturnCode;
        echo $Result;
        if($Result!=1){
            return array(
                'success' => false,
                'result' => '',
                'msg' => 'Get AuthCode Failed.'
            );
        }else{

            $AuthCode = $opt->AuthCode;
            $TradeSeq = $opt->TradeSeq;
            $AuthUrl = "https://test.mycard520.com.tw/MyCardPay?AuthCode=".$AuthCode;
            $ReturnCode = 0;

            $ary = array(
                'FacTradeSeq' => $FacTradeSeq,
                'TradeSeq' => $TradeSeq,
                'ServerId' => $ServerId,
                'member_id' => $CustomerId,
                'PaymentType' => $PaymentType,
                'ItemCode' => $ItemCode,
                'product_id' => $ProductName,
                'Amount' => $Amount,
                'Currency' => $Currency,
                'Created_date' => $Created_date,
                'AuthCode' => $AuthCode,
                'ReturnCode' => $ReturnCode,
                'agent_id' => $agent_id
            );

            try{
                //新增一筆mycard交易
                self::AddMycard($ary);

                return array(
                    'success' => true,
                    'result' => $AuthUrl,
                    'msg' => 'Get AuthCode Succeed.'
                );


            }catch (Exception $exception){
                echo $exception->getMessage();
            }
        }
    }

    private function paymentConfirm($authcode){
        $url = "https://test.b2b.mycard520.com.tw/MyBillingPay/api/PaymentConfirm?AuthCode=".$authcode;
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);

        $output = curl_exec($ch);
        $opt = json_decode($output);
        curl_close($ch);
        return $opt->ReturnCode;
    }



}