<?php
require '../../classes/constants.php';
require '../../classes/functions.php';
require '../../classes/database.php';
require_once('../../classes/phpmailer/class.phpmailer.php');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//check get or post

$request=get_my_post_get_variables(array('email_address','name','id_or_passport','id_passport_number','telephone_number','date_of_birth','gender','comments'));

//die(json_encode($request));
$email_address=$request['email_address'];
$name=$request['name'];
$id_or_passport=$request['id_or_passport'];
$id_passport_number=$request['id_passport_number'];
$telephone_number=$request['telephone_number'];
$date_of_birth=$request['date_of_birth'];
$gender=$request['gender'];
$comments=$request['comments'];






if(
   isset($email_address) && !empty($email_address) &&     
    isset($name) && !empty($name) &&
    isset($id_or_passport) && !empty($id_or_passport) &&
    isset($id_passport_number) && !empty($id_passport_number) &&
    isset($telephone_number) && !empty($telephone_number) &&
    isset($date_of_birth) && !empty($date_of_birth) &&
    isset($gender) && !empty($gender) &&
    isset($comments) && !empty($comments) 
        )
{
    
       
        $check_email_address= CheckIfExistsTwoColumnsFunction(users_table, 'email_address', 'email_address', $email_address, $email_address);
        
        if($check_email_address==true)
        {
            
        
                
                        if($id_or_passport==1 || $id_or_passport==2   )
                        {
                                if(validateDate($date_of_birth,'Y-m-d')==true )
                                {
                                    if(strtolower($gender)=='male' || strtolower($gender)=='female'  )
                                    {
                                      $password= rand(1001,9999);
                                        $account_type_title="Client";
                                        
                                      // die($password.'==');
                                           // $emailErr = "Invalid email format";
                                            $time_now= storable_datetime_function(time());
                                         
                                            $insert= InsertIntoUsersTable(users_table, $email_address, $name, '1', $id_or_passport, $id_passport_number, $telephone_number,md5($password), $date_of_birth, $comments, $gender, $time_now); 
                                                    
                                            if($insert==true)
                                            {
                                                
                                               
                                               
                                                $get_data= SelectTableOnTwoConditions(users_table, 'email_address', $email_address, 'time_stamp', $time_now)[0];
                                                
                                                    if(ensure_ton_url()==true)//ensure url
                                                    {
                                                        //compile contract, and make wallet
                                                         $creat_wallet=make_wallet_for_me($get_data["_id"]);

                                                        if($creat_wallet['status']==true)
                                                        {
                                                            $body=make_user_invite_email_html($get_data["name"],'Client',$get_data["email_address"],$password,$creat_wallet['data']['seed_phrase'],$creat_wallet['data']['raw_address']);
                                                            mail_sender_function($get_data["email_address"], $get_data["name"], $body, 'Please do not reply to this email', 'Flamingo finance account');
                                                            
                                                            $response= json_encode(array("check"=>true,"message"=>"Success, please check your email for details.","_id"=>$get_data['_id']));
                                                        }
                                                        else
                                                        {
                                                            $response= json_encode(array("check"=>false,"message"=>"Too bad could not create user at this time."));
                                                        }
                                                         
                                                    }
                                                    else
                                                    {
                                                        $response= json_encode(array("check"=>false,"message"=>"Ops could not create user at this time."));
                                                    }


                                                
                                                
                                               
                                            }
                                            else
                                            {
                                                 $response= json_encode(array("check"=>false,"message"=>"Could not create user at this time."));
                                            }
                                    }
                                    else
                                    {
                                         $response= json_encode(array("check"=>false,"message"=>"Invalid gender format must be male or female."));
                                    }

                                }
                                else 
                                {
                                    $response= json_encode(array("check"=>false,"message"=>"Date of birth must be of format 'Y-m-d'."));
                                }
                        }
                        else
                        {
                            $response= json_encode(array("check"=>false,"message"=>"Document type can only be 1- ID, 2 -passport"));
                        }
               
        }
        else
        {
            $response= json_encode(array("check"=>false,"message"=>"The email has already been registered before."));
        }
        
        
   
}
else
{
    $response= json_encode(array("check"=>false,"message"=>"Please fill all the required fields."));
    
}

echo $response;
 
/*
  json_encode(array(
     "1"=>array( "branch_id"=>"1","branch_name"=>"branch name 1","country"=>"Kenya","state_county_province"=>"state_county_province 1","location"=>"place 1","directions"=>"directions 1","co-ordinates"=>array(),"phone_contacts"=>array("0716214868","0716214868"),"email_contacts"=>array("info@clicksoft.co.ke","info@clicksoft.co.ke")),
     "2"=>array( "branch_id"=>"2","country"=>"Kenya","state_county_province"=>"state_county_province 2","location"=>"place 2","directions"=>"directions 2","co-ordinates"=>array(),"phone_contacts"=>array("0716214868","0716214868"),"email_contacts"=>array("info@clicksoft.co.ke","info@clicksoft.co.ke")),
        
 ));
  */
