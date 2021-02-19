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

$request=get_my_post_get_variables(array('email_address','password'));

//die(json_encode($request));
$email_address=$request['email_address'];
$password=$request['password'];



if(isset($email_address) &&  isset($password) && !empty($email_address) &&  !empty($password) )
{
    
     //check
    $check=CheckIfExistsTwoColumnsFunction(users_table,'email_address','password',$email_address,md5($password));
    
    if($check==false)//exists
    {
        //get the items in the table and set the session
        $user_info=SelectTableOnTwoConditions(users_table,'email_address',$email_address,'password',md5($password))[0];
        
        //echo json_encode($user_info);
        
       
            $session_key=make_session_key($user_info);
            
           
       
        
        
        
        
       if($user_info['active']==1)
       {
           //$admin_info_json= stripslashes($admin_info_json);
            //$response= json_encode(array("check"=>true,"session_id"=>$session_id,"message"=> "Success." ,"administrator_level"=>(int)$admin_info[0]['administrator_level'], "Cookie"=>"PHPSESSID=".session_id(),'Cookie_as_parameter'=> base64_encode( encrypt_me_now($session_id, $_SESSION[$session_id]) )  ) );
        $response= json_encode(array("check"=>true,"token"=>$session_key,"token_type"=>"Bearer","message"=> "Success." ,"type"=>(int)$user_info['type'] ) );
        
       }
       else
       {
            $response= json_encode(array("check"=>false,"token"=>false,"token_type"=>"false","message"=>"User has been deactivated.","user_level"=>null ));
       }
        
      
    }
    else
    {
        $response= json_encode(array("check"=>false,"token"=>false,"token_type"=>"false","message"=>"Wrong username/email address and password combination.","user_level"=>null ));
    }
}
else
{
    $response= json_encode(array("check"=>false,"token"=>false,"token_type"=>"false","message"=>"Please provide email address as username and a password.","user_level"=>null));
    
}

 echo $response;