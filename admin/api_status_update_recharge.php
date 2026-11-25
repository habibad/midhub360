<?php
require_once dirname(__DIR__) . '/lib/guard.php'; midhub_guard_cli_or_super_admin(); // SECURITY: was reachable without admin login
require_once '../db.php';
require_once 'autoload.php';

$apicount = Api::apistatus("1","eFlexiload","Recharge");

if($apicount == 1) //Check API Active/Inactive
{
		       
        $sql1 = "SELECT * from send_query where status='6' and api_reid!=0 ORDER BY id ASC LIMIT 15";
		$count = DB::getInstance()->get($sql1)->Count();

		if($count > 0)
        {
			$retrieve  = DB::getInstance()->get($sql1)->result();
           
			foreach ($retrieve as $flexi)
            {
				$number     = $flexi->phone;
				$type       = $flexi->load_type;
				$amount     = $flexi->balance;
				$flexiId    = $flexi->id;
                $userid     = $flexi->user_id;
                $api_reid   = $flexi->api_reid;

               
                /*API Request Start*/
                $url = Api::apihistorystatus("$api_reid");                    

                $client = curl_init($url);
                curl_setopt($client, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($client, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
                curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($client, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($client, CURLOPT_SSLVERSION, 1);
                curl_setopt($client, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

                $response = curl_exec($client); 
                $result = json_decode($response); 

                curl_close($client);
                        
                $status     = $result->STATUS;
                $txn        = $result->TXNID;
                $errormsg   = $result->message;             
                                
                /*API Request End*/



                echo "<br>" . $errormsg . "<br>";

                echo "Status: " . $status . "<br><br>";

                echo "Number: " . $amount . "<br><br>";

                echo "Amount: " . $number . "<br><br>";

                echo "ID: " . $flexiId . "<br><br>";

				
                if($status == "SUCCESS")
                {					

                     /*Update History Table Start*/

                     // only settle rows still processing (6); overlapping runs must not settle twice
                     $query = "UPDATE send_query SET status='1' WHERE id = " . (int) $flexiId . " AND status='6'";
                     global $connect;
                     $result = mysqli_query($connect, $query);
                     if(!$result)
                     {
                         die("Query Failed!" . mysqli_error($connect));
                     }
                     if (mysqli_affected_rows($connect) !== 1) { continue; }

                      /*Update History Table End*/

                      /*Insert Data to Reportserver Table Start*/                      
                      $query  = "INSERT INTO reportserver (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,date)";
                      $query .= "VALUES(0,'API','nomsg','$userid','$amount','$txn','$number','$flexiId',NOW(),'1','0',CURDATE())"; 

                      $result = mysqli_query($connect, $query);    
                      if(!$result)
                      {
                          
                          die("Query Failed2!" . mysqli_error($connect));
                      }
                      /*Insert Data to Reportserver Table End*/
                      \Midhub\Commission\CommissionService::payForRecharge((int) $flexiId); // 5-level commission, exactly once

                      
				}
                elseif($status == "FAILED")
                {					

                     /*Update History Table Start*/

                     // claim the row first so an overlapping run cannot refund it twice
                     $query = "UPDATE send_query SET status='3' WHERE id = " . (int) $flexiId . " AND status='6'";
                     global $connect;
                     $result = mysqli_query($connect, $query);
                     if(!$result)
                     {
                         die("Query Failed!" . mysqli_error($connect));
                     }
                     if (mysqli_affected_rows($connect) !== 1) { continue; }

                      /*Update History Table End*/


                     /*Update User Balance (relative increment: no lost updates) */

                     $queryup = "UPDATE user SET balance2 = balance2 + " . (float) $amount . " WHERE id = " . (int) $userid;
                     
                      $resultup = mysqli_query($connect, $queryup);    
                      if(!$resultup)
                      {                      
                      die("Query Failed4!" . mysqli_error($connect));
                      }

                       /*Update User Balance End*/



                      
				}
                else
                {
					continue;
				}

               
                
			}



            echo "<br>Successfull Checked and Updated!";

		}
        else
        {
			echo json_encode
            (
				array
                (
					'status'  => 'error',
					'message' => 'Recently has no new request'
				)
			);
		}


}
else
{
    echo json_encode
    (
        array
        (
            'status'  => 'error',
            'message' => 'API Inactive!'
        )
    );
}
	
