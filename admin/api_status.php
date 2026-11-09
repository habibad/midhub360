<?php
require_once '../db.php';
require_once 'api_function.php';

$apicount = apistatus("1","BillsMama","Recharge");

if($apicount == 1)
{

                                     
                $query  = "SELECT * from send_query where status='0' ORDER BY id DESC LIMIT 1";    
                
                $result = mysqli_query($connect, $query); 

                $reseller_data= mysqli_fetch_object($result);

                $count1 = mysqli_num_rows($result);

                if($count1 == 1)
                {
                    
                    $number  = $reseller_data->phone;
                    $type    = $reseller_data->load_type;
                    $amount  = $reseller_data->balance;
                    $flexiId = $reseller_data->id;

                    echo "Number" . $number . "<br>";

                    //Check API History

                    $status = apihistorystatus("$flexiId");

                    echo "Status: ". $status;
                    

                    /* */

                }
                else
                {
                    echo "No Data Found2";
                   exit();
                }


}
else
{
    echo "API Inactive!";
    exit();
}

?>