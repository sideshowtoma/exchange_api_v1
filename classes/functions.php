<?php
header('Content-Type: application/json');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function make_session_key($user_info)
{
    if($user_info['session_key']!=null)
    {
        return $user_info['session_key'];
    }
    else
    {
          $key= sha1($user_info['_id'].'--'.$user_info['email_address'].$user_info['type'].$user_info['id_or_passport'].$user_info['id_passport_number'].$user_info['time_stamp'].$user_info['password']);
          $key= base64_encode($key.md5($key).sha1($key));
  
          UpdateTableOneCondition(users_table, 'session_key', $key, '_id', $user_info['_id']);
          
          return $key;
          
    }
    
    
}

function verify_session_key()
{
    
    $key_bearer= explode('Bearer ',getallheaders()['Authorization']) ;
    $key=$key_bearer[1];
    //echo $key;
   

    $data= SelectTableOnTwoConditions(users_table, 'session_key', $key, 'session_key', $key)[0];
    //echo json_encode($data);
    if(count($data)>0)
    {
        return true;
    }
    else
    {
        return false;
    }
    
}

function get_session_info()
{
    
    $key_bearer= explode('Bearer ',getallheaders()['Authorization']) ;
    $key=$key_bearer[1];
    //echo $key;
   

    return SelectTableOnTwoConditions(users_table, 'session_key', $key, 'session_key', $key)[0];
    
    
}



function get_my_post_get_variables($look_for_array)
{
    $return= array();
    
    
    foreach ($look_for_array as $value) 
    {
       // die($value);
        $return[$value]=trim($_GET[$value]);
        
        if(!isset($_GET[$value]))
        {
             $return[$value]=trim($_POST[$value]);
        }
        
    }
    
    
    return $return;
}



function storable_datetime_function($time)
{
            date_default_timezone_set(time_zone_format);//make time kenyan
            $my_day= date('Y-m-d H:i:s',$time);
            
            return $my_day;
            
}


function usable_datetime_function($time)
{
            date_default_timezone_set(time_zone_format);//make time kenyan
            $my_day= date('d-m-Y H:i:s',$time);
            
            return $my_day;
            
}

function storable_datetime_function_no_time_zone($time)
{
         //   date_default_timezone_set(time_zone_format);//make time kenyan
            $my_day= date('Y-m-d H:i:s',$time);
            
            return $my_day;
            
}


function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}


function UTCTimeToLocalTime($time, $tz = '', $FromDateFormat = 'Y-m-d H:i:s', $ToDateFormat = 'H:i:s d-m-Y')
{
if ($tz == '')
    $tz = date_default_timezone_get();

$utc_datetime = DateTime::createFromFormat($FromDateFormat, $time, new
    DateTimeZone('UTC'));
$local_datetime = $utc_datetime;

$local_datetime->setTimeZone(new DateTimeZone($tz));
return $local_datetime->format($ToDateFormat);
}




function run_ssh_command($command)
{
    $connection = ssh2_connect(ssh_machine, ssh_port);
    
        if(ssh2_auth_password($connection,ssh_user_name, ssh_password))
        {
            // echo " Authentication Successful\n";
        }
        else
        {
            die('Authentication Failed');
        }

$stream = ssh2_exec($connection,$command);
$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

// Enable blocking for both streams
stream_set_blocking($errorStream, true);
stream_set_blocking($stream, true);

// Whichever of the two below commands is listed first will receive its appropriate output.  The second command receives nothing
//echo "Output: " . stream_get_contents($stream);
//echo "Error: " . stream_get_contents($errorStream);

$retturn_array=array("Output"=>stream_get_contents($stream),"Error"=>stream_get_contents($errorStream));

// Close the streams       
fclose($errorStream);
fclose($stream);
ssh2_disconnect(); // Causes the connection to be reset

return $retturn_array;
}

//echo function_exists ( 'ssh2_connect' ) ; 

