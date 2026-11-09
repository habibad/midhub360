<?php
include"../db.php";
require_once 'autoload.php';

/*Check Block IP Start*/

$count = Security::CheckBlockIP();

if($count > 0)
{
    header("location: forbidden.php");
}

/*Check Block IP End*/


/*Check Country Block Start*/

$query2 = "SELECT * FROM settings where id='1'";        
$result2 = mysqli_query($connect, $query2);

if(!$result2){
    die("Query Failed!" . mysqli_error($connect));      
}        

$data           = mysqli_fetch_object($result2); 
$country_block  = $data->country_block;

if($country_block == 1)
{

        $country_code = Security::CheckCountry();

        $query1  = "SELECT * FROM country_allow where CountryCode='$country_code'"; 
        $result1 = mysqli_query($connect, $query1); 
        $count1 = mysqli_num_rows($result1);

        if($count1 == 0)
        {
            header("location: ../block_country.php");
        }

}

/*Check Country Block End*/



?>