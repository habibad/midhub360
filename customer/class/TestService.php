<?php

class TestService
{
      /**
       *       function for get payment
       *       @param $id | null | Mixed | reseller Id
       *       @return array | object
       */
      public static function getPayment($id = '')
      {
            $paySql = "SELECT * FROM pay_receive WHERE userid = '$id' ORDER BY id DESC LIMIT 6";
            $getPay = DB::getInstance()->get($paySql)->result();
            return $getPay;
      }
	  
	  public static function checkUserbalance($reseller_id = '')
      {
		$us_id = Session::get('us_id');
            $amountSql = "SELECT * FROM user where id='$reseller_id' and created_by='$us_id'";
            $getPay = DB::getInstance()->get($amountSql)->result();
			
            return $getPay[0]->balance2;
      }
	  
	  public static function checkUserbalancemb($reseller_id = '')
      {
		$us_id = Session::get('us_id');
            $amountSql = "SELECT * FROM user where id='$reseller_id' and created_by='$us_id'";
            $getPay = DB::getInstance()->get($amountSql)->result();
			
            return $getPay[0]->bkash_balance;
      }
	  
	  
	  public static function checkSelfbalance()
      {
		$us_id = Session::get('us_id');
            $amountSql = "SELECT * FROM user where id='$us_id'";
            $getPay = DB::getInstance()->get($amountSql)->result();
			
            return $getPay[0]->balance2;
      }
	  
	  public static function checkSelfbalancemb()
      {
		$us_id = Session::get('us_id');
            $amountSql = "SELECT * FROM user where id='$us_id'";
            $getPay = DB::getInstance()->get($amountSql)->result();
			
            return $getPay[0]->bkash_balance;
      }
	  

     
      /* Customer Add Balance Start */

      public static function transfer()
      {
            $resId    = Input::get('resId');            
            $amount   = Input::get('amount');
            $type     = Input::get('type');
            $note     = Input::get('des');
            $balance  = Input::get('balance');
            $userPin  = Input::get('pincode');
		$ip	    = $_SERVER["REMOTE_ADDR"];
		$payment_type = "";
            $us_id      = Session::get('us_id');
            $amount     = (int)$amount;
           


            $query= "SELECT * from user where id='$us_id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
            die("Query Failed!" . mysqli_error($connect));      
            } 

            $reseller_data = mysqli_fetch_object($result);
            $get_pincode = $reseller_data->pin_code;
            $get_selfbalance = $reseller_data->balance2;
            $get_selfmbbalance = $reseller_data->bkash_balance;
            $get_selfusername = $reseller_data->user;


            $inputpin = md5($userPin);

                                     
            if ($get_pincode != $inputpin) 
            {
                  Session::Flush('error', 'Pincode is Invalid');
                  Redirect::to('cs-profile.php?reseller_id='.$resId);
                  
            }
            elseif(!is_numeric($userPin)) 
            {                
                  Session::Flush('error', 'Pin Code allow only number!');
                  Redirect::to('cs-profile.php?reseller_id='.$resId);
            }
            elseif (empty($userPin)) 
            {               
                  Session::Flush('error', 'You must enter your Pin Code!');
                  Redirect::to('cs-profile.php?reseller_id='.$resId);
            }
            elseif(!is_numeric($amount)) 
            {                
                  Session::Flush('error', 'Amount allow only number!');
                  Redirect::to('cs-profile.php?reseller_id='.$resId);
            }
            //elseif(!is_int($amount)) 
            elseif(is_int($amount) == false)
            {                
                  Session::Flush('error', 'Amount allow only full amount!'. is_int($amount));
                  Redirect::to('cs-profile.php?reseller_id='.$resId);
            }
		elseif(intval($amount)==0)
		{
		      Session::Flush('error', 'Amount is not valid');
			Redirect::to('cs-profile.php?reseller_id='.$resId);
		}
		elseif($amount<1 || $amount>100000)
		{
			Session::Flush('error', 'Amount is not valid');
			Redirect::to('cs-profile.php?reseller_id='.$resId);
		}
		elseif(strlen($amount)<1)
		{
			Session::Flush('error', 'Amount is not valid');
			Redirect::to('cs-profile.php?reseller_id='.$resId);
		}                       
            elseif (!preg_match("/^([A-Za-z0-9,-: ])*$/", $note))                                    
            {
                  Session::Flush('error', 'Note allow only letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                  Redirect::to('cs-profile.php?reseller_id='.$resId);                           
            }
            else
            {
                  if($type == "Transfer")
                  {		
                                              
                        if($get_selfbalance < $amount)
                        {
                              Session::Flush('error', 'Insufficient Your Recharge Balance!');
                              Redirect::to('cs-profile.php?reseller_id='.$resId);
                        }
                        else 
                        {

                              /*Update Self Balance */                        
                             
                              $update_selfbalance = $get_selfbalance - $amount;

                              $queryself = "UPDATE user SET balance2='$update_selfbalance' WHERE id='$us_id'";
                              
                              $resultself = mysqli_query($connect, $queryself);    
                              if(!$resultself)
                              {                      
                              die("Query Failed4!" . mysqli_error($connect));
                              }
                              
                              /*Update Self Balance End*/
                              
                              
                              /*Update User Balance */
                        
                              $querysl= "SELECT balance2 from user where id='$resId'";                        
                              $resultsl = mysqli_query($connect, $querysl);

                              if(!$resultsl){
                              die("Query Failed!3" . mysqli_error($connect));      
                              } 

                              $reseller_data= mysqli_fetch_object($resultsl);
                              $balance =$reseller_data->balance2;

                              $update_balance = $balance + $amount;

                              $queryup = "UPDATE user SET balance2='$update_balance' WHERE id='$resId'";
                              
                              $resultup = mysqli_query($connect, $queryup);    
                              if(!$resultup)
                              {                      
                              die("Query Failed4!" . mysqli_error($connect));
                              }
                              
                              /*Update User Balance End*/

                              $queryin  = "INSERT INTO  reseller_balance (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
                              $queryin .= "VALUES(0,'$resId','$amount',NOW(),'$us_id',NOW(),'$note','$ip','0','$balance','$update_balance')"; 
                                          
                              $resultin = mysqli_query($connect, $queryin);    
                              if(!$resultin)
                              {                                          
                              die("Query Failed2!" . mysqli_error($connect));
                              }

                                    $notifica_msgre = "Recharge Balance added to your account from " . $get_selfusername . " Amount: " . $amount . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($resId,$notifica_msgre,$notifica_from);                  
                        
                                    Session::Flush('success', 'Successfully Balance Added!');
                                    Redirect::to('cs-profile.php?reseller_id='.$resId); 
                                    
                        }
                        
                  }
                  elseif($type == "TransferMb")
                  {
                         
                        if($get_selfmbbalance < $amount)
                        {
                              Session::Flush('error', 'Insufficient Your M. Banking Balance');
                              Redirect::to('cs-profile.php?reseller_id='.$resId);
                        }
                        else 
                        {

                                    /*Update Self Balance */                        
                             
                                    $update_selfmbbalance = $get_selfmbbalance - $amount;

                                    $queryself = "UPDATE user SET bkash_balance='$update_selfmbbalance' WHERE id='$us_id'";
                                    
                                    $resultself = mysqli_query($connect, $queryself);    
                                    if(!$resultself)
                                    {                      
                                    die("Query Failed4!" . mysqli_error($connect));
                                    }
                                    
                                    /*Update Self Balance End*/

                                    /*Update User Balance */

                                    $querysl= "SELECT bkash_balance from user where id='$resId'";                        
                                    $resultsl = mysqli_query($connect, $querysl);

                                    if(!$resultsl){
                                    die("Query Failed!3" . mysqli_error($connect));      
                                    } 

                                    $reseller_data= mysqli_fetch_object($resultsl);
                                    $balance =$reseller_data->bkash_balance;

                                    $update_balance = $balance + $amount;

                                    $queryup = "UPDATE user SET bkash_balance='$update_balance' WHERE id='$resId'";
                                    
                                    $resultup = mysqli_query($connect, $queryup);    
                                    if(!$resultup)
                                    {                      
                                    die("Query Failed4!" . mysqli_error($connect));
                                    }
                                    
                                    /*Update User Balance End*/

                                    $queryin  = "INSERT INTO  bkash_balance (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
                                    $queryin .= "VALUES(0,'$resId','$amount',NOW(),'$us_id',NOW(),'$note','$ip','0','$balance','$update_balance')"; 
                                          
                                    $resultin = mysqli_query($connect, $queryin);    
                                    if(!$resultin)
                                    {
                                                
                                    die("Query Failed2!" . mysqli_error($connect));
                                    }

                                    $notifica_msgre = "M. Bank Balance added to your account from " . $get_selfusername . " Amount: " . $amount . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($resId,$notifica_msgre,$notifica_from);                  
                                          
                                    Session::Flush('success', 'Successfully Balance Added!');
                                    Redirect::to('cs-profile.php?reseller_id='.$resId);
                                    
                        }
                  }
                  else 
                  {
                        echo "Invalid";
                  }


            }       

			
      }
  
     /* Customer Add Balance End */

  
      /**
       *      Update reseller balance
       *      @param $userId | null | int | reseller id
       *      @param $amount | null | payment or return amount
       */
      public static function upBanalnce($userId = '', $amount = '', $type = '')
      {
			$us_id = Session::get('us_id');
			$row   = DB::getInstance()->get("SELECT * from user where id='$userId' and created_by='$us_id'")->result();
			$row2   = DB::getInstance()->get("SELECT * from user where id='$us_id'")->result();
			
			if($type == "Transfer")
			{
				$add_balance2 = ($row[0]->balance2 + $amount);
				
				$return_balance2 = ($row2[0]->balance2 - $amount);				
                        
				$query = "UPDATE user SET balance2='$return_balance2' WHERE id='$us_id'";
                        global $connect; 
                        $result = mysqli_query($connect, $query);    
                        if(!$result){
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
                       				
				$updateData = "UPDATE user SET balance2='$add_balance2' WHERE id='$userId' AND created_by='$us_id'";
			}
			elseif($type == "TransferMb")
			{
				$add_balancemb = ($row[0]->bkash_balance + $amount);
				
				$return_balancemb = ($row2[0]->bkash_balance - $amount);
				
				$query = "UPDATE user SET bkash_balance='$return_balancemb' WHERE id='$us_id'";
                        
                        global $connect; 
                        $result = mysqli_query($connect, $query);    
                        if(!$result){
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
				
				$updateData = "UPDATE user SET bkash_balance='$add_balancemb' WHERE id='$userId' AND created_by='$us_id'";
			}
			else{
				echo "Invalid";
                  }
			
						
            $up = DB::getInstance()->get($updateData);
            return $up ? true : false;
      }

      /** Add Online Fund Request Start */
      public static function testService(){

            $user_id         = Session::get('us_id');
            $balance_type    = Input::get('payment_method');
            $amount          = Input::get('amount');
            $service         = 'Test Service';
            $amount          = (int)$amount;

            $invoiceNumber = rand(0, 99999999);
            // Pad the number with leading zeros to ensure it is 8 digits long
            $reference = str_pad($invoiceNumber, 8, '0', STR_PAD_LEFT);
            $invoiceNumber = "C".$user_id."T".time()."INV".$reference;

            /* Function for prevent of sql injection */
         
            global $connect;
            $amount          = stripcslashes($amount); 
            $amount          = mysqli_real_escape_string($connect, $amount);
                  
            /* End of Function for prevent of sql injection */


            /*Check Duplicate Transaction ID Start*/

            $query1  = "SELECT * FROM testservice_request where invoice_no='$invoiceNumber'";    
    
            $result1 = mysqli_query($connect, $query1); 
            if(!$result1)
            {
                die("Query Failed!" . mysqli_error($connect));      
            } 
           
            $count1 = mysqli_num_rows($result1); 
        
            /*Check Duplicate Transaction ID End */


            if ($count1 >= 1) 
            {               
                  Session::Flush('error', 'You have already submitted with the same Transaction ID.');
                  Redirect::to('send_add_balance.php'); 
            }
            elseif (empty($amount)) 
            {               
                  Session::Flush('error', 'You must enter amount!');
                  Redirect::to('send_add_balance.php'); 
            }
            elseif(!is_numeric($amount)) 
            {                
                Session::Flush('error', 'Amount allow only number!');
                Redirect::to('send_add_balance.php');
            } 
            elseif (strlen($amount) > 7)
            {
                  Session::Flush('error', 'Amount needs to have less than 7 digit!');
                  Redirect::to('send_add_balance.php'); 
            }
            else 
            {

                  $query  = "INSERT INTO testservice_request (user_id,service,wallet_type,amount,status,invoice_no)";
                  $query .= "VALUES('$user_id','$service','$balance_type','$amount','0','$invoiceNumber')";
      
                  $result = mysqli_query($connect, $query);    
                  if(!$result){
                      
                      die("Query Failed!" . mysqli_error($connect));
                  }

                  // Get the inserted ID
                  $insertedId = mysqli_insert_id($connect);

                  if($balance_type=="Wallet"){
                        // Query to update the balance
                        $query = "UPDATE user SET balance2 = balance2 - $amount WHERE id = $user_id";

                        // Execute the query
                        $success = mysqli_query($connect, $query);

                        if ($success) {
                              $queryRequest = "UPDATE testservice_request SET status = 1 WHERE id = $insertedId";
                              $update = mysqli_query($connect, $queryRequest);
                              Session::Flush('success', 'Your request successfullly submitted. Thank You!');
                              Redirect::to('test_service.php');  
                        } else {
                              Session::Flush('success', "Failed to add balance to the customer's wallet.");
                              Redirect::to('test_service.php');  
                        }

                  }else{
      
                        // Calling API
                        $url = "https://sandbox.paystation.com.bd/initiate-payment";
                        $postData = [
                              'invoice_number' => $invoiceNumber,
                              'currency' => 'BDT',
                              'payment_amount' => $amount,
                              'reference' => $reference,
                              'cust_name' => 'Test',
                              'cust_phone' => '01700000001',
                              'cust_email' => 'test100@gmail.com',
                              'cust_address' => 'Address',
                              'callback_url' => 'https://sandbox.paystation.com.bd/payment-success/104',
                              'checkout_items' => 'Sona-Rupa',
                              'merchantId' => '104-1653730183',
                              'password' => 'gamecoderstorepass',
                              'opt_a' => '1',
                              'opt_b' => '2',
                              'opt_c' => '3'
                        ];

                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                              CURLOPT_RETURNTRANSFER => 1,
                              CURLOPT_URL => $url,
                              CURLOPT_USERAGENT => 'Internal Request',
                              CURLOPT_SSL_VERIFYPEER => false,
                              CURLOPT_POST => 1,
                              CURLOPT_POSTFIELDS => $postData
                        ));
                        $httpcode = curl_getinfo($curl);
                        $resp = curl_exec($curl);
                        curl_close($curl);
                        $response = json_decode($resp);

                        // Check if the status code is 200
                        if ($response->status_code == "200") {
                              // Extract the payment amount from the response
                              $paymentAmount = $response->payment_amount;

                              // Assuming you have a function to add balance to the customer's wallet
                              // This is a placeholder for the actual function you would call
                              $customerId = $user_id; // Example customer ID
                              // $success = addBalanceToWallet($customerId, $paymentAmount);

                              $queryRequest = "UPDATE testservice_request SET status = 1 WHERE id = $insertedId";
                              $update = mysqli_query($connect, $queryRequest);
                              Session::Flush('success', 'Your request successfullly submitted. Thank You!');
                              Redirect::to('test_service.php');  

                        } else {
                              Session::Flush('success', 'Payment was not successful. No balance added.');
                              Redirect::to('test_service.php');  
                        }

                  }

            }


      }

      /** Add Online Fund Request Start End */

}
