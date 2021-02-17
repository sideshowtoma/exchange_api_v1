<?php
header('Content-Type: application/json');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