function ensure_ton_url()
{
    
    $check_result=run_ssh_command(ton_cli_command." config --list");
    if(isset($check_result["Output"]))
    {
        //echo $check_result["Output"];
        $decoded_feedback=use_range_to_give_array($check_result["Output"],1,12);
        
        if($decoded_feedback["url"]==ton_url)
        {
            return true;
        }
        elseif($decoded_feedback["url"]!=ton_url) 
        {
           // echo "lets do this";
             $check_result_2=run_ssh_command(ton_cli_command." config --url ".ton_url);  
             
             //echo $check_result_2["Output"];
             if(isset($check_result_2["Output"]))
             {
                 $change_result=read_specific_line($check_result_2["Output"],1);
                 
                 if($change_result=="Succeeded.")
                 {
                      return true;
                 }
                 else
                 {
                      return false;
                 }
                 
             }
             else
             {
                 return false;
             }
            
        }
        else
        {
            return false;
        }
        
       // echo json_encode($decoded_feedback);
    }
    else
    {
        return false;
    }
}

function use_range_to_give_array($string,$start,$end)
{
    $counter=0;
    $json="";
        foreach(preg_split("/((\r?\n)|(\r\n?))/",  $string) as $line)
        {
           if($counter>=$start && $counter<=$end )
           {
               $json.=$line;
           }
           
           $counter++;
        } 
        
        return json_decode($json,true);
        
        
}  


function read_specific_line($string,$number)
{
    $counter=0;
    $line_data="";
        foreach(preg_split("/((\r?\n)|(\r\n?))/",  $string) as $line)
        {
           if($number==$counter )
           {
               $line_data=$line;
           }
           
           $counter++;
        } 
        
        return $line_data;
}



function see_if_target_string_exists($string,$target_string_line)
{
    $exists=false;
    
        foreach(preg_split("/((\r?\n)|(\r\n?))/",  $string) as $line)
        {
           if(trim(strtolower($line)) == trim(strtolower($target_string_line)) )
           {
               $exists=true;
           }
        } 
        
        return $exists;
}

function make_wallet_for_me($user_id)
{
    $array_return=array();
     $compile_result=run_ssh_command(compiler_command." ".absolute_path."/downloads/ton_contracts/Wallet.sol");  
     
     
                 if(isset($compile_result["Output"]))
                 {
                     
                      if(file_exists(ssh_home_path."Wallet.code") && file_exists(ssh_home_path."Wallet.abi.json")  )
                      {
                          //echo "okay";
                          //assambe
                           $assamble_result=run_ssh_command(tvm_linker_command." compile ".ssh_home_path."Wallet.code --lib ".tvm_linker_path);  
                           
                           if(isset($assamble_result["Output"]))
                           {
                              // echo $assamble_result["Output"];
                               //get tvc
                               $tvc_file_name=read_specific_line($assamble_result["Output"],6);
                               $tvc_file_name= explode("Saved contract to file ", $tvc_file_name)[1];
                               
                               
                                if(file_exists(ssh_home_path.$tvc_file_name)   )
                                {
                                    //echo $tvc_file_name;
                                    //deploy contract
                                    $deploy_result=run_ssh_command(ton_cli_command." genaddr ".ssh_home_path.$tvc_file_name." ".ssh_home_path."Wallet.abi.json --genkey ".absolute_path."/uploads/".md5($user_id).".json");  
                          
                                    if(isset($deploy_result["Output"]))
                                    {
                                        //echo $deploy_result["Output"];
                                          $keys= explode("    keys: ",read_specific_line($deploy_result["Output"],4))[1];
                                          $seed_phrase=explode("Seed phrase: ",read_specific_line($deploy_result["Output"],8))[1];
                                          $raw_address=explode("Raw address: ",read_specific_line($deploy_result["Output"],10))[1];
                                          
                                          if(file_exists($keys) && !empty($seed_phrase)&& !empty($raw_address))
                                          {
                                              $seed_phrase= str_replace('"', "", $seed_phrase);
                                              
                                               $array_return["status"]=true;
                                               $array_return["data"]=array("keys"=>$keys,"seed_phrase"=>$seed_phrase,"raw_address"=>$raw_address);
                                          }
                                          else
                                          {
                                               $array_return["status"]=false;
                                          }
                                         // echo $keys.' '.$seed_phrase.' '.$raw_address;
                                          
                                    }
                                    else
                                    {
                                        $array_return["status"]=false;
                                    }
                                    
                                }
                                else
                                {
                                    $array_return["status"]=false;
                                }
                              
                           }
                           else
                           {
                                $array_return["status"]=false;
                           }
                      }
                      else
                      {
                          $array_return["status"]=false;
                      }
                 }
                 else
                 {
                      $array_return["status"]=false;
                 }
     
     return $array_return;
}



