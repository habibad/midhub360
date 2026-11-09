<?php 

/*Database Connection Start */

$server_name      = "localhost";
$db_user          = "billsmama_hosting4bd";
$db_password      = "2HrDeOr-m2~)";
$db_name          = "billsmama_eloadbd";

$connect = mysqli_connect($server_name, $db_user, $db_password, $db_name);


if(!$connect){
    
   die("Database is not connected!" . mysqli_connect_error());
    
}  


/*Database Connection End*/

?>