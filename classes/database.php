<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//connect to db
function ConnetToDatabaseFuntion()//requires host name, user name ,pass word and database
{
        
            if (!mysqli_connect(database_host,database_user_name,database_user_password,database_name))
            {
                die("Could not connect to database".'--'.database_host.'--'.database_user_name.'--'.database_user_password.'--'.database_name."\n");	// kill script incase of error
            }
            else
            {
                return mysqli_connect(database_host,database_user_name,database_user_password,database_name);
            }

}		         



//script to check if a certain value exists using two column values
function CheckIfExistsTwoColumnsFunction($TableName,$ColumnData1,$ColumnData2,$MatchWith1,$MatchWith2)
{
       
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

        $query="SELECT `$ColumnData1` , `$ColumnData2` FROM `$TableName` WHERE `$ColumnData1` = '".mysqli_real_escape_string($Connection,$MatchWith1)."' AND `$ColumnData2` = '".mysqli_real_escape_string($Connection,$MatchWith2)."'"; 
        $myquery=mysqli_query($Connection,$query);
        $num=mysqli_num_rows($myquery);
        mysqli_free_result($myquery);
        if($num>=1)
        {
            
             $Connection->close();
             return false;// if the match is found 
        }
        else 
        {
             $Connection->close();
             return true;// if the match is not found

        }

}

function CheckIfExistsThreeColumnsFunction($TableName,$ColumnData1,$ColumnData2,$ColumnData3,$MatchWith1,$MatchWith2,$MatchWith3)
{
       
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

        $query="SELECT `$ColumnData1` , `$ColumnData2` FROM `$TableName` WHERE `$ColumnData1` = '".mysqli_real_escape_string($Connection,$MatchWith1)."' AND `$ColumnData2` = '".mysqli_real_escape_string($Connection,$MatchWith2)."' AND `$ColumnData3` = '".mysqli_real_escape_string($Connection,$MatchWith3)."'  "; 
        $myquery=mysqli_query($Connection,$query);
        $num=mysqli_num_rows($myquery);
        mysqli_free_result($myquery);
        if($num>=1)
        {
            
             $Connection->close();
             return false;// if the match is found 
        }
        else 
        {
             $Connection->close();
             return true;// if the match is not found

        }

}

 //select on one condition from 
function SelectTableOnTwoConditions($TableName,$ConditionColumn1,$ConditionValue1,$ConditionColumn2,$ConditionValue2)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

       $my_county_select=$my_county_select="SELECT * FROM `$TableName` WHERE `$ConditionColumn1`='".mysqli_real_escape_string($Connection,$ConditionValue1)."' AND `$ConditionColumn2`='".mysqli_real_escape_string($Connection,$ConditionValue2)."' ";
	
      
		$do_my_county_select=mysqli_query($Connection,$my_county_select);
               
                if($do_my_county_select)
                {
                        $selected_manage_data=mysqli_fetch_all($do_my_county_select,MYSQLI_ASSOC);
                         
                        $Connection->close();
                        mysqli_free_result($do_my_county_select);
                        return $selected_manage_data;
                }
                else 
                {
                         die("could not select on two conditions table");
                }

}

function SelectTableOnFourConditions($TableName,$ConditionColumn1,$ConditionValue1,$ConditionColumn2,$ConditionValue2,$ConditionColumn3,$ConditionValue3,$ConditionColumn4,$ConditionValue4)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

       $my_county_select=$my_county_select="SELECT * FROM `$TableName` WHERE `$ConditionColumn1`='".mysqli_real_escape_string($Connection,$ConditionValue1)."' AND `$ConditionColumn2`='".mysqli_real_escape_string($Connection,$ConditionValue2)."'  AND `$ConditionColumn3`='".mysqli_real_escape_string($Connection,$ConditionValue3)."'  AND `$ConditionColumn4`='".mysqli_real_escape_string($Connection,$ConditionValue4)."'";
	
      
		$do_my_county_select=mysqli_query($Connection,$my_county_select);
               
                if($do_my_county_select)
                {
                        $selected_manage_data=mysqli_fetch_all($do_my_county_select,MYSQLI_ASSOC);
                        
                        $Connection->close();
                        mysqli_free_result($do_my_county_select);
                        return $selected_manage_data;
                }
                else 
                {
                         die("could not select on four conditions table");
                }

}

 //select on one condition from first half under table