function make_user_invite_email_html($name,$account_type_title,$email_address,$password,$seed_phrase=null,$raw_address=null)
{
    
    
    
    return '<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>HelloDawa</title>
</head>'
    . '<body>'
            . '<div style="background-color:#396270; color:#FFFFFF; padding: 10px">'
            . '<h4>Hello <b>'.strtoupper($name).'</b> below are your Flamingo Finance account details and credentials.</h4>'
            . '<p>To login and access your account use the credntails below.</p>'
           . '<table style="background-color:#396270;color:#FFFFFF; padding: 10px">'
                . '<tr>'
                    . '<td><b>Account type:</b></td>'. '<td>'.$account_type_title.'</td>'
                . '</tr>'
                . '<tr>'
                    . '<td><b>Username/email:</b></td>'. '<td>'.$email_address.'</td>'
                . '</tr>'
                . '<tr>'
                    . '<td><b>Password/Pin:</b></td>'. '<td>'.$password.'</td>'
                . '</tr>'
                . '<tr>'
                    . '<td><b>Website:</b></td>'. '<td><a href="'.user_login_url.'">Visit</a></td>'
                . '</tr>'
                . '<tr>'
                    . '<td><b>Android app:</b></td>'. '<td><a href="'.android_app_url.'">Download</a></td>'
                . '</tr>'
                . '<tr>'
                    . '<td><b>Ios app:</b></td>'. '<td><a href="'.ios_app_url.'">Download</a></td>'
                . '</tr>'
                . '<tr>'
                    . '<td><b>Dev seed phrase:</b></td>'. '<td>'.$seed_phrase.'</td>'
                . '</tr>'
                . '<tr>'
                    . '<td><b>Dev raw address:</b></td>'. '<td>'.$raw_address.'</td>'
                . '</tr>'
            . '</table>'
            . '</div>'
            . '</br>'
    . '</body>'
    . '</html>';
    
}

function mail_sender_function($address,$name,$body,$alt_body,$subject,$attachment_1=null,$attachment_2=null,$attachment_3=null,$attachment_4=null)
{
        $mail             = new PHPMailer();

   
        $mail->IsSMTP(); 
        $mail->Host       = mail_host; 
        $mail->SMTPDebug  = 0;                  
        $mail->SMTPAuth   = true;                  
        $mail->Host       = mail_host;
        $mail->Port       = mail_port;                    
        $mail->Username   = mail_user_name; 
        $mail->Password   = mail_password;        

        $mail->SetFrom(mail_user_name, mail_sender);

        $mail->AddReplyTo(mail_user_name,mail_sender);

        $mail->Subject    = $subject;
        $mail->AltBody    = $alt_body; 

        $mail->MsgHTML($body);

        
        $mail->AddAddress($address, $name);

        if($attachment_1!==null)
        {
             $mail->AddAttachment($attachment_1);
        }
       
        if($attachment_2!==null)
        {
               $mail->AddAttachment($attachment_2);
        }
      
        if($attachment_3!==null)
        {
               $mail->AddAttachment($attachment_3);
        }
        
        if($attachment_4!==null)
        {
               $mail->AddAttachment($attachment_4);
        }

        if(!$mail->Send()) {
         // echo "Mailer Error: " . $mail->ErrorInfo;
            return false;
        } else {
          //echo "Message sent!";
            return true;
        }
}


