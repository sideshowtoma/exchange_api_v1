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

$request=get_my_post_get_variables(array('user_id','sort_by','sort_order','skip','limit','buying_selling','search_phrase'));

//die(json_encode($request));
$user_id=$request['user_id'];
$sort_by=$request['sort_by'];
$sort_order=$request['sort_order'];
$skip=$request['skip'];
$limit=$request['limit'];
$search_phrase=$request['search_phrase'];
$buying_selling=$request['buying_selling'];




if( isset($user_id) && !empty($user_id) &&
    isset($sort_by) && !empty($sort_by) &&
        isset($sort_order) && !empty($sort_order) &&
   isset($limit) && !empty($limit) 
        )
{
     if(  verify_session_key())
    {
      
       
         if($buying_selling=='buying' ||  $buying_selling=='selling')
         {
                if($limit>0 && is_numeric($limit) && ($skip=='' || ($skip>=0 && is_numeric($skip))  ) )
                {
                    $sort_order= strtoupper($sort_order);

                    if($sort_order=='ASC' || $sort_order=='DESC')
                    {
                         $search_phrase=isset($search_phrase) && !empty($search_phrase) ? 
                                                " 
                                                    AND ( 
                                                        ".users_transactions.".pin_id LIKE '%".$search_phrase."%' OR
                                                        ".users_transactions.".mode LIKE '%".$search_phrase."%' OR
                                                        ".users_transactions.".narrative LIKE '%".$search_phrase."%' OR
                                                        ".users_transactions.".comments LIKE '%".$search_phrase."%' OR
                                                        ".users_transactions.".time_stamp LIKE '%".$search_phrase."%'

                                                            )
                                                "  :"";

                            $query_is="SELECT ".users_transactions."._id AS '_id', 
                                        ".bank.".name  AS 'bank_name',
                                        ".users_transactions.".amount AS 'amount',
                                        ".users_transactions.".exchange_rate AS 'exchange_rate',
                                        ".users_transactions.".amount / ".users_transactions.".exchange_rate  AS 'grams',
                                        ".users_transactions.".pin_id AS 'pin_id',
                                        ".users_transactions.".mode AS 'mode',
                                        ".users_transactions.".narrative AS 'narrative',
                                        ".users_transactions.".comments AS 'comments',
                                        DATE_FORMAT(".users_transactions.".time_stamp, '%d-%m-%Y %k:%i:%s') AS 'time_stamp'
                                        FROM `".users_transactions."` 
                                        INNER JOIN ".bank." ON ".bank."._id =".users_transactions.".bank_id
                                        WHERE  ".users_transactions.".user_id = $user_id AND ".users_transactions.".buying = $buying_selling  ".$search_phrase."
                                        ORDER BY `".users_transactions."`.`".$sort_by."` $sort_order LIMIT ".$limit." OFFSET ".$skip." ";

                            $query_is_all= SelectFromTableOnPreparedQuery($query_is);



                            $response= json_encode(array("check"=>true,"message"=>$query_is_all,"total_rows"=> GetTableNumRowsWithCondition(users_transactions, 'user_id', $user_id, 'buying', $buying_selling)));

                    }
                    else
                    {
                         $response= json_encode(array("check"=>false,"message"=>"Sort order can only be DESC for descending or ASC for ascending order."));
                    }




                }
                else
                {
                    $response= json_encode(array("check"=>false,"message"=>"Invalid skip or limit values, skip must be a number equal to 0 or greater than 0 and limit must be a number greater than 0."));
                }
         }
         else
         {
             $response= json_encode(array("check"=>false,"message"=>"Buying selling can only be 'selling' or 'buying'."));
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