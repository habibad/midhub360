<?php 

/*Database Connection Start */

$server_name      = "localhost";
$db_user          = "aanonda_eload024";
$db_password      = "V1911Z%cR_@@";
$db_name          = "midhub-db";



$connect = mysqli_connect($server_name, $db_user, $db_password, $db_name);


if(!$connect){
    
   die("Database is not connected!" . mysqli_connect_error());
    
}  


/*Database Connection End*/

?>