function get_account_balance($raw_account_id)
{
    $data=array();
    if(ensure_ton_url())
    {
         $account_result=run_ssh_command(ton_cli_command." account ".$raw_account_id);  
               
        // die($account_result["Output"]);
         
                                    if(isset($account_result["Output"]))
                                    {
                                       // echo read_specific_line($account_result["Output"],8);
                                        //echo $deploy_result["Output"];
                                          $balance= explode("balance:",read_specific_line($account_result["Output"],7))[1];
                                          $last_paid=explode("last_paid:",read_specific_line($account_result["Output"],8))[1];
                                          $last_trans_lt=explode("last_trans_lt:",read_specific_line($account_result["Output"],9))[1];
                                          
                                          if(isset($balance) && isset($last_paid) && isset($last_trans_lt) )
                                          {
                                               $data['balance']=(double)trim($balance)/1000000000;
                                          $data['last_paid']= storable_datetime_function(trim($last_paid));
                                          $data['last_trans_lt']=trim($last_trans_lt);
                                         // $data['last_trans_real_time']=$last_trans_lt;
                                          }
                                          else
                                          {
                                              $data['balance']=0;
                                              $data['last_paid']=NULL;
                                              $data['last_trans_lt']=NULL;
                                                      
                                          }
                                         
                                          
                                         // echo $keys.' '.$seed_phrase.' '.$raw_address;
                                          
                                    }
    }
    
    return $data;
}


function deploy_wallet_contract($wallet_abi,$key_pair_path,$wallet_tvc)
{
    $done=false;
    if(ensure_ton_url())
    {
         $deploy=run_ssh_command(ton_cli_command." deploy --abi ".$wallet_abi." --sign ".$key_pair_path." ".$wallet_tvc." {} ");  
               
         //die($deploy["Output"]);
         
                                    if(isset($deploy["Output"]))
                                    {
                                       // echo read_specific_line($account_result["Output"],8);
                                        //echo $deploy_result["Output"];
                                          $status= read_specific_line($deploy["Output"],9);
                                            //die($status); 
                                          if($status=="Transaction succeeded.")
                                          {
                                          $done=true;
                                          }
                                          
                                    }
    }
    
    return $done;
}



function get_seed_phrase()
{
    $seed_phrase=null;
   // die("hahaha");
    if(ensure_ton_url())
    {
         $phrase_result=run_ssh_command(ton_cli_command." genphrase");  
               
         //die($phrase_result["Output"]);
         
                                    if(isset($phrase_result["Output"]))
                                    {
                                       
                                          $seed_phrase= explode("Seed phrase: ",read_specific_line($phrase_result["Output"],2))[1];
                                          
                                         $seed_phrase= str_replace('"', "", $seed_phrase);
                                          
                                    }
    }
    
    return $seed_phrase;
}


function get_a_key_pair($key_pair_name,$seed_phrase)
{
    $made=false;
    
   // die("hahaha");
    if(ensure_ton_url())
    {
       // echo ton_cli_command." getkeypair ".$key_pair_name.' "'.$seed_phrase.'"';
         $key_pair_result=run_ssh_command(ton_cli_command." getkeypair ".$key_pair_name.' "'.$seed_phrase.'"');  
               
         //die($key_pair_result["Output"]);
         
                                    if(isset($key_pair_result["Output"]))
                                    {
                                       
                                          $key_pair_path= explode("key_file: ",read_specific_line($key_pair_result["Output"],2))[1];
                                          
                                          if(file_exists($key_pair_path))
                                          {
                                              $made=true;
                                          }
                                              
                                        
                                          
                                    }
    }
    
    return $made;
}


function make_address_multi_sig($key_pair_path,$tvc,$abi)
{
    $raw_address=false;
    
   // die("hahaha");
    if(ensure_ton_url())
    {
       // echo ton_cli_command." getkeypair ".$key_pair_name.' "'.$seed_phrase.'"';
         $make_address_result=run_ssh_command(ton_cli_command." genaddr ".$tvc." ".$abi." --setkey ".$key_pair_path." --wc ".default_work_chain_id);  
               
        // die($make_address_result["Output"]);
         
                                    if(isset($make_address_result["Output"]))
                                    {
                                       
                                          $raw_address= explode("Raw address: ",read_specific_line($make_address_result["Output"],8))[1];
                                          
                                         
                                              
                                        
                                          
                                    }
    }
    
    return $raw_address;
}


