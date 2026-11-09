<?php

/*Start Function for Brand data*/

function branddata($para)
{

$query= "SELECT * from branding where id='1'";
global $connect;
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
} 

$reseller_data= mysqli_fetch_object($result);
$getdata =$reseller_data->$para;
return $getdata;
}

/*End Function for Brand data*/


/*Start Function for Company Data*/

function CompanyData($para)
{

$query= "SELECT * from company where id='1'";
global $connect;
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
} 

$reseller_data= mysqli_fetch_object($result);
$getdata =$reseller_data->$para;
return $getdata;
}

/*End Function for Company Data*/


?>