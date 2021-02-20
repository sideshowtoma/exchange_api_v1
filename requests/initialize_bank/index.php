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

$data=json_decode(file_get_contents('php://input'), true);

$name=$data['name'];
$buying_selling=$data['buying_selling'];
$members=$data['members'];
$sponsor_member_id=$data['sponsor_member_id'];
$sponsor_amount=$data['sponsor_amount'];
 $max_votes=$data['max_votes'];



if(
   isset($name) && !empty($name) &&     
    isset($buying_selling) && !empty($buying_selling) &&
    isset($members) && !empty($members)  &&
    isset($sponsor_member_id) && !empty($sponsor_member_id)  &&
    isset($sponsor_amount) && !empty($sponsor_amount)  &&
    isset($max_votes) && !empty($max_votes) 
        )
{
     //check if session is set
    if(  verify_session_key())
    {
        $check_name= CheckIfExistsTwoColumnsFunction(bank, 'name', 'name', $name, $name);
        
        if($check_name==true)
        {
            if($max_votes<count($members))
            {
                $get_sponsor_member=SelectTableOnTwoConditions(user_accounts, 'user_id', $sponsor_member_id, 'user_id', $sponsor_member_id)[0];
                 
                $sponsor_account_data= get_account_balance($get_sponsor_member['wallet_code']);
                
              //  die(json_encode($sponsor_account_data));
                
                if($sponsor_account_data['balance']>$sponsor_amount)
                {
                    $abi_wallet=absolute_path."/downloads/ton_contracts/Wallet.abi.json";
                    $tvc_wallet=absolute_path."/downloads/ton_contracts/Wallet.tvc";
                     $from_key_file=absolute_path."/uploads/".md5($sponsor_member_id).".json";
                 
                    $did_it_deploy=deploy_wallet_contract($abi_wallet,$from_key_file,$tvc_wallet);
                    
                    if($did_it_deploy==true)
                    {
                            if($buying_selling=="buying" || $buying_selling=="selling" )
                            {
                                if(count($members)>0)
                                {
                                    $all_members_exist=true;

                                    foreach ($members as $member_value) 
                                    {
                                        $check_member= CheckIfExistsTwoColumnsFunction(user_accounts, 'user_id', 'user_id', $member_value, $member_value);

                                        if($check_member==true)//missing
                                        {
                                              $all_members_exist=false;
                                        }
                                    }

                                    if($all_members_exist==true)
                                    {
                                        //create multi sig
                                        //get seed phrase
                                        $seed_phrase=get_seed_phrase();

                                        if($seed_phrase!=null)
                                        {
                                            $key_pair_name=absolute_path."/uploads/bank_keys/".md5($name).".json";

                                            $make_key_pair=get_a_key_pair($key_pair_name,$seed_phrase);

                                            if($make_key_pair==true)
                                            {
                                                //make address
                                                 $tvc=absolute_path."/downloads/ton_contracts/SafeMultisigWallet.tvc";
                                                  $abi=absolute_path."/downloads/ton_contracts/SafeMultisigWallet.abi.json";



                                                $raw_multi_sig_address=make_address_multi_sig($key_pair_name,$tvc,$abi);

                                                if($raw_multi_sig_address!=null)
                                                {
                                                    //die ($raw_multi_sig_address);

                                                      //sponsor the address first
                                                    $sent_sponsor=send_some_tokens_multisig($get_sponsor_member['wallet_code'],$raw_multi_sig_address,$from_key_file,$abi_wallet,$sponsor_amount*1000000000);

                                                    if($sent_sponsor==true)
                                                    {
                                                                //get members public keys
                                                                $pub_keys_use=array();
                                                                foreach ($members as $member_value) 
                                                                {
                                                                    $file_to_read=absolute_path."/uploads/".md5($member_value).".json";

                                                                    if(file_exists($file_to_read))
                                                                    {
                                                                        $myfile = fopen($file_to_read,"r") or "";
                                                                        $data_read_is= json_decode(fread($myfile,filesize($file_to_read)),true);
                                                                        fclose($myfile);

                                                                       // echo json_encode($data_read_is);
                                                                        $pub_keys_use[count($pub_keys_use)]="0x".$data_read_is['public'];
                                                                    }
                                                                }

                                                              //  echo json_encode($pub_keys_use);

                                                                //deploy
                                                                $deploy_is=deploy_my_multisig($tvc,$pub_keys_use,$max_votes,$abi,$key_pair_name);
                                                                
                                                                if($deploy_is==true)
                                                                {
                                                                    $time_stamp= storable_datetime_function(time());
                                                                    //insert into bank
                                                                    $insert=InsertIntoBankTable(bank, $name, $buying_selling, $raw_multi_sig_address, base64_encode($seed_phrase), $max_votes, $time_stamp);
                                                                    if($insert==true)
                                                                    {
                                                                        $data= SelectTableOnTwoConditions(bank, 'name', $name, 'name', $name)[0];
                                                                        $bank_id=$data['_id'];
                                                                        
                                                                        foreach ($members as $member_value) 
                                                                        {
                                                                            if($member_value==$sponsor_member_id)//sponsor
                                                                            {
                                                                                InsertIntoBankSponsorsAmountTable(bank_sponsors,$bank_id,$member_value,'yes',$sponsor_amount,$time_stamp);
                                                                            }
                                                                            else
                                                                            {
                                                                                 InsertIntoBankSponsorsNullTable(bank_sponsors,$bank_id,$member_value,'no',$time_stamp);
                                                                         
                                                                            }
                                                                        }
                                                                
                                                                       $response= json_encode(array("check"=>true,"message"=>"Success.","_id"=>$bank_id));  
                                                                    }
                                                                    else
                                                                    {
                                                                       $response= json_encode(array("check"=>false,"message"=>"Could not register bank at this time")); 
                                                                    }
                                                                    
                                                                }
                                                                else
                                                                {
                                                                     $response= json_encode(array("check"=>false,"message"=>"Could not deploy bank at this time"));
                                                                }

                                                    }
                                                    else 
                                                    {
                                                         $response= json_encode(array("check"=>false,"message"=>"Could not sponsor bank at this time"));
                                                    }
                                                   

                                                }
                                                else
                                                {
                                                     $response= json_encode(array("check"=>false,"message"=>"Could not make address at this time"));
                                                }
                                                //echo $raw_multi_sig_address;
                                            }
                                            else
                                            {
                                                 $response= json_encode(array("check"=>false,"message"=>"Could not make key pair at this time"));
                                            }

                                        }
                                        else
                                        {
                                             $response= json_encode(array("check"=>false,"message"=>"Could not make seed phrase at this time"));
                                        }


                                    }
                                    else
                                    {
                                         $response= json_encode(array("check"=>false,"message"=>"Invalid members"));
                                    }
                                }
                                else
                                {
                                     $response= json_encode(array("check"=>false,"message"=>"You must include members to form the bank"));
                                }
                            }
                            else
                            {
                                 $response= json_encode(array("check"=>false,"message"=>"Bank can only be 'buying' or 'selling'."));
                            }
                    }
                    else
                    {
                        $response= json_encode(array("check"=>false,"message"=>"Unable to deploy contract, ensure sponsor has tons."));
                    }
                    
                }
                else
                {
                     $response= json_encode(array("check"=>false,"message"=>"Sponsor has (".number_format($sponsor_account_data['balance'],2).") inadequate funds (". number_format($sponsor_amount,2).")."));
                }
            }
            else
            {
                $response= json_encode(array("check"=>false,"message"=>"You can only have a minimum of ". count($members)." votes."));
            }
        }
        else
        {
             $response= json_encode(array("check"=>false,"message"=>"Invalid bank name, name already in use."));
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
 
/*
  json_encode(array(
     "1"=>array( "branch_id"=>"1","branch_name"=>"branch name 1","country"=>"Kenya","state_county_province"=>"state_county_province 1","location"=>"place 1","directions"=>"directions 1","co-ordinates"=>array(),"phone_contacts"=>array("0716214868","0716214868"),"email_contacts"=>array("info@clicksoft.co.ke","info@clicksoft.co.ke")),
     "2"=>array( "branch_id"=>"2","country"=>"Kenya","state_county_province"=>"state_county_province 2","location"=>"place 2","directions"=>"directions 2","co-ordinates"=>array(),"phone_contacts"=>array("0716214868","0716214868"),"email_contacts"=>array("info@clicksoft.co.ke","info@clicksoft.co.ke")),
        
 ));
  */
