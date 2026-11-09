<?php

function transaction_id($trans_id)
{

$query= "SELECT transactionid from reportserver where f_id='$trans_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$txnid =$reseller_data->transactionid ?? "none";
//echo "$username";
return $txnid;
}


?>