function send_some_tokens_multisig($from_raw_address,$to_raw_address,$from_key_file,$abi,$amount)
{
    $sent=false;
    
   // die("hahaha");
    if(ensure_ton_url())
    {
        /*
        $array=array("dest"=>$to_raw_address,
                    "value"=>$amount,
                    "bounce"=>false,
                    "allBalance"=>false,
                    "payload"=>'',
                    
            
        );
        */
      
         $array=array("dest"=>$to_raw_address,
                    "value"=>$amount,
                    "bounce"=>false
                    
            
        );
      
       
        $command= ton_cli_command." call ".$from_raw_address." sendTransaction ".json_encode(json_encode($array))." --abi ".$abi." --sign ".$from_key_file;
        
       // die($command);
       // echo ton_cli_command." getkeypair ".$key_pair_name.' "'.$seed_phrase.'"';
         $make_transaction=run_ssh_command($command);  
               
         //echo($make_transaction["Output"]);
         
                                    if(isset($make_transaction["Output"]))
                                    {
                                       
                                        $sent=see_if_target_string_exists($make_transaction["Output"],"Succeeded.");
                                           
                                        /*
                                          $status= read_specific_line($make_transaction["Output"],15);
                                          
                                          //echo $status.':::::::';
                                         if($status=="Succeeded.")
                                         {
                                             $sent=true;
                                         }
                                          */    
                                        
                                          
                                    }
    }
    
    return $sent;
}


function deploy_my_multisig($tvc,$owners,$req,$abi,$signjson)
{
    $deployed=false;
    
   // die("hahaha");
    if(ensure_ton_url())
    {
        $array=array("owners"=>$owners,
                    "reqConfirms"=>$req,
                    
            
        );
        
        $command= ton_cli_command." deploy ".$tvc." ".json_encode(json_encode($array))." --abi ".$abi." --sign ".$signjson;
        
        //die($command);
       // echo ton_cli_command." getkeypair ".$key_pair_name.' "'.$seed_phrase.'"';
         $deploy_results=run_ssh_command($command);  
               
        // die($make_transaction["Output"]);
         
                                    if(isset($deploy_results["Output"]))
                                    {
                                            $deployed=see_if_target_string_exists($deploy_results["Output"],"Transaction succeeded.");
                                       
                                          
                                              
                                        
                                          
                                    }
    }
    
    return $deployed;
}

function get_rates_to_exchange_ton()
{
    

    $array=array();
    $array_to_bounce=array('url'=>coinmarketingurl."coin/".coinmarketingurl_ton_code,
                    'myvars'=>'',
                    'header_array'=>array(' '.coinmarketingurl_header.':'.coinmarketingurl_token.' '),
                    'post'=>0
                );
    
    
    $coin=json_decode(send_curl_post(bounce_traffic_url, json_encode($array_to_bounce), array(),true),true);
   
    //die(json_encode($coin));
    
    if($coin["status"]=="success")
    {
        $bank=get_the_rates_for_me_cached();
         
       // echo json_encode($bank);
        
        if($bank["success"]==true)
        {
            //echo json_encode($bank);
            $array=$coin["data"]["coin"];
           // $array["quotes"]=$bank["quotes"];
            unset($array["btcPrice"]);
            $array["price_kes"]=($array["price"]*$bank["quotes"]["USDKES"]);
             $array["price_kes_actual"]=($array["price"]*$bank["quotes"]["USDKES"])-($array["price"]*$bank["quotes"]["USDKES"])*(percentage_offset/100);
             $array["price_kes_flamingo_cut_price"]=($array["price"]*$bank["quotes"]["USDKES"])*(percentage_offset/100);
             $array["price_kes_flamingo_cut_percentage"]=percentage_offset;
             //$array["price_kes_flamingo_usd_kes_rate"]=$bank["quotes"]["USDKES"];
             $array["price_kes_flamingo_kes_usd_rate"]=$bank["quotes"]["USDKES"];
             $array["price_kes_flamingo_all_quotes"]=$bank["quotes"];
        }
        
    }
    
     
    
    return $array;
}


