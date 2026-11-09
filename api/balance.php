<?php 
//session_start();
//header("Content-Type:application/json");
require_once '../db.php';
require_once 'autoload.php';

$user   = Xss::xssClean(Input::get('user'));
$key    = Xss::xssClean(Input::get('key'));

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

$query  = "SELECT * from user where user='$user' and api_key='$key' and active='1'";    
    
$result = mysqli_query($connect, $query); 

$reseller_data= mysqli_fetch_object($result);

$count = mysqli_num_rows($result); 



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