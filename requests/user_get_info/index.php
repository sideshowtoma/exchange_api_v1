<?php
require '../../classes/constants.php';
require '../../classes/functions.php';
require '../../classes/database.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


     //check if session is set
    if(  verify_session_key())
    {
        $data=get_session_info();
        
        $get_account_info= SelectTableOnTwoConditions(user_accounts, 'user_id', $data['_id'], 'user_id', $data['_id'])[0];
        
    
        $account_array=array(
                'wallet_code'=>$get_account_info['wallet_code'],
                'pass_phrase'=> base64_decode($get_account_info['pass_phrase'],true),
                'time_stamp'=>UTCTimeToLocalTime($get_account_info['time_stamp'], '', 'Y-m-d H:i:s', 'd-m-Y H:i:s')
        );
        
        $balance_info=get_account_balance($account_array['wallet_code']);
        $account_array['wallet_balance']=$balance_info;
         //   die(json_encode($get_account_info));
            
        $array=array(
                "user_id"=> $data['_id'],
                "user_email_address"=> $data['email_address'],
                "user_name"=> $data['name'],
                "user_id_or_passport"=> $data['id_or_passport']==1 ? "National ID" : "Passport",
                "user_id_passport_number"=>$data['id_passport_number'],
                "user_telephone_number"=> $data['telephone_number'],
                "user_date_of_birth"=> UTCTimeToLocalTime($data['date_of_birth'], '', 'Y-m-d', 'd-m-Y'),
                "user_gender"=> $data['gender'],
                "user_comments"=> $data['comments'],
                "user_active"=> $data['active']==1 ? "Active" : "Inactive",
                "user_time_stamp"=> UTCTimeToLocalTime($data['time_stamp'], '', 'Y-m-d H:i:s', 'd-m-Y H:i:s'),
                "user_account"=> $account_array
        );
        $response= json_encode(array("check"=>true,"message"=>$array));
          
         
       
        
        
    }
    else 
    {
        $response= json_encode(array("check"=>false,"message"=>"Please provide a valid token."));
    }


echo $response;
 
/*
  json_encode(array(
     "1"=>array( "branch_id"=>"1","branch_name"=>"branch name 1","country"=>"Kenya","state_county_province"=>"state_county_province 1","location"=>"place 1","directions"=>"directions 1","co-ordinates"=>array(),"phone_contacts"=>array("0716214868","0716214868"),"email_contacts"=>array("info@clicksoft.co.ke","info@clicksoft.co.ke")),
     "2"=>array( "branch_id"=>"2","country"=>"Kenya","state_county_province"=>"state_county_province 2","location"=>"place 2","directions"=>"directions 2","co-ordinates"=>array(),"phone_contacts"=>array("0716214868","0716214868"),"email_contacts"=>array("info@clicksoft.co.ke","info@clicksoft.co.ke")),
        
 ));
  */
