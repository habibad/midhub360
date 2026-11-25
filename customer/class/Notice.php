<?php

class Notice 
{
        
           
         /*Notice Count Method Start*/
          public static function NoticeCount($userid)
          {
             
             global $connect;
             $query  = "SELECT count(*) as total_count FROM notification WHERE seen='0' and UserId='$userid'"; 
             $result = mysqli_query($connect, $query); 
             if(!$result)
             {
                 die("Query Failed!" . mysqli_error($connect));      
             }   

             $data= mysqli_fetch_object($result);
             $total_count =$data->total_count ?? 0;
 
             return $total_count;
          }
 
          /*Notice Count Method End*/
 
           

           
          /*Start Function for total customer start*/

          public static function notification($userid)
          {
  
          $query= "SELECT * FROM notification WHERE UserId='$userid' order by id desc limit 10";
          global $connect;
          $result = mysqli_query($connect, $query);
          
          //echo "$username";
          return $result;
          }
  
          /*Start Function for get total recharge amount end*/

                     
         /*Start Function for Notification Details start*/

         public static function ReadNotification($nid,$userid)
         {
 
         $query= "SELECT * FROM notification WHERE id='$nid' and UserId='$userid'";
         global $connect;
         $result = mysqli_query($connect, $query);
         
         //echo "$username";
         return $result;
         }
 
         /*Start Function for Notification Details end*/


         public static function InsertNotification($userid,$message,$from)
         {

            $query  = "INSERT INTO notification (id,UserId,submitted_time,message,MsgFrom,seen,date)";
            $query .= "VALUES(0,'$userid',NOW(),'$message','$from','0',CURDATE())";

            global $connect;
    
            $result = mysqli_query($connect, $query);    
            if(!$result)
            {
                
                die("Query Failed!" . mysqli_error($connect));
            }
         }


         /* Read Notice start*/

         public static function ReadNotice($nid)
         {
 
         $query= "SELECT * FROM noticelist WHERE id='$nid'";
         global $connect;
         $result = mysqli_query($connect, $query);
         
         //echo "$username";
         return $result;
         }
 
         /*Read Notice end*/



}

?>