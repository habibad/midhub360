<?php

class Balance
{
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
    
}

?>