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

$request=get_my_post_get_variables(array('user_id','bank_id','amount','pin_id','mode','comments','authorization'));

//die(json_encode($request));
$user_id=$request['user_id'];
$bank_id=$request['bank_id'];
$amount=$request['amount'];
$pin_id=$request['pin_id'];
$mode=$request['mode'];
$comments=$request['comments'];
$authorization=$request['authorization'];



if(isset($user_id) &&  isset($bank_id) && !empty($amount) &&  !empty($pin_id)  &&  !empty($mode) &&  !empty($comments) &&  !empty($authorization) )
{
    
         //check
            $check_user_id=CheckIfExistsTwoColumnsFunction(user_accounts,'user_id','user_id',$user_id,$user_id);

            if($check_user_id==false)//exists
            {
            //    echo $bank_id;

                    $check_bank=CheckIfExistsTwoColumnsFunction(bank,'buying_selling','_id','buying',$bank_id);

                    if($check_bank==false)//exists
                    {
                        
                        if(is_numeric($amount) && $amount>0)
                        {
                            
                            if($amount<=max_transact)
                            {
                               //echo $tons_in_bank.">".$how_many_tons_you_buying;
                                      $check_pin=CheckIfExistsTwoColumnsFunction(users_transactions_temp,'pin_id','pin_id',$pin_id,$pin_id);
                                      
                                     if($check_pin==true)//does not exist
                                     {
                                        $insert= InsertIntoTransactionsTempTable(users_transactions_temp, $user_id, $bank_id, $amount, $pin_id, $mode, $comments, $authorization, storable_datetime_function(time()));
                                        
                                        if($insert==true)
                                        {
                                             $response= json_encode(array("check"=>true,"message"=>"Success." ));
                                        }
                                        else
                                        {
                                            $response= json_encode(array("check"=>false,"message"=>"Could not store temporary transaction at this time." ));
                                        }
                                     }
                                     else
                                     {
                                         $response= json_encode(array("check"=>false,"message"=>"Invalid transaction pin." ));
                                     }
                            }
                            else
                            {
                                 $response= json_encode(array("check"=>false,"message"=>"You can only transact ". number_format(max_transact,2)." at any one time." ));
                            }
                             
                        }
                        else
                        {
                            $response= json_encode(array("check"=>false,"message"=>"Invalid amount." ));
                        }
                       
                        
                        

                    }
                    else
                    {
                        $response= json_encode(array("check"=>false,"message"=>"Invalid Bank ID." ));
                    }   
            
            
            }
            else
            {
                $response= json_encode(array("check"=>false,"message"=>"Invalid user ID." ));
            }
   
    
    
}
else
{
    $response= json_encode(array("check"=>false,"message"=>"Please fill all the required fields."));
    
}

 echo $response;