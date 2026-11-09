<?php 
//session_start();
require_once '../db.php';
require_once 'autoload.php';

$user   = Xss::xssClean(Input::get('user'));
$key    = Xss::xssClean(Input::get('key'));
$number = Xss::xssClean(Input::get('number'));
$amount = Xss::xssClean(Input::get('amount'));
$type   = Xss::xssClean(Input::get('type'));
$rid   = Xss::xssClean(Input::get('rid'));


/* validate request information Start*/
if(!isset($user) || empty($user))
{      
      $msg = "ERROR : INVALID REQUEST";
      $response['RESPONSE'] = $msg;
      echo json_encode($response);
      exit();
}

if(!isset($key) || empty($key))
{
      $msg = "ERROR : INVALID REQUEST";
      $response['RESPONSE'] = $msg;
      echo json_encode($response);
      exit();
}

if(!isset($number) || empty($number))
{
      $msg = "ERROR : INVALID REQUEST";
      $response['RESPONSE'] = $msg;
      echo json_encode($response);
      exit();
}

if(!isset($amount) || empty($amount))
{
      $msg = "ERROR : INVALID REQUEST";
      $response['RESPONSE'] = $msg;
      echo json_encode($response);
      exit();
}

if(!isset($type) || empty($type))
{
      $msg = "ERROR : INVALID REQUEST";
      $response['RESPONSE'] = $msg;
      echo json_encode($response);
      exit();
}
if(!isset($rid) || empty($rid))
{
      $msg = "ERROR : INVALID REQUEST";
      $response['RESPONSE'] = $msg;
      echo json_encode($response);
      exit();
}



//echo $user. "<br>" . $key . "<br>" . $number . "<br>" . $amount . "<br>" . $type;


/* validate request information end*/

/* Function for same request restriction start*/

function getSendingRestriction($phone)
    {
        $error = 0;
        $sql = "SELECT phone from send_query where phone='$phone' and (status='0' or status='4') limit 1";
        global $connect;
        $result = mysqli_query($connect, $sql); 

        $count = mysqli_num_rows($result); 

        if ($count > 0)
        {
            $error = 1;
        }

        return $error;
    }

  /* Function for same request restriction End*/


/* Operator Name start*/

$operatorType  = substr($number, 0, 3);
if ($operatorType == '017') {
      $oprerator = 'grameenphone';
} elseif ($operatorType == '018') {
      $oprerator = 'robi';
} elseif ($operatorType == '019') {
      $oprerator = 'banglalink';
} elseif ($operatorType == '016') {
      $oprerator = 'airtel';
} elseif ($operatorType == '015') {
      $oprerator = 'teletalk';
} elseif ($operatorType == '011') {
      $oprerator = 'cc';
} elseif ($operatorType == '013') {
      $oprerator = 'grameenphone'; 
} elseif ($operatorType == '014') {
      $oprerator = 'banglalink';     
} else {
      echo 'ERROR : PREFIX NOT FOUND';
      exit();
}

/* Operator Name end*/

/*Query for User Validity start*/

$query  = "SELECT * from user where user='$user' and api_key='$key' and active='1'";    
    
$result = mysqli_query($connect, $query); 

$reseller_data= mysqli_fetch_object($result);


$count = mysqli_num_rows($result);  // If result matched $user and $pass, table row must be 1 row

/*Query for User Validity End*/


/*Query for rid check start*/

$query1  = "SELECT * from send_query where api_reid='$rid'";    
    
$result1 = mysqli_query($connect, $query1); 

$count1 = mysqli_num_rows($result1);  // If result matched $user and $pass, table row must be 1 row

/*Query for User Validity End*/


if($count == 1) 
{  

      $user_id     =$reseller_data->id;
      $balance     =$reseller_data->balance2;
      $api_status  =$reseller_data->api_status;

      $restrict = getSendingRestriction($number);
           

      if ($api_status == 0)
      {
            $msg = "ERROR: Your API Account is Inactive!";            
      }
      elseif ($key == "0")
      {
            $msg = "ERROR: API Key is expired!";           
      }      
      elseif ($balance < $amount)
      {
            $msg = "ERROR: Insufficient Balance!";           
      }
      elseif (strlen($number) < 11)
      {                
            $msg = "ERROR: Mobile number must be 11 digit!";            
      }
      elseif(!is_numeric($number)) 
      {                
            $msg = "ERROR: Mobile Number allow only Number!";           
      }
      elseif(!is_numeric($amount)) 
      {                
            $msg = "ERROR : Amount allow only Number!";           
      }
      elseif(!is_numeric($rid)) 
      {                
            $msg = "ERROR : rid allow only Number!";           
      }
      elseif($count1 >= 1) 
      {                
            $msg = "ERROR : rid already exist!";           
      }
      elseif ($amount < 5 || $amount > 10000)
      {
            $msg = "ERROR : Invalid Amount. Amount must be between 9 to 10000!";           
      } 
      elseif ($restrict == 1)
      {
            $msg = "ERROR : You already requested this number. Please wait for completed before requested!";            
      }      
      else 
      {
            $time = time();
            $refrom='API';
            
            $upbalance = $balance - $amount;
            $query = "UPDATE user set balance2='$upbalance' where id='$user_id'";
            global $connect; 
            $result = mysqli_query($connect, $query);    
            if(!$result)
            {
                
                die("Query Failed!" . mysqli_error($connect));
            }

            $apicount = Api::apistatus("1","eFlexiload","Recharge"); //API Active/Inactive Function

            if($apicount == 1)  //check API active or inactive
            {
                  $status = Api::apirequest("$number","$amount","$type","$rid"); //recharge request pass to API

                  if($status == "SUCCESS")
                  {
                        $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid,txnid)";
                        $query .= "VALUES(0,'$user_id','$number','$type','$amount',NOW(),'6','$time','$oprerator',CURDATE(),'0','0','0','0','0','0','$refrom','$rid','0')";
      
                        $result = mysqli_query($connect, $query); 
      
                        if(!$result)
                        {                        
                              die("Query Failed!" . mysqli_error($connect));
                        }
                  }
                  else
                  {
                        $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid,txnid)";
                        $query .= "VALUES(0,'$user_id','$number','$type','$amount',NOW(),'0','$time','$oprerator',CURDATE(),'0','0','0','0','0','0','$refrom','$rid','0')";
      
                        $result = mysqli_query($connect, $query); 
      
                        if(!$result)
                        {                        
                              die("Query Failed!" . mysqli_error($connect));
                        }   
                  }

            }
            else
            {
          
                  $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid,txnid)";
                  $query .= "VALUES(0,'$user_id','$number','$type','$amount',NOW(),'0','$time','$oprerator',CURDATE(),'0','0','0','0','0','0','$refrom','$rid','0')";

                  $result = mysqli_query($connect, $query); 

                  if(!$result)
                  {                        
                        die("Query Failed!" . mysqli_error($connect));
                  }
            
            }
            
            $msg = "SUCCESS";           
   
      }


      $response['RESPONSE'] = $msg;


      echo json_encode($response);

      //var_dump(json_decode($saju));

      exit();
   

}
else 
{
      $msg = "ERROR : INVALID USER";
      $response['RESPONSE'] = $msg;
      echo json_encode($response);
      exit();
}

?>
