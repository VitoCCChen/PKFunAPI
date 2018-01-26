<?php

//traits CoderFbHelp
namespace App\Classes;


class FBHelper
{

    private $fb_userdata;
    private $fb_name;
    private $fb_id;
    private $fb_email;

    public function __construct()
    {
    }

    public function init($access_token)
    {

        $fb = new \Facebook\Facebook([
            'app_id' => '450483541741065',
            'app_secret' => 'e7035df44ddc186382c1c1a888de917c',
            'default_graph_version' => 'v2.10',
            //'default_access_token' => '{access-token}', // optional
        ]);



        try {
            // Get the \Facebook\GraphNodes\GraphUser object for the current user.
            // If you provided a 'default_access_token', the '{access-token}' is optional.
            $response = $fb->get('/me?fields=email,name', $access_token);
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            $result = array(
                'success' => false,
                'result' => '',
                'message' => 'Graph returned an error: ' . $e->getMessage()
            );
            return $result;


        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            $result = array(
                'success' => false,
                'result' => '',
                'message' => 'Facebook SDK returned an error: ' . $e->getMessage(),
            );
            return $result;


        }


        $me = $response->getGraphUser();
        $this->fb_userdata = $response->getGraphNode()->asArray(); //$userData["id"]為FB的ID
        $this->fb_name = $me->getFirstName().$me->getLastName();
        $this->fb_id = $me->getId();
        $this->fb_email = $me->getEmail();


    }

    public function getUserArray(){
        return $this->fb_userdata;
    }

    public function getUserId(){
        return $this->fb_id;
    }

    public function getUserName(){
        return $this->fb_name;
    }

    public function getUserEmail(){
        return $this->fb_email;
    }

}