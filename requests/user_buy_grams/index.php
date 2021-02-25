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

                    $check_bank=CheckIfExistsTwoColumnsFunction(bank,'buying_selling','_id','buying',$bank_id);

                    if($check_bank==false)//exists
                    {
                        
                        if(is_numeric($amount) && $amount>0)
                        {
                            
                            if($amount<=max_transact)
                            {
                                //get rate of tons
                                $rate_info=get_rates_to_exchange_ton();
                                $one_ton_costs=$rate_info['price_kes_actual'];


                                 $how_many_tons_you_buying=$amount/$one_ton_costs;
                                 
                                 $bank_info= SelectTableOnTwoConditions(bank, '_id', $bank_id, '_id', $bank_id)[0];
                                 $max_votes=$bank_info['max_votes']-1;
                                 $balance_info=get_account_balance($bank_info['raw_id']);
                                 $tons_in_bank=$balance_info['balance'];
                                 
                                 if($tons_in_bank>$how_many_tons_you_buying)
                                 {
                                     //echo $tons_in_bank.">".$how_many_tons_you_buying;
                                      $check_pin=CheckIfExistsTwoColumnsFunction(users_transactions,'pin_id','pin_id',$pin_id,$pin_id);
                                      
                                     if($check_pin==true)//does not exist
                                     {
                                       //  SelectTableOnFourConditions($TableName, $ConditionColumn1, $ConditionValue1, $ConditionColumn2, $ConditionValue2, $ConditionColumn3, $ConditionValue3, $ConditionColumn4, $ConditionValue4)
                                         $all_sponsors=SelectTableOnTwoConditions(bank_sponsors, 'bank_id', $bank_id, 'bank_id', $bank_id, 'bank_id', $bank_id, 'bank_id', $bank_id);
                                         
                                        $member_info= SelectTableOnTwoConditions(user_accounts, 'user_id', $user_id, 'user_id', $user_id)[0];
                                        $member_raw_address= $member_info['wallet_code'];
                                        
                                        $tvc=absolute_path."/downloads/ton_contracts/SafeMultisigWallet.tvc";
                                        $abi=absolute_path."/downloads/ton_contracts/SafeMultisigWallet.abi.json";
                                           
                                        $champion_id= $all_sponsors[rand(0, (count($all_sponsors)-1))]['user_id'];
                                        //die($champion_id.'==');
                                        $custodian_key_file=absolute_path."/uploads/".md5($champion_id).".json";
                                        
                                        
                                        $transaction_id=do_multisig_transfare($bank_info['raw_id'],$member_raw_address,$how_many_tons_you_buying,$abi,$custodian_key_file);
                                      //  echo $transaction_id;
                                        if($transaction_id!=null)
                                        {
                                            //confirm
                                            $done=false;
                                            
                                            //ensure it happes
                                            while ($done==false) 
                                            {
                                            
                                                   // $not_yet_voted=false;
                                                    $multisig_address=$bank_info['raw_id'];

                                                    $done_votes=0;
                                                    for ($index = 0; $index < count($all_sponsors); $index++) 
                                                    {
                                                        if($done_votes < $max_votes && $all_sponsors[$index]['user_id'] != $champion_id)
                                                        {
                                                                $custodian_key_file_sub=absolute_path."/uploads/".md5($all_sponsors[$index]['user_id']).".json";
                                                                $done=confirm_transaction_multisig($multisig_address,$transaction_id,$abi,$custodian_key_file_sub);

                                                                
                                                                $done_votes++;
                                                        }

                                                    }
                                            }
                                            
                                            
                                            if($done==true)
                                            {
                                                    //insert
                                                    $insert= InsertIntoTransactionsTable(users_transactions, $user_id, $bank_id, 'buying', $amount, $one_ton_costs, $pin_id, $mode, $transaction_id, $comments, storable_datetime_function(time()));

                                                    if($insert==true)
                                                    {
                                                        $response= json_encode(array("check"=>true,
                                                            "message"=>"Success.",
                                                            "Tokens"=> (double)number_format($how_many_tons_you_buying,2),
                                                            "NanoTokens"=> (int)($how_many_tons_you_buying*1000000000),
                                                            "Worth"=>(double)number_format($amount,2) ));
                                                    }
                                                    else
                                                    {
                                                        $response= json_encode(array("check"=>false,"message"=>"Could not transfare tons at this time." ));
                                                    }
                                            }
                                            else
                                            {
                                                 $response= json_encode(array("check"=>false,"message"=>"Timeout error, could not confirm funds at this time." ));
                                            }
                                            
                                           
                                            
                                        }
                                        else
                                        {
                                             $response= json_encode(array("check"=>false,"message"=>"Something went wrong could not execute transaction at this time." ));
                                        }
                                        // $response= json_encode(array("check"=>true,"members_no_sponsors"=>$members_no_sponsors,"members_yes_sponsors"=>$members_yes_sponsors ));
                                     }
                                     else
                                     {
                                         $response= json_encode(array("check"=>false,"message"=>"Invalid transaction pin." ));
                                     }
                                 }
                                 else
                                 {
                                     $response= json_encode(array("check"=>false,"message"=>"You are trying to buy ".number_format($how_many_tons_you_buying,2)." while the bank only has ".number_format($tons_in_bank,2)." GRAMS." ));
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