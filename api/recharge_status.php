<?php 
//session_start();
require_once '../db.php';
require_once 'function.php';
require_once 'autoload.php';

$user   = Xss::xssClean(Input::get('user'));
$key    = Xss::xssClean(Input::get('key'));
$number = Xss::xssClean(Input::get('number'));
$amount = Xss::xssClean(Input::get('amount'));
$type   = Xss::xssClean(Input::get('type'));
$rid    = Xss::xssClean(Input::get('rid'));
require_once __DIR__ . '/guard.php';
midhub_api_validate(array('user' => $user, 'key' => $key, 'rid' => $rid)); // SECURITY: whitelist before any SQL

/* validate request information Start*/
if(!isset($user) || empty($user))
{     
      echo json_encode
      (
      array
      (
         'status'  => 'error',
         'message' => 'INVALID REQUEST'
      )
      );        
 
      exit();
}

if(!isset($key) || empty($key)){
      echo json_encode
      (
      array
      (
         'status'  => 'error',
         'message' => 'INVALID REQUEST'
      )
      );        
 
      exit();
}

if(!isset($rid) || empty($rid)){
      echo json_encode
      (
      array
      (
         'status'  => 'error',
         'message' => 'INVALID REQUEST'
      )
      );        
 
      exit();
}



/* validate request information end*/


$reseller_data = null; list($reseller_data, $count) = midhub_api_auth($user, $key); // prepared statement + constant-time key check


if($count == 1) 
{  

      $user_id     =$reseller_data->id;
      $balance     =$reseller_data->balance2;
      $api_status  =$reseller_data->api_status;

     
      if ($api_status == 0)
      {
            echo json_encode
            (
                  array
                  (
                     'status'  => 'error',
                     'message' => 'Your API Account is Inactive!'
                  )
                  );

                  exit();  
      }
      elseif ($key == "0")
      {
            echo json_encode
            (
                  array
                  (
                     'status'  => 'error',
                     'message' => 'Key is Expired!'
                  )
                  );

                  exit();              
           
      }
      elseif ($rid == "0")
      {
            echo json_encode
            (
                  array
                  (
                     'status'  => 'error',
                     'message' => 'rid is invalid!'
                  )
                  );

                  exit();              
           
      }       
      elseif(!is_numeric($rid)) 
      {                
           
            echo json_encode
            (
                  array
                  (
                     'status'  => 'error',
                     'message' => 'rid allow only number!'
                  )
                  );

                  exit();        
      }       
      else 
      {
            // scoped to the authenticated reseller: rids are guessable integers
            $query= "SELECT * from send_query where api_reid='" . (int) $rid . "' AND user_id = " . (int) $reseller_data->id . " ORDER BY id ASC LIMIT 1";

            $result = mysqli_query($connect, $query); 

            if(!$result)
            {                        
                  die("Query Failed!" . mysqli_error($connect));
            }


            $reseller_data= mysqli_fetch_object($result);

            $count1 = mysqli_num_rows($result);

            if($count1 == 1)
            {

                  $recharge_id  = $reseller_data->id;
                  $status       = $reseller_data->status;
                  $amount       = $reseller_data->balance;

                  $txn_id = transaction_id($recharge_id);


                  if($status == 1){
                        $msg = "SUCCESS";
                  }elseif($status == 0){
                        $msg = "PENDING";
                  }elseif($status == 2){
                        $msg = "CANCELLED";
                  }elseif($status == 3){
                        $msg = "FAILED";
                  }elseif($status == 4){
                        $msg = "WAITING";
                  }


                  $response["STATUS"] = "$msg";
                  $response["TXNID"]  = "$txn_id";            
            
                  echo json_encode($response);
                  exit();

            }
            else
            {
                  echo json_encode
                  (
                        array
                        (
                           'status'  => 'error',
                           'message' => 'Invalid rid!'
                        )
                        );
      
                        exit();  
            } 

             
      }
   

}
else 
{
      echo json_encode
            (
		      array
                  (
			   'status'  => 'error',
			   'message' => 'INVALID USER'
			)
			);

      exit();
}



?>