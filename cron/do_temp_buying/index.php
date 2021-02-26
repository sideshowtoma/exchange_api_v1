<?php
require '/var/www/html/exchange_api_v1/classes/constants.php';
require '/var/www/html/exchange_api_v1/classes/functions.php';
require '/var/www/html/exchange_api_v1/classes/database.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//* * * * * /usr/bin/php /var/www/html/exchange_api_v1/cron/do_temp_buying/index.php
//*/5 * * * * /usr/bin/php /var/www/html/exchange_api_v1/cron/do_temp_buying/index.php
//echo 'hahaha';
//sudo nano /etc/crontab

$select_all_undone= SelectTableOnTwoConditions(users_transactions_temp, 'done', 'no', 'done', 'no');

//die(json_encode($select_all_undone));
foreach ($select_all_undone as $value) 
{
    $vars='user_id='.$value['user_id'].'&bank_id='.$value['bank_id'].'&amount='.$value['amount'].'&pin_id='.$value['pin_id'].'&mode='.$value['mode'].'&comments='.$value['comments'].'';
     $buy=json_decode(send_curl_post(local_url.'/requests/user_buy_grams/', $vars, array('Authorization: '.$value['authorization'].''),true),true);
     
     echo json_encode($buy);
     if($buy["check"]==true)
     {
         UpdateTableOneCondition(users_transactions_temp, 'done', 'yes', '_id', $value['_id']);
         
     }
}