<?php
require '../../classes/constants.php';
require '../../classes/functions.php';
require '../../classes/database.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//check get or post

$request=get_my_post_get_variables(array('old_password','new_password','confirm_password'));





$old_password=$request['old_password'];
$new_password=$request['new_password'];
$confirm_password=$request['confirm_password'];




if(
   isset($old_password) && !empty($old_password) &&     
    isset($new_password) && !empty($new_password) &&
    isset($confirm_password) && !empty($confirm_password) 
        )
{
     //check if session is set
    if(  verify_session_key())
    {
       
        //die("lol");
        $user_info= get_session_info();
        
        $old_password_is=$user_info['password'];
        
        
        if(md5($old_password)== strtolower($old_password_is) )//check old password matches
        {
            if($new_password==$confirm_password)//check the two new passwords match
            {
                if(is_numeric($new_password) && strlen($new_password)==4 )
                {
                    //update
                    $did_it_update_password=UpdateTableOneCondition(users_table, 'password', md5($new_password), '_id', $user_info['_id']);
                    $did_it_update_session=UpdateTableOneCondition(users_table, 'session_key', null, '_id', $user_info['_id']);
                    
                    if($did_it_update_password==true && $did_it_update_session==true)
                    {
                        
                       
                         $response= json_encode(array("check"=>true,"message"=>"Success. please log back in with your new password."));
                          
                    }
                    else
                    {
                         $response= json_encode(array("check"=>false,"message"=>"Could not update password at this time."));
                    }
                    
                }
                else
                {
                     $response= json_encode(array("check"=>false,"message"=>"Your password must be 4 number long"));
                }
            }
            else
            {
                $response= json_encode(array("check"=>false,"message"=>"Your two new passwords do not match."));
            }
        }
        else
        {
            $response= json_encode(array("check"=>false,"message"=>"Your current password does not match with what you have provided."));
        }
        
        
    }
    else 
    {
        $response= json_encode(array("check"=>false,"message"=>"Please provide a valid token."));
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
