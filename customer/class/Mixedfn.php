<?php
//include "../../db.php";

class Mixedfn
{

   
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
            
    
        /*Start Function for level name*/

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

         /*End Function for level name*/
   
    
    
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

         public static function totalrecharge($resid)
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM send_query WHERE user_id='$resid'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/

         
         /*Start Function for get total recharge amount start*/

         public static function totaldrive($resid)
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM drive_request WHERE user_id='$resid'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


          
         /*Start Function for get total recharge amount start*/

         public static function totalmBank($resid)
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM bkash_send WHERE user_id='$resid'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


            
         /*Start Function for get total recharge amount start*/

         public static function totalbillPay($resid)
         {
 
         $query= "SELECT SUM(balance) AS value_sum FROM billpay_request WHERE user_id='$resid'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/


             
         /*Start Function for get total recharge amount start*/

         public static function totalbilReq($resid)
         {
 
         $query= "SELECT SUM(amount) AS value_sum FROM add_fund WHERE user_id='$resid'";
         global $connect;
         $result = mysqli_query($connect, $query);
         $reseller_data= mysqli_fetch_object($result);
         $total_bl =$reseller_data->value_sum ?? 0;
         //echo "$username";
         return $total_bl;
         }
 
         /*Start Function for get total recharge amount end*/




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


           
          /*Start Function for total balance start*/

          public static function totalbl($que1,$que2)
          {
  
          $query= "SELECT SUM(amount) AS value_sum FROM $que1 $que2";
          global $connect;
          $result = mysqli_query($connect, $query);
          $reseller_data= mysqli_fetch_object($result);
          $total_sale =$reseller_data->value_sum ?? 0;
          //echo "$username";
          return $total_sale;
          }
  
          /*Start Function for get total recharge amount end*/


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


              /*Start Function for get Recharge Balance*/

              public static function ac_balance($bltype,$user_id)
              {
  
              $query= "SELECT $bltype from user where id='$user_id'";
              global $connect;
              $result = mysqli_query($connect, $query);
              $get_data= mysqli_fetch_object($result);   
              return $get_data;
              }
  
              /*End Function for get Recharge Balance*/


              public static function low_balance($user_id)
              {
                  $query="SELECT * from user where id='$user_id'";
                  global $connect;
                  $row=mysqli_query($connect, $query);
                  $reseller_data= mysqli_fetch_object($row);
                  $low_balance=$reseller_data->limit_balance;
                  return $low_balance;
              }

         
              public static function default_low_balance($limitbl)
              {
                  $lowbl_query="SELECT $limitbl from settings where id='1'";
                  global $connect;
                  $row=mysqli_query($connect, $lowbl_query);
                  $reseller_data= mysqli_fetch_object($row);
                  $default_low_balance=$reseller_data->$limitbl;
                  return $default_low_balance;
              }


              
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


}


?>