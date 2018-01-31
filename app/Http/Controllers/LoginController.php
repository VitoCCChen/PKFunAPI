<?php

namespace App\Http\Controllers;

use App\Classes\FBHelper;
use App\Http\Requests\FbLogin;
use App\Http\Requests\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Member;



class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */




    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }



    public function Login(Login $request){
        $account = $request->account;
        $pw = md5($request->password);
        if(session()->has('memberData')){
            return response()->json(array(
                'success' => true ,
                'result' => session()->get('memberData')[0],
                'code' => 4,
                'message' => 'Logged in already.'
            ));
        }
        $memberData = Member::where('member_id',$account)->select('member_id','member_name','email','platform_id','point')->get();
        $num_count = Member::where('member_id',$account)->count();
        if($num_count==0){
            return response()->json(array(
                'success' => false,
                'result' => "",
                'code' => 3,
                'message' => 'Account is not exist.'
            ));
        }
        $password =  Member::where('member_id',$account)->select('member_pw')->get()[0]->member_pw;
        if($pw!=$password){
            return response()->json(array(
                'success' => false,
                'result' => '',
                'code' => 2,
                'message' => "Wrong password.",
            ));
        }

        session(['memberData' => $memberData]);
        return response()->json(array(
            'success' => true,
            'result' => $memberData,
            'code' => 1,
            'message' => "Login successfully."
        ));


    }



    public function LoginWitFb(FbLogin $request){
        //determine logged in already or not.
        $token = $request->accesstoken;
        if(session()->has('memberData')){
            return response()->json(array(
                'success' => true ,
                'result' => '',
                'code' => 4,
                'message' => 'Logged in already.'
            ));
        }
        $fb = new FBHelper();
        $result = $fb->init($token);
        if($result)
            return response()->json($result);
        $fb_memberData = $fb->getUserArray();
        $fb_memberMail = $fb->getUserEmail();
        $fb_memberId = $fb->getUserId();
        $fb_memberName = $fb_memberData['name'];


        $memberData = Member::where('member_id',$fb_memberId)->select('member_id','member_name','email','platform_id','point')->get();
        $counter =  Member::where('member_id', $fb_memberId)->count();
        //if account exist or not
        if($counter>0){
            session(['memberData' => $memberData]);
            return response()->json(array(
                'success' => true,
                'result' => $memberData,
                'code' => 1,
                'message' => 'Login successfully.'
            ));
        }else if($counter == 0){

        //add a new facebook member
        $member = new Member;
        $member->member_id = $fb_memberId;
        $member->member_pw = "";
        $member->member_name = $fb_memberName;
        $member->platform_id = '4';
        $member->agent_id = "";
        $member->email = $fb_memberMail;
        $member->point = 0;
        $member->last_manager = "";
        $member->save();
        session(['memberData' => $memberData]);

        return response()->json(array(
            'success' => true,
            'result' => array(
                'member_id' => $fb_memberId,
                'member_name' => $fb_memberName,
                'email' => $fb_memberMail,
                'platform' => "4",
                "point" => 0
            ),
            'code'=> 1,
            'message' => 'Login successfully, welcome.'
        ));

        }
    }


    public function register(Request $request){
        if(session()->has('memberData')){
            return response()->json(array(
                'success' => false ,
                'result' => '',
                'code' => 2,
                'message' => 'Logged in already.'
            ));
        }
        $account = $request->account;
        $pw = $request->password;
        $nickname = $request->nickname;
        $email = $request->email;
        $agent_id = $request->agent_id;

        if((!isset($account)) || $account=="" || (!isset($pw)) || $pw=="" || (!isset($nickname)) || $nickname=="" || (!isset($account)) || $account==""){
            return response()->json(array(
                'success' => false,
                'result' => '',
                'code' => 3,
                'message' => "System false."
            ));
        }
        //if account exist or not
        $counter =  Member::where('member_id', '=', $account)->count();
        $exist_data = Member::select('member_id','member_name','email','platform_id')->where('member_id', '=', $account)->get();
        if($counter>0){
            return response()->json(array(
                'success' => true,
                'result' => $exist_data,
                'code' => 4,
                'message' => "Account already existed, please log in."
            ));
        }

        //add a member
        $member = new Member;
        $member->member_id = $account;
        $member->member_pw = md5($pw);
        $member->member_name = $nickname;
        $member->platform_id = '';
        $member->agent_id = $agent_id;
        $member->email = $email;
        $member->point = 0;
        $member->last_manager = "";
        $new_mamber = array(
            "member_id" => $member->member_id,
            "member_name" => $member->member_name,
            "email" => $member->email
        );
        $member->save();


        return response()->json(array(
            'success' => true,
            'result' => $new_mamber,
            'code'=> 1,
            'message' => "Registered successfully , please log in."
        ));

    }
}