function SelectTableAll($TableName)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

       $my_county_select=$my_county_select="SELECT * FROM `$TableName` ";
								
		$do_my_county_select=mysqli_query($Connection,$my_county_select);
               
                if($do_my_county_select)
                {
                        $selected_manage_data=mysqli_fetch_all($do_my_county_select,MYSQLI_ASSOC);
                        
                        $Connection->close();
                        mysqli_free_result($do_my_county_select);
                        return $selected_manage_data;
                }
                else 
                {
                         die("could not select on all table");
                }

}

function UpdateTableOneCondition($TableName,$set_column,$set_column_value,$check_column,$check_column_value)
{
        
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

       
        $update_ward_table_query="UPDATE `$TableName` SET 
                                   `$set_column`='". mysqli_escape_string($Connection, $set_column_value)."'
                                   WHERE `$check_column`='$check_column_value' ";
        
        //echo $update_ward_table_query.'<br>';
                $do_my_ward_update=mysqli_query($Connection,$update_ward_table_query);
                if($do_my_ward_update)
                {

                        $Connection->close();
                        return true;
                }
                else 
                {
                       return false;
                    
                }

}

function UpdateTableTWOCondition($TableName,$set_column,$set_column_value,$check_column1,$check_column_value1,$check_column2,$check_column_value2)
{
        
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

       
        $update_ward_table_query="UPDATE `$TableName` SET 
                                   `$set_column`='$set_column_value'
                                   WHERE `$check_column1`='$check_column_value1'AND `$check_column2`='$check_column_value2'  ";
        
        //echo $update_ward_table_query.'<br>';
                $do_my_ward_update=mysqli_query($Connection,$update_ward_table_query);
                if($do_my_ward_update)
                {

                        $Connection->close();
                        return true;
                }
                else 
                {
                       return false;
                    
                }

}


function UpdateTableQuery($query)
{
        
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

       
        $update_ward_table_query=$query;
        
        //echo $update_ward_table_query.'<br>';
                $do_my_ward_update=mysqli_query($Connection,$update_ward_table_query);
                if($do_my_ward_update)
                {

                        $Connection->close();
                        return true;
                }
                else 
                {
                       return false;
                    
                }

}

function SelectFromTableOnPreparedQuery($query)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

         $my_county_select=$query;
								
		$do_my_county_select=mysqli_query($Connection,$my_county_select);
                
                if($do_my_county_select)
                {
                        $selected_manage_data=mysqli_fetch_all($do_my_county_select,MYSQLI_ASSOC);
                        
                        $Connection->close();
                        mysqli_free_result($do_my_county_select);
                        return $selected_manage_data;
                }
                else 
                {
                         die("could not select pre prepped query".$query);
                }

}






function DeleteSpecificRowONTwoConditions($TableName,$Column1,$Column2,$ColumnValue1,$ColumnValue2)
{
        
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

        $delete_query="DELETE FROM `$TableName` WHERE `$Column1` = '$ColumnValue1' AND `$Column2` = '$ColumnValue2'";

        $do_delete_query=mysqli_query($Connection,$delete_query);

        if($do_delete_query)
        {
                mysqli_free_result($do_delete_query);
                $Connection->close();
                return true;
        }
        else 
        {
                mysqli_free_result($do_delete_query);
                $Connection->close();
                return false;
        }

}


function InsertIntoUsersTable($TableName,$email_address,$name,$type,$id_or_passport,$id_passport_number,$telephone_number,$password,$date_of_birth,$comments,$gender,$time_stamp)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();
        
        
        $insert_into_table ="INSERT INTO `$TableName`(`email_address`,
                                                        `name`,
                                                        `type`,
                                                        `id_or_passport`,
                                                        `id_passport_number`,
                                                        `telephone_number`,
                                                        `password`,
                                                        `date_of_birth`,
                                                        `comments`,
                                                        `gender`,
                                                        `time_stamp`) 
                                          VALUES ('". mysqli_escape_string($Connection, $email_address)."',
                                                   '". mysqli_escape_string($Connection, $name)."',
                                                   '". mysqli_escape_string($Connection, $type)."',
                                                   '". mysqli_escape_string($Connection, $id_or_passport)."',
                                                   '". mysqli_escape_string($Connection, $id_passport_number)."',
                                                   '". mysqli_escape_string($Connection, $telephone_number)."',
                                                   '". mysqli_escape_string($Connection, $password)."',
                                                   '". mysqli_escape_string($Connection, $date_of_birth)."',
                                                   '". mysqli_escape_string($Connection, $comments)."',
                                                    '". mysqli_escape_string($Connection, $gender)."',
                                                   '". mysqli_escape_string($Connection, $time_stamp)."')";
//die($insert_into_table);
        //echo $insert_into_table.'<br><br><br><br>';
        
                                if($insert_into_table_query=mysqli_query($Connection,$insert_into_table))
                                {
                                        mysqli_free_result($insert_into_table_query);
                                        $Connection->close();
                                        return true;
                                }
                                else 
                                {
                                    die("could not insert into users table".$insert_into_table);
                                }


}


function InsertIntoUserAccountsTable($TableName,$user_id,$wallet_code,$pass_phrase,$time_stamp)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();
        
        
        $insert_into_table ="INSERT INTO `$TableName`(`user_id`,
                                                        `wallet_code`,
                                                        `pass_phrase`,
                                                        `time_stamp`) 
                                          VALUES ('". mysqli_escape_string($Connection, $user_id)."',
                                                   '". mysqli_escape_string($Connection, $wallet_code)."',
                                                   '". mysqli_escape_string($Connection, $pass_phrase)."',
                                                   '". mysqli_escape_string($Connection, $time_stamp)."')";
//die($insert_into_table);
        //echo $insert_into_table.'<br><br><br><br>';
        
                                if($insert_into_table_query=mysqli_query($Connection,$insert_into_table))
                                {
                                        mysqli_free_result($insert_into_table_query);
                                        $Connection->close();
                                        return true;
                                }
                                else 
                                {
                                    die("could not insert into user_accounts table".$insert_into_table);
                                }


}


function InsertIntoBankTable($TableName,$name,$buying_selling,$raw_id,$phrase,$max_votes,$time_stamp)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();
        
        
        $insert_into_table ="INSERT INTO `$TableName`(`name`,
                                                        `buying_selling`,
                                                        `raw_id`,
                                                        `phrase`,
                                                        `max_votes`,
                                                        `time_stamp`) 
                                          VALUES ('". mysqli_escape_string($Connection, $name)."',
                                                   '". mysqli_escape_string($Connection, $buying_selling)."',
                                                   '". mysqli_escape_string($Connection, $raw_id)."',
                                                   '". mysqli_escape_string($Connection, $phrase)."',
                                                   '". mysqli_escape_string($Connection, $max_votes)."',
                                                   '". mysqli_escape_string($Connection, $time_stamp)."')";
//die($insert_into_table);
        //echo $insert_into_table.'<br><br><br><br>';
        
                                if($insert_into_table_query=mysqli_query($Connection,$insert_into_table))
                                {
                                        mysqli_free_result($insert_into_table_query);
                                        $Connection->close();
                                        return true;
                                }
                                else 
                                {
                                    die("could not insert into bank table".$insert_into_table);
                                }


}



function InsertIntoBankSponsorsAmountTable($TableName,$bank_id,$user_id,$is_sponsor,$amount,$time_stamp)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();
        
        
        $insert_into_table ="INSERT INTO `$TableName`(`bank_id`,
                                                        `user_id`,
                                                        `is_sponsor`,
                                                        `amount`,
                                                        `time_stamp`) 
                                          VALUES ('". mysqli_escape_string($Connection, $bank_id)."',
                                                   '". mysqli_escape_string($Connection, $user_id)."',
                                                   '". mysqli_escape_string($Connection, $is_sponsor)."',
                                                   '". mysqli_escape_string($Connection, $amount)."',
                                                   '". mysqli_escape_string($Connection, $time_stamp)."')";
//die($insert_into_table);
        //echo $insert_into_table.'<br><br><br><br>';
        
                                if($insert_into_table_query=mysqli_query($Connection,$insert_into_table))
                                {
                                        mysqli_free_result($insert_into_table_query);
                                        $Connection->close();
                                        return true;
                                }
                                else 
                                {
                                    die("could not insert into bank_sponsors table".$insert_into_table);
                                }


}

function InsertIntoBankSponsorsNullTable($TableName,$bank_id,$user_id,$is_sponsor,$time_stamp)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();
        
        
        $insert_into_table ="INSERT INTO `$TableName`(`bank_id`,
                                                        `user_id`,
                                                        `is_sponsor`,
                                                        `time_stamp`) 
                                          VALUES ('". mysqli_escape_string($Connection, $bank_id)."',
                                                   '". mysqli_escape_string($Connection, $user_id)."',
                                                   '". mysqli_escape_string($Connection, $is_sponsor)."',
                                                   '". mysqli_escape_string($Connection, $time_stamp)."')";
//die($insert_into_table);
        //echo $insert_into_table.'<br><br><br><br>';
        
                                if($insert_into_table_query=mysqli_query($Connection,$insert_into_table))
                                {
                                        mysqli_free_result($insert_into_table_query);
                                        $Connection->close();
                                        return true;
                                }
                                else 
                                {
                                    die("could not insert into bank_sponsors table".$insert_into_table);
                                }


}



function InsertIntoTransactionsTable($TableName,$user_id,$bank_id,$buying,$amount,$exchange_rate,$pin_id,$mode,$narrative,$comments,$time_stamp)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();
        
        
        $insert_into_table ="INSERT INTO `$TableName`(`user_id`,
                                                        `bank_id`,
                                                        `buying`,
                                                        `amount`,
                                                        `exchange_rate`,
                                                        `pin_id`,
                                                        `mode`,
                                                        `narrative`,
                                                        `comments`,
                                                        `time_stamp`) 
                                          VALUES ('". mysqli_escape_string($Connection, $user_id)."',
                                                   '". mysqli_escape_string($Connection, $bank_id)."',
                                                   '". mysqli_escape_string($Connection, $buying)."',
                                                   '". mysqli_escape_string($Connection, $amount)."',
                                                   '". mysqli_escape_string($Connection, $exchange_rate)."',
                                                   '". mysqli_escape_string($Connection, $pin_id)."',
                                                   '". mysqli_escape_string($Connection, $mode)."',
                                                   '". mysqli_escape_string($Connection, $narrative)."',
                                                   '". mysqli_escape_string($Connection, $comments)."',
                                                   '". mysqli_escape_string($Connection, $time_stamp)."')";
//die($insert_into_table);
        //echo $insert_into_table.'<br><br><br><br>';
        
                                if($insert_into_table_query=mysqli_query($Connection,$insert_into_table))
                                {
                                        mysqli_free_result($insert_into_table_query);
                                        $Connection->close();
                                        return true;
                                }
                                else 
                                {
                                    die("could not insert into users_transactions table".$insert_into_table);
                                }


}


function GetTableNumRowsWithCondition($TableName,$column1,$value1,$column2,$value2)
{
        
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();

        $query="SELECT * FROM `$TableName` WHERE `".$column1."` = '".$value1."' AND  `".$column2."` = '".$value2."'";

        $do_query=mysqli_query($Connection,$query);

        if($do_query)
        {
                $num= mysqli_num_rows($do_query); 
                mysqli_free_result($do_query);
                $Connection->close();
                return $num;
        }
        else 
        {
                
                return 0;
        }

}


function InsertIntoTransactionsTempTable($TableName,$user_id,$bank_id,$amount,$pin_id,$mode,$comments,$authorization,$time_stamp)
{
    
        //connecting to database
        $Connection=ConnetToDatabaseFuntion();
        
        
        $insert_into_table ="INSERT INTO `$TableName`(`user_id`,
                                                        `bank_id`,
                                                        `amount`,
                                                        `pin_id`,
                                                        `mode`,
                                                        `comments`,
                                                        `authorization`,
                                                        `time_stamp`) 
                                          VALUES ('". mysqli_escape_string($Connection, $user_id)."',
                                                   '". mysqli_escape_string($Connection, $bank_id)."',
                                                   '". mysqli_escape_string($Connection, $amount)."',
                                                   '". mysqli_escape_string($Connection, $pin_id)."',
                                                   '". mysqli_escape_string($Connection, $mode)."',
                                                   '". mysqli_escape_string($Connection, $comments)."',
                                                   '". mysqli_escape_string($Connection, $authorization)."',
                                                   '". mysqli_escape_string($Connection, $time_stamp)."')";
//die($insert_into_table);
        //echo $insert_into_table.'<br><br><br><br>';
        
                                if($insert_into_table_query=mysqli_query($Connection,$insert_into_table))
                                {
                                        mysqli_free_result($insert_into_table_query);
                                        $Connection->close();
                                        return true;
                                }
                                else 
                                {
                                    die("could not insert into users_transactions_temp table".$insert_into_table);
                                }


}