<?php
require '../../classes/constants.php';
require '../../classes/functions.php';
require '../../classes/database.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$request=get_my_post_get_variables(array('buying_selling'));


$buying_selling=$request['buying_selling'];


if(
   isset($buying_selling) && !empty($buying_selling) 
        )
{
    
    
    if($buying_selling=="buying" || $buying_selling=="selling")
    {
            //check if session is set
            if(  verify_session_key())
            {
                

                $accounts= SelectTableOnTwoConditions(bank, 'buying_selling', $buying_selling, 'buying_selling', $buying_selling);

                $array=array();
                
                foreach ($accounts as $value) 
                {
                     $balance_info=get_account_balance($value['raw_id']);
                   
                
                    $array[count($array)]=array("_id"=> $value["_id"],
                        "name"=> $value["name"],
                        "raw_id"=> $value["raw_id"],
                        "wallet_balance"=> $balance_info,
                        "time_stamp"=> UTCTimeToLocalTime($value['time_stamp'], '', 'Y-m-d H:i:s', 'd-m-Y H:i:s')
                        );
                    
                }
                //die(json_encode($accounts));

               
                
                
                $response= json_encode(array("check"=>true,"message"=>$array));





            }
            else 
            {
                $response= json_encode(array("check"=>false,"message"=>"Please provide a valid token."));
            }
    }
    else
    {
        $response= json_encode(array("check"=>false,"message"=>"Can only be 'buying' or 'selling'."));
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