function get_the_rates_for_me_cached()
{
    $bank=array();
    
    //check if it exists
      $data= SelectFromTableOnPreparedQuery("SELECT * FROM `".currency_temp."`  WHERE   ".currency_temp.".url_hash ='".md5(apilayerurl)."' AND  ".currency_temp.".time_stamp >= '".storable_datetime_function(time())."' ")[0];
      
      if(empty($data))
      {
          
           $bank=json_decode(send_curl_post(apilayerurl."?access_key=".apilayer_access_key."&currencies=".apilayer_access_currencies."&format=2", "", array(),false),true);
           //check if exists
           if(CheckIfExistsTwoColumnsFunction(currency_temp, 'url_hash', 'url_hash', md5(apilayerurl), md5(apilayerurl)))//does not exist insert
           {
               InsertIntoCurrencyTemp(currency_temp,md5(apilayerurl) ,json_encode($bank), storable_datetime_function(time()+max_cache_time));
           }
           else//update
           {
               UpdateTableOneCondition(currency_temp, 'response', json_encode($bank), 'url_hash', md5(apilayerurl));
               UpdateTableOneCondition(currency_temp, 'time_stamp', storable_datetime_function(time()+max_cache_time), 'url_hash', md5(apilayerurl));
           }
      }
      else
      {
          $bank= json_decode($data['response'],true);
      }
    return $bank;
}


function send_curl_post($url,$myvars,$header_array,$post=1)
{
   
     $ch = curl_init( $url );//initialize response
            
    //die($myvars);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);//ignore sign in
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);//ignore sign in
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);//set fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array); 
        
            
            curl_setopt( $ch, CURLOPT_POST, $post);//as post
        
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );//true to url
        curl_setopt( $ch, CURLOPT_HEADER, 0 );//header null
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);//catch the response
        
        
       
        return curl_exec($ch);
    
}
 

function do_multisig_transfare($multisig_address,$destination_address,$value,$multisig_abi,$custodian_key_file)
{
    $transaction_id=null;
    
    if(ensure_ton_url())
    {
         //decide on bounce
        /*
            $member_account_balance_data=get_account_balance($destination_address);
            $bounce=true;//assume contract exists
            if($member_account_balance_data['balance']==0)
            {
               $bounce=false;//contract does not exist
            }

            */
            $bounce=false;
                                              // die(json_encode($member_account_balance_data));

           $array=array("dest"=>$destination_address,
                           "value"=>(int)($value*1000000000),
                           "bounce"=>$bounce,
                           "allBalance"=>false,
                           "payload"=>""
                       );

              $command= ton_cli_command." call ".'"'.$multisig_address.'"'." submitTransaction ".json_encode(json_encode($array))." --abi ".$multisig_abi." --sign ".$custodian_key_file;

         //   die($command);
            //
              $deploy_results=run_ssh_command($command);  

            // echo($deploy_results["Output"]).'****************';
               if(isset($deploy_results["Output"]))
               {

                    $transaction_id= explode('"transId": ',read_specific_line($deploy_results["Output"],17))[1];
                    $transaction_id= str_replace('"', "", $transaction_id);      
                }
     }
    
     return $transaction_id;
}


function confirm_transaction_multisig($multisig_address,$transaction_id,$multisig_abi,$custodian_key_file)
{
    $done=false;
    
     if(ensure_ton_url())
    {
         $array=array("transactionId"=>$transaction_id );
    
        $command= ton_cli_command." call ".$multisig_address." confirmTransaction ".json_encode(json_encode($array))." --abi ".$multisig_abi." --sign ".$custodian_key_file;

        //die($command);
        $deploy_results=run_ssh_command($command);  

        //   echo($deploy_results["Output"]).'======================';
            if(isset($deploy_results["Output"]))
            {

                
                $done=see_if_target_string_exists( $deploy_results["Output"],"Succeeded.");
                 
            }
     }
     
    
    return $done;
}