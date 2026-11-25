<?php
//include "../../db.php";

class Mixedfn
{

    public static function SimBalance($reId)
    {

        $query= "SELECT balance from reportserver where f_id='$reId' ORDER BY id DESC LIMIT 1";
        global $connect;
        $result = mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($result);
        $op_balance =$reseller_data->balance ?? 0;
        //echo "$username";
        return $op_balance;


    }


    public static function CustomerLevel($reId)
    {

        $query= "SELECT level from user where id='$reId'";
        global $connect;
        $result = mysqli_query($connect, $query);
        $get_data= mysqli_fetch_object($result);
        $level =$get_data->level ?? 0;
        //echo "$username";
        return $level;


    }


        /*Start Function for get Username*/

        public static function username($user_id)
        {

        $query= "SELECT user from user where id='$user_id'";
        global $connect;
        $result = mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($result);
        $username =$reseller_data->user ?? 0;
        //echo "$username";
        return $username;
        }

        /*End Function for get Username*/


         /*Start Function for get total recharge amount start*/

         public static function totalrecharge()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM send_query";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/

         /*Start Function for get total recharge amount start*/

         public static function totalbank()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM bank_request";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/

         
         /*Start Function for get total recharge amount start*/

         public static function totaldrive()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM drive_request";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


          
         /*Start Function for get total recharge amount start*/

         public static function totalmBank()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM bkash_send";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


            
         /*Start Function for get total recharge amount start*/

         public static function totalbillPay()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM billpay_request";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


             
         /*Start Function for get total recharge amount start*/

         public static function totalbilReq()
         {
 
         $query= "SELECT SUM(amount) AS value_sum FROM add_fund";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


              
         /*Start Function for get total recharge amount start*/

         public static function totalpendingre()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM send_query WHERE status='0'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/

         /*Start Function for get total recharge amount start*/

         public static function totalpendingbank()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM bank_request WHERE status='0'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/

                
         /*Start Function for get total recharge amount start*/

         public static function totalpendingdrive()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM drive_request WHERE status='0'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


                 
         /*Start Function for get total recharge amount start*/

         public static function totalpendingmbank()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM bkash_send WHERE status='0'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


                   
         /*Start Function for get total recharge amount start*/

         public static function totalpendingbillpay()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM billpay_request WHERE status='0'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


                     
         /*Start Function for get total recharge amount start*/

         public static function totalpendingaddfund()
         {
 
         $query= "SELECT SUM(amount) AS value_sum FROM add_fund WHERE status='0'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


                       
         /*Start Function for get total recharge amount start*/

         public static function totalwaitingre()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM send_query WHERE status='4'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


           /*Start Function for get total number pending recharge*/

           public static function totalnumpre()
           {
   
           $query= "SELECT id FROM send_query WHERE status='0'";
           global $connect;
           $result = mysqli_query($connect, $query); 
           if(!$result)
           {
               die("Query Failed!" . mysqli_error($connect));      
           }        
                      
           $count = mysqli_num_rows($result); 
           return $count;
           }
   
           /*Start Function for get total number pending recharge end*/

           /*Start Function for get total number pending recharge*/

           public static function totalnumpdrive()
           {
   
           $query= "SELECT id FROM drive_request WHERE status='0'";
           global $connect;
           $result = mysqli_query($connect, $query); 
           if(!$result)
           {
               die("Query Failed!" . mysqli_error($connect));      
           }        
                      
           $count = mysqli_num_rows($result); 
           return $count;
           }
   
           /*Start Function for get total number pending recharge end*/

           /*Start Function for get total number pending recharge*/

           public static function totalnumpmb()
           {
   
           $query= "SELECT id FROM bkash_send WHERE status='0'";
           global $connect;
           $result = mysqli_query($connect, $query); 
           if(!$result)
           {
               die("Query Failed!" . mysqli_error($connect));      
           }        
                      
           $count = mysqli_num_rows($result); 
           return $count;
           }
   
           /*Start Function for get total number pending recharge end*/


           /*Start Function for get total number pending recharge*/

           public static function totalnumbill()
           {
   
           $query= "SELECT id FROM billpay_request WHERE status='0'";
           global $connect;
           $result = mysqli_query($connect, $query); 
           if(!$result)
           {
               die("Query Failed!" . mysqli_error($connect));      
           }        
                      
           $count = mysqli_num_rows($result); 
           return $count;
           }
   
           /*Start Function for get total number pending recharge end*/


           /*Start Function for get total number pending recharge*/

           public static function totalnumpaddfund()
           {
   
           $query= "SELECT id FROM add_fund WHERE status='0'";
           global $connect;
           $result = mysqli_query($connect, $query); 
           if(!$result)
           {
               die("Query Failed!" . mysqli_error($connect));      
           }        
                      
           $count = mysqli_num_rows($result); 
           return $count;
           }
   
           /*Start Function for get total number pending recharge end*/
           

           /*Start Function for get total number pending recharge*/

           public static function totalnumpbank()
           {
   
           $query= "SELECT id FROM bank_request WHERE status='0'";
           global $connect;
           $result = mysqli_query($connect, $query); 
           if(!$result)
           {
               die("Query Failed!" . mysqli_error($connect));      
           }        
                      
           $count = mysqli_num_rows($result); 
           return $count;
           }
   
           /*Start Function for get total number pending recharge end*/


            /*Start Function for get total number pending recharge*/

            public static function totalnumwre()
            {
    
            $query= "SELECT id FROM send_query WHERE status='4'";
            global $connect;
            $result = mysqli_query($connect, $query); 
            if(!$result)
            {
                die("Query Failed!" . mysqli_error($connect));      
            }        
                       
            $count = mysqli_num_rows($result); 
            return $count;
            }
    
            /*Start Function for get total number pending recharge end*/



                 
         /*Start Function for get total recharge amount start*/

         public static function retodaysale()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM send_query WHERE status='1' and s_date=CURDATE()";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_sale =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_sale;
         }
 
         /*Start Function for get total recharge amount end*/


         /*Start Function for get total recharge amount start*/

         public static function drivetodaysale()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM drive_request WHERE status='1' and s_date=CURDATE()";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_sale =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_sale;
         }
 
         /*Start Function for get total recharge amount end*/


         /*Start Function for get total recharge amount start*/

         public static function mbanktodaysale()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM bkash_send WHERE status='1' and s_date=CURDATE()";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_sale =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_sale;
         }
 
         /*Start Function for get total recharge amount end*/


         
         /*Start Function for get total recharge amount start*/

         public static function billktodaysale()
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM billpay_request WHERE status='1' and s_date=CURDATE()";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_sale =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_sale;
         }
 
         /*Start Function for get total recharge amount end*/



          /*Start Function for operator  sales start*/

          public static function opsale($que1,$que2)
          {
  
          $query= "SELECT SUM(balance) AS value_sum FROM send_query $que1 $que2";
          global $connect;
          $result = mysqli_query($connect, $query);
          $reseller_data= mysqli_fetch_object($result);
          $total_sale =$reseller_data->value_sum ?? 0;
          //echo "$username";
          return $total_sale;
          }
  
          /*Start Function for get total recharge amount end*/



          /*Start Function for Mobile Banking  sales start*/

          public static function mbsale($que1,$que2)
          {
  
          $query= "SELECT SUM(balance) AS value_sum FROM bkash_send $que1 $que2";
          global $connect;
          $result = mysqli_query($connect, $query);
          $reseller_data= mysqli_fetch_object($result);
          $total_sale =$reseller_data->value_sum ?? 0;
          //echo "$username";
          return $total_sale;
          }
  
          /*Start Function for Mobile Banking amount end*/


          
          /*Start Function for operator  sales start*/

          public static function salesreport($que1,$que2)
          {
  
          $query= "SELECT SUM(balance) AS value_sum FROM $que1 $que2";
          global $connect;
          $result = mysqli_query($connect, $query);
          $reseller_data= mysqli_fetch_object($result);
          $total_sale =$reseller_data->value_sum ?? 0;
          //echo "$username";
          return $total_sale;
          }
  
          /*Start Function for get total recharge amount end*/


           
          /*Start Function for total customer start*/

          public static function totalcs($level)
          {
  
          $query= "SELECT id FROM user WHERE level='$level'";
          global $connect;
          $result = mysqli_query($connect, $query);
          $total_cs = mysqli_num_rows($result);

          //echo "$username";
          return $total_cs;
          }
  
          /*Start Function for get total recharge amount end*/



           /*Start Function for operator  sales start*/

           public static function cstotalbl($balance,$level)
           {
   
           $query= "SELECT SUM($balance) AS value_sum FROM user WHERE level='$level'";
           global $connect;
           $result = mysqli_query($connect, $query);
           $reseller_data= mysqli_fetch_object($result);
           $total_balance =$reseller_data->value_sum ?? 0;
           //echo "$username";
           return $total_balance;
           }
   
           /*Function for get total recharge amount end*/


           
           /*Start Function for IP Blocked start*/

           public static function blockip()
           {

            $block_ip    = Input::get('blockip');

            if (filter_var($block_ip, FILTER_VALIDATE_IP)) 
            {
   
                  $query  = "INSERT INTO ip_block (id,block_ip)";
                  $query .= "VALUES(0,'$block_ip')"; 

                  global $connect;                     
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {
                              
                  die("Query Failed!" . mysqli_error($connect));
                  }

                  Session::Flush('success', 'Successfully Blocked this ' .$block_ip. ' IP ');
                  Redirect::to('block_ip.php'); 

            }
            else
            {
                  Session::Flush('error', $block_ip .'  is not a valid IP address! Valid IP example: <strong>127.0. 0.1</strong>');
                  Redirect::to('block_ip.php');
            }
            
            }
   
           /*Function for IP Blocked end*/



                            
            /*Start Function for Boss ID*/

            public static function boss_id($user_id)
            {

            $query= "SELECT created_by from user where id='$user_id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                die("Query Failed!" . mysqli_error($connect));      
            } 

            $row= mysqli_fetch_object($result);
            $boss_id =$row->created_by ?? 0;
            return $boss_id;
            }

            /*End Function for Boss ID*/


                             
            /*Start Function for recharge balance*/

            public static function rebalance($user_id)
            {

            $query= "SELECT balance2 from user where id='$user_id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                die("Query Failed!" . mysqli_error($connect));      
            } 

            $row= mysqli_fetch_object($result);
            $rebalance =$row->balance2 ?? 0;
            return $rebalance;
            }

            /*End Function for recharge balance */

            /*Start Function for recharge balance*/

            public static function mbbalance($user_id)
            {

            $query= "SELECT bkash_balance from user where id='$user_id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                die("Query Failed!" . mysqli_error($connect));      
            } 

            $row= mysqli_fetch_object($result);
            $mbbalance =$row->bkash_balance ?? 0;
            return $mbbalance;
            }

            /*End Function for recharge balance */

            /*Start Function for recharge balance*/

            public static function findcommission($id,$level)
            {

            $query= "SELECT * FROM commission WHERE id='$id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                die("Query Failed!" . mysqli_error($connect));      
            } 

            $row= mysqli_fetch_object($result);
            $commission =$row->$level ?? 0;
            return $commission;
            }

            /*End Function for recharge balance */
            

            /*Start Function for account fee*/

            public static function findacfee($id,$level)
            {

            $query= "SELECT * FROM account_fee WHERE id='$id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                die("Query Failed!" . mysqli_error($connect));      
            } 

            $row= mysqli_fetch_object($result);
            $commission =$row->$level ?? 0;
            return $commission;
            }

            /*End Function for account fee*/


            /*Start Function for billpay charge fee*/

            public static function billpaycharge($id,$level)
            {

            $query= "SELECT * FROM billpay_charge WHERE id='$id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                die("Query Failed!" . mysqli_error($connect));      
            } 

            $row= mysqli_fetch_object($result);
            $commission =$row->$level ?? 0;
            return $commission;
            }

             /*End Function for billpay charge fee*/

            /*Start Function for level name change*/

            public static function levelnamechange($id,$level)
            {

            $query= "SELECT * FROM level_name WHERE id='$id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                die("Query Failed!" . mysqli_error($connect));      
            } 

            $row= mysqli_fetch_object($result);
            $level_name =$row->$level ?? 0;
            return  $level_name;
            }

             /*End Function for level name change*/


            /*Start Function for update balance from auto add*/

            public static function upbalance_autofund($balance,$update_balance,$userid)
            {
               
                $query = "UPDATE user SET $balance='$update_balance' WHERE id='$userid'";                                    
                global $connect;
                $result = mysqli_query($connect, $query);    
                if(!$result)
                {                      
                die("Query Failed4!" . mysqli_error($connect));
                }

            }

           /*Start Function for update balance from auto add end*/


            /*Start Function for update balance from auto add*/

            public static function insertpayhist_autofund($payment,$withcomm,$user_id,$note,$userId,$recharge_balance,$update_balance)
            {

                $ip =$_SERVER["REMOTE_ADDR"];

                $queryin  = "INSERT INTO  $payment (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
                $queryin .= "VALUES(0,'$user_id','$withcomm',CURDATE(),'$userId',NOW(),'$note','$ip','0','$recharge_balance','$update_balance')"; 
                global $connect;         
                $resultin = mysqli_query($connect, $queryin);    
                if(!$resultin)
                {
                            
                die("Query Failed2!" . mysqli_error($connect));
                }

            }

           /*Start Function for update balance from auto add end*/



         /*Start Function for Brand data*/

         public static function webdata($para)
            {

            $query= "SELECT * FROM web_setting where id='1'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                die("Query Failed!" . mysqli_error($connect));      
            } 

            $reseller_data= mysqli_fetch_object($result);
            $getdata =  $reseller_data->$para;
            return $getdata;
            }

        /*End Function for Brand data*/




           

}


?>