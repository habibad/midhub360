<?php 

/*Database Connection Start */

$server_name      = "localhost";
$db_user          = "aanonda_ebill24";
$db_password      = "9viN0_EkpUgM";
$db_name          = "aanonda_ebills24";


$connect = mysqli_connect($server_name, $db_user, $db_password, $db_name);


if(!$connect){
    
   die("Database is not connected!" . mysqli_connect_error());
    
}  


/*Database Connection End*/

?>