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
                                
                               $member_info_actual= SelectTableOnTwoConditions(users_table, '_id', $user_id, '_id', $user_id)[0];
                                $telephone_number= $member_info_actual['telephone_number'];       
                                 
                               // die($telephone_number.'===');
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
                                                        
                                                        /* Urls */
                                                        $access_token_url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
                                                        $b2c_url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';


                                                           /* Required Variables */
                                                            $consumerKey = 'TjeOcFd7AuW5aXTW3IjIGqiE0RgFD5C3';        
                                                            $consumerSecret = 'QIjhJkGI7NC3mbfR';     
                                                            $headers = ['Content-Type:application/json; charset=utf8'];

                                                            /* from the test credentials provided on you developers account */
                                                            $InitiatorName = 'PETER MWONGELA';      
                                                            $SecurityCredential = 'DI8aXqXmqwFLLNtMxb+B0h5b/Tks8FZ3Tvfwcf6V6ohzOZ9n4n40a7TgUFW5UR//nnUNrQkTjnNpfbyY23d9lv5d5yCKrnU4DjDW8U7sZ4ugOBSHQyZ6yLsZFwoYRMQ3WRbhgoKxDR4heeYSoItK83Rbl1Pq1pcGL7qGfk3drko3VFRyLpKSPpLFCMmwEB62tCW8g0+K4F/3B96jzYfwto93S338GAvrWGXUb76+TfyDip7nhPgbSnnZWilkMBS3MWRT46nMtPP86I+wPAGQCW/0Mb19OWX6JBRKXrWvbbmoiYciS4j7U0Y8tiklD6UYWKOvpx/8ejcLoPP9TYJxKw=='; 
                                                            $CommandID = 'BusinessPayment';           
                                                            $Amount = $amount;//'1';
                                                            $PartyA = '433354';             
                                                            $PartyB = $telephone_number;//'254710549195';             
                                                            $Remarks = 'Salary';      
                                                            $QueueTimeOutURL = 'https://www.denkim.co.ke/biz/B2CResultURL.php';    
                                                            $ResultURL = 'https://www.denkim.co.ke/biz/B2CResultURL.php';          
                                                            $Occasion =$pin_id;//'test payment b2c'; //'test payment b2c';           

                                                            /* Obtain Access Token */
                                                            $curl = curl_init($access_token_url);
                                                            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                                                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                                                            curl_setopt($curl, CURLOPT_HEADER, FALSE);
                                                            curl_setopt($curl, CURLOPT_USERPWD, $consumerKey.':'.$consumerSecret);
                                                            $result = curl_exec($curl);
                                                            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                                                            $result = json_decode($result);
                                                            $access_token = $result->access_token;
                                                            curl_close($curl);

                                                            /* Main B2C Request to the API */
                                                            $b2cHeader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];
                                                            $curl = curl_init();
                                                            curl_setopt($curl, CURLOPT_URL, $b2c_url);
                                                            curl_setopt($curl, CURLOPT_HTTPHEADER, $b2cHeader); //setting custom header

                                                            $curl_post_data = array(
                                                              //Fill in the request parameters with valid values
                                                              'InitiatorName' => $InitiatorName,
                                                              'SecurityCredential' => $SecurityCredential,
                                                              'CommandID' => $CommandID,
                                                              'Amount' => $Amount,
                                                              'PartyA' => $PartyA,
                                                              'PartyB' => $PartyB,
                                                              'Remarks' => $Remarks,
                                                              'QueueTimeOutURL' => $QueueTimeOutURL,
                                                              'ResultURL' => $ResultURL,
                                                              'Occasion' => $Occasion
                                                            );

                                                            $data_string = json_encode($curl_post_data);
                                                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                                            curl_setopt($curl, CURLOPT_POST, true);
                                                            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                                                            $curl_response = curl_exec($curl);
                                                            //print_r($curl_response);
                                                            //echo $curl_response;
                                                            $curl_response= json_decode($curl_response,true);
                                        
                                                        $response= json_encode(array("check"=>true,
                                                            "message"=>"Success.",
                                                            "Tokens"=> (double)number_format($how_many_tons_you_selling,2),
                                                            "NanoTokens"=> (int)($how_many_tons_you_selling*1000000000),
                                                            "Worth"=>(double)number_format($amount,2),
                                                            "SendMoneyRequest"=>$curl_response,
                                                            
                                                            ));
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