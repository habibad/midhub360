<?php 
//session_start();
//header("Content-Type:application/json");
require_once '../db.php';
require_once 'autoload.php';

$user   = Xss::xssClean(Input::get('user'));
$key    = Xss::xssClean(Input::get('key'));
require_once __DIR__ . '/guard.php';
midhub_api_validate(array('user' => $user, 'key' => $key)); // SECURITY: whitelist before any SQL

/* validate request information Start*/
if(!isset($user) || empty($user)){
      echo 'ERROR : INVALID REQUEST';
      exit();
}

if(!isset($key) || empty($key)){
      echo 'ERROR : INVALID REQUEST';
      exit();
}


//echo $user. "<br>" . $key . "<br>" . $number . "<br>" . $amount . "<br>" . $type;


/* validate request information end*/

$reseller_data = null; list($reseller_data, $count) = midhub_api_auth($user, $key); // prepared statement + constant-time key check



if($count == 1) 
{  

      $user_id     =$reseller_data->id;
      $balance     =$reseller_data->balance2;
      $mb_balance  =$reseller_data->bkash_balance;

      $api_status  =$reseller_data->api_status;

      $response['BALANCE'] = "$balance";
      
     
      if ($api_status == 0)
      {
            $msg = "Your API Account is Inactive!";
            //echo 'ERROR : Your API Account is Inactive!';
            //exit();
      }
      elseif ($key == "0")
      {
            $msg = "API Key is expired!";
            //echo 'ERROR : API Key is expired!';
            //exit();
      }     
      else 
      {
            $msg = $response;
            //echo $balance;
            //exit();
   
      }
   

}
else 
{
      $msg = "INVALID USER";
      //echo 'ERROR : INVALID USER';
      //exit();
}


echo json_encode($msg);

//var_dump(json_decode($saju));

exit();

?>