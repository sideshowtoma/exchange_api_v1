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

$request=get_my_post_get_variables(array('user_id','bank_id','amount','pin_id','mode','narrative','comments'));

//die(json_encode($request));
$user_id=$request['user_id'];
$bank_id=$request['bank_id'];
$amount=$request['amount'];
$pin_id=$request['pin_id'];
$mode=$request['mode'];
$comments=$request['comments'];



if(isset($user_id) &&  isset($bank_id) && !empty($amount) &&  !empty($pin_id)  &&  !empty($mode) &&  !empty($comments) )
{
    if(  verify_session_key())
    {
         //check
            $check_user_id=CheckIfExistsTwoColumnsFunction(user_accounts,'user_id','user_id',$user_id,$user_id);

            if($check_user_id==false)//exists
            {
            //    echo $bank_id;

                    $check_bank=CheckIfExistsTwoColumnsFunction(bank,'buying_selling','_id','selling',$bank_id);

                    if($check_bank==false)//exists
                    {
                        
                        if(is_numeric($amount) && $amount>0)
                        {
                            
                            if($amount<=max_transact)
                            {
                                //get rate of tons
                                $rate_info=get_rates_to_exchange_ton();
                                $one_ton_costs=$rate_info['price_kes_actual'];


                                $how_many_tons_you_selling=$amount/$one_ton_costs;
                                
                                $member_info= SelectTableOnTwoConditions(user_accounts, 'user_id', $user_id, 'user_id', $user_id)[0];
                                $member_raw_address= $member_info['wallet_code'];
                                        
                                 
                                 $balance_info=get_account_balance($member_raw_address);
                                 $tons_in_account=$balance_info['balance'];
                                 
                                 if($tons_in_account>$how_many_tons_you_selling)
                                 {
                                      $check_pin=CheckIfExistsTwoColumnsFunction(users_transactions,'pin_id','pin_id',$pin_id,$pin_id);
                                      
                                     if($check_pin==true)//does not exist
                                     {
                                          $bank_info= SelectTableOnTwoConditions(bank, '_id', $bank_id, '_id', $bank_id)[0];
                                          
                                            $abi_wallet=absolute_path."/downloads/ton_contracts/Wallet.abi.json";
                                            $tvc_wallet=absolute_path."/downloads/ton_contracts/Wallet.tvc";
                                            $from_key_file=absolute_path."/uploads/".md5($user_id).".json";
                     
                                            $did_it_deploy=deploy_wallet_contract($abi_wallet,$from_key_file,$tvc_wallet);
                                               
                                            $sent_tons=send_some_tokens_multisig($member_raw_address,$bank_info['raw_id'],$from_key_file,$abi_wallet,(int)($how_many_tons_you_selling*1000000000));
                                         
                                            if($sent_tons==true && $did_it_deploy==true)
                                            {
                                                    $insert= InsertIntoTransactionsTable(users_transactions, $user_id, $bank_id, 'selling', $amount, $one_ton_costs, $pin_id, $mode, md5($pin_id), $comments, storable_datetime_function(time()));

                                                    if($insert==true)
                                                    {
                                                        $response= json_encode(array("check"=>true,
                                                            "message"=>"Success.",
                                                            "Tokens"=> (double)number_format($how_many_tons_you_selling,2),
                                                            "NanoTokens"=> (int)($how_many_tons_you_selling*1000000000),
                                                            "Worth"=>(double)number_format($amount,2) ));
                                                    }
                                                    else
                                                    {
                                                        $response= json_encode(array("check"=>false,"message"=>"Could not transfare tons at this time." ));
                                                    }
                                            }
                                            else
                                            {
                                                     $response= json_encode(array("check"=>false,"message"=>"Cannot buy grams at this time." ));
                                            }
                                     }
                                     else
                                     {
                                          $response= json_encode(array("check"=>false,"message"=>"Invalid transaction pin." ));
                                     }
                                 }
                                 else
                                 {
                                     $response= json_encode(array("check"=>false,"message"=>"You are trying to sell ".number_format($how_many_tons_you_selling,2)." while you only have ".number_format($tons_in_account,2)." GRAMS." ));
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
         $response= json_encode(array("check"=>false,"message"=>"Invalid authorization."));
    }
    
    
}
else
{
    $response= json_encode(array("check"=>false,"message"=>"Please fill all the required fields."));
    
}

 echo $response;