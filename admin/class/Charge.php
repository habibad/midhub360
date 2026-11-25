<?php

class Charge
{
     /*Start Function for find commission*/

     public static function findcharge($id,$type)
     {

     $query= "SELECT * FROM addfund_charge WHERE id='$id'";
     global $connect;
     $result = mysqli_query($connect, $query);

     if(!$result){
         die("Query Failed!" . mysqli_error($connect));      
     } 

     $row= mysqli_fetch_object($result);
     $charge = $row->$type;
     return $charge;
     }

     /*End Function for find commission */
}


?>