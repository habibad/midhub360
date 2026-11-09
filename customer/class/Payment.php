<?php

class Payment
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



      /** Add Fund Request Start */
      public static function addFund(){

            $user_id         = Session::get('us_id');
            $balance_type    = Input::get('bltype');
            $amount          = Input::get('amount');
            $mcategory       = Input::get('mcategory');
            $mtype           = Input::get('mtype');
            $account_number  = Input::get('acnumber');
            $transaction_id  = Input::get('transid');
            $sending_number  = Input::get('sendnumber'); 
            $amount          = (int)$amount;

            /* Function for prevent of sql injection */
         
            global $connect;
            $amount          = stripcslashes($amount);
            $account_number  = stripcslashes($account_number);
            $transaction_id  = stripcslashes($transaction_id);
            $sending_number  = stripcslashes($sending_number);           
            
            $amount            = mysqli_real_escape_string($connect, $amount);
            $account_number    = mysqli_real_escape_string($connect, $account_number);
            $transaction_id    = mysqli_real_escape_string($connect, $transaction_id);
            $sending_number    = mysqli_real_escape_string($connect, $sending_number);                     
    
        /* End of Function for prevent of sql injection */

            
            $query= "SELECT user, level, created_by FROM user where id='$user_id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                  die("Query Failed!" . mysqli_error($connect));      
            } 

            $reseller_data= mysqli_fetch_object($result);
            $created_by =$reseller_data->created_by;
            $username =$reseller_data->user;
            $user_level =$reseller_data->level;


            /*Check Duplicate Transaction ID Start*/

            $query1  = "SELECT * FROM add_fund where transaction_id='$transaction_id'";    
    
            $result1 = mysqli_query($connect, $query1); 
            if(!$result1)
            {
                die("Query Failed!" . mysqli_error($connect));      
            } 
           
            $count1 = mysqli_num_rows($result1);  // If result matched $user and $pass, table row must be 1 row
        
            /*Check Duplicate Transaction ID End */


            if (empty($transaction_id)) 
            {               
                  Session::Flush('error', 'You must enter Transaction ID');
                  Redirect::to('send_add_balance.php'); 
            }
            elseif ($count1 >= 1) 
            {               
                  Session::Flush('error', 'You have already submitted with the same Transaction ID.');
                  Redirect::to('send_add_balance.php'); 
            }
            elseif (strlen($transaction_id) > 20)
            {
                  Session::Flush('error', 'Transaction ID needs to have less than 20 characters!');
                  Redirect::to('send_add_balance.php'); 
            }
            elseif (!preg_match("/^([A-Za-z0-9])+$/", $transaction_id))           
            {           
                  Session::Flush('error', 'Transaction ID only allow letter and number! Space not allowed!');
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
            elseif (empty($account_number)) 
            {               
                  Session::Flush('error', 'You must enter Payment Number!');
                  Redirect::to('send_add_balance.php'); 
            }
            elseif(!is_numeric($account_number)) 
            {                
                Session::Flush('error', 'Payment Number allow only number!');
                Redirect::to('send_add_balance.php');
            } 
            elseif (strlen($account_number) > 12)
            {
                  Session::Flush('error', 'Payment Number needs to have less than 13 digit!');
                  Redirect::to('send_add_balance.php'); 
            }
            elseif (empty($sending_number)) 
            {               
                  Session::Flush('error', 'You must enter Sending Number!');
                  Redirect::to('send_add_balance.php'); 
            }
            elseif(!is_numeric($sending_number)) 
            {                
                Session::Flush('error', 'Sending Number allow only number!');
                Redirect::to('send_add_balance.php');
            } 
            elseif (strlen($sending_number) > 12)
            {
                  Session::Flush('error', 'Sending Number needs to have less than 13 digit!');
                  Redirect::to('send_add_balance.php'); 
            }
            else 
            {

                  $query  = "INSERT INTO add_fund (id,user_id,username,level,boss_id,balance_type,category,mcategory,type,amount,account_name,account_number,transaction_id,sending_number,date,status,comment,document)";
                  $query .= "VALUES(0,'$user_id','$username','$user_level','$created_by','$balance_type','Mobile Banking','$mcategory','$mtype','$amount','0','$account_number','$transaction_id','$sending_number',NOW(),'0', '0','0')"; //Used concatenation (.) add two string
      
                  $result = mysqli_query($connect, $query);    
                  if(!$result){
                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  
                      Session::Flush('success', 'Your request successfullly submitted. Thank You!');
                      Redirect::to('send_add_balance.php');  

            }


      }

      /** Add Fund Request Start End */


      /** Add Online Fund Request Start */
      public static function addFundThroughOnlineBackUp(){

            $user_id         = Session::get('us_id');
            $balance_type    = Input::get('bltype');
            $amount          = Input::get('amount');
            $mcategory       = Input::get('mcategory');
            $amount          = (int)$amount;
            $mtype           = "Online";
            $account_number  = "Blank";
            $transaction_id  = "Blank";
            $sending_number  = "Blank";

            $invoiceNumber = rand(0, 99999999);
            // Pad the number with leading zeros to ensure it is 8 digits long
            $reference = str_pad($invoiceNumber, 8, '0', STR_PAD_LEFT);
            $invoiceNumber = "C".$user_id."T".time()."INV".$reference;

            /* Function for prevent of sql injection */
         
            global $connect;
            $amount          = stripcslashes($amount); 
            $amount          = mysqli_real_escape_string($connect, $amount);
                  
            /* End of Function for prevent of sql injection */

            if ( $balance_type == "recharge" ) {
                  $balance_type = "Recharge";
            }

            if ( $balance_type == "mobile_banking" ) {
                  $balance_type = "Mobile Banking";
            }

            $query= "SELECT user, level, created_by FROM user where id='$user_id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                  die("Query Failed!" . mysqli_error($connect));      
            } 

            $reseller_data= mysqli_fetch_object($result);
            $created_by =$reseller_data->created_by;
            $username =$reseller_data->user;
            $user_level =$reseller_data->level;


            /*Check Duplicate Transaction ID Start*/

            $query1  = "SELECT * FROM add_fund where invoice_no='$invoiceNumber'";    
    
            $result1 = mysqli_query($connect, $query1); 
            if(!$result1)
            {
                die("Query Failed!" . mysqli_error($connect));      
            } 
           
            $count1 = mysqli_num_rows($result1);  // If result matched $user and $pass, table row must be 1 row
        
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

                  $query  = "INSERT INTO add_fund (id,user_id,username,level,boss_id,balance_type,category,mcategory,type,amount,account_name,account_number,transaction_id,sending_number,date,status,comment,document,invoice_no)";
                  $query .= "VALUES(0,'$user_id','$username','$user_level','$created_by','$balance_type','Mobile Banking','$mcategory','$mtype','$amount','0','$account_number','$transaction_id','$sending_number',NOW(),'0', '0','0','$invoiceNumber')"; //Used concatenation (.) add two string
      
                  $result = mysqli_query($connect, $query);    
                  if(!$result){
                      
                      die("Query Failed!" . mysqli_error($connect));
                  }

                  // Get the inserted ID
                  $insertedId = mysqli_insert_id($connect);

                  $callbackURL = "https://www.midhub360.com/";

                  // Calling API
                  $url = "https://sandbox.paystation.com.bd/initiate-payment";
                  $postData = [
                        'invoice_number' => $invoiceNumber,
                        'currency' => 'BDT',
                        'payment_amount' => $amount,
                        'reference' => $reference,
                        'cust_name' => 'Test',
                        'cust_phone' => $account_number,
                        'cust_email' => 'test100@gmail.com',
                        'cust_address' => 'Address',
                        'callback_url' => 'https://sandbox.paystation.com.bd/payment-success/104',
                        'checkout_items' => 'Add_Balance',
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

                        if($balance_type=="Recharge"){
                              // Query to update the balance
                              $query = "UPDATE user SET balance2 = balance2 + $amount WHERE id = $customerId";
                        }else{
                              // Query to update the balance
                              $query = "UPDATE user SET bkash_balance = bkash_balance + $amount WHERE id = $customerId";
                        }

                        // Execute the query
                        $success = mysqli_query($connect, $query);

                        if ($success) {
                              $queryRequest = "UPDATE add_fund SET status = 1 WHERE id = $insertedId";
                              $update = mysqli_query($connect, $queryRequest);
                              Session::Flush('success', 'Your request successfullly submitted. Thank You!');
                              Redirect::to('send_add_balance.php');  
                        } else {
                              Session::Flush('success', "Failed to add balance to the customer's wallet.");
                              Redirect::to('send_add_balance.php');  
                        }
                  } else {
                        echo "Payment was not successful. No balance added.";
                        Session::Flush('success', 'Payment was not successful. No balance added.');
                        Redirect::to('send_add_balance.php');  
                  }

                  Session::Flush('success', 'Your request successfullly submitted. Thank You!');
                  Redirect::to('send_add_balance.php');  

            }


      }
      /** Add Online Fund Request Start End */

      /** Add Online Fund Request Start */
      public static function addFundThroughOnline(){

            $user_id         = Session::get('us_id');
            $balance_type    = Input::get('bltype');
            $amount          = Input::get('amount');
            $mcategory       = Input::get('mcategory');
            $amount          = (int)$amount;
            $mtype           = "Online";
            $account_number  = "Blank";
            $transaction_id  = "Blank";
            $sending_number  = "Blank";

            $invoiceNumber = rand(0, 99999999);
            // Pad the number with leading zeros to ensure it is 8 digits long
            $reference = str_pad($invoiceNumber, 8, '0', STR_PAD_LEFT);
            $invoiceNumber = "C".$user_id."T".time()."INV".$reference;

            $transaction_id = $invoiceNumber;

            /* Function for prevent of sql injection */
         
            global $connect;
            $amount          = stripcslashes($amount); 
            $amount          = mysqli_real_escape_string($connect, $amount);
                  
            /* End of Function for prevent of sql injection */

            if ( $balance_type == "recharge" ) {
                  $balance_type = "Recharge";
            }

            if ( $balance_type == "mobile_banking" ) {
                  $balance_type = "Mobile Banking";
            }

            $query= "SELECT user, level, created_by FROM user where id='$user_id'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
                  die("Query Failed!" . mysqli_error($connect));      
            } 

            $reseller_data= mysqli_fetch_object($result);
            $created_by =$reseller_data->created_by;
            $username =$reseller_data->user;
            $user_level =$reseller_data->level;


            /*Check Duplicate Transaction ID Start*/

            $query1  = "SELECT * FROM add_fund where invoice_no='$invoiceNumber'";    
    
            $result1 = mysqli_query($connect, $query1); 
            if(!$result1)
            {
                die("Query Failed!" . mysqli_error($connect));      
            } 
           
            $count1 = mysqli_num_rows($result1);  // If result matched $user and $pass, table row must be 1 row
        
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

                  $query  = "INSERT INTO add_fund (id,user_id,username,level,boss_id,balance_type,category,mcategory,type,amount,account_name,account_number,transaction_id,sending_number,date,status,comment,document,invoice_no)";
                  $query .= "VALUES(0,'$user_id','$username','$user_level','$created_by','$balance_type','Mobile Banking','$mcategory','$mtype','$amount','0','$account_number','$transaction_id','$sending_number',NOW(),'0', '0','0','$invoiceNumber')"; //Used concatenation (.) add two string
      
                  $result = mysqli_query($connect, $query);    
                  if ( !$result ) {
                      
                      die("Query Failed!" . mysqli_error($connect));

                  } else {
      
                        $callbackURL = "https://www.midhub360.com/customer/callback.php";

                        // Calling API
                        $url = "https://sandbox.paystation.com.bd/initiate-payment";
                        $postData = [
                              'invoice_number' => $invoiceNumber,
                              'currency' => 'BDT',
                              'payment_amount' => $amount,
                              'reference' => $reference,
                              'cust_name' => 'Test',
                              'cust_phone' => $account_number,
                              'cust_email' => 'test100@gmail.com',
                              'cust_address' => 'Address',
                              'callback_url' => $callbackURL,
                              'checkout_items' => $balance_type,
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
                        if (isset($response->status_code) && $response->status_code == "200") {
                              if (!empty($response->payment_url)) {
                                    // Redirect to payment URL
                                    header("Location: " . $response->payment_url);
                                    exit;
                              } else {
                                    Session::flush('error', 'Payment URL not found in the response.');
                                    Redirect::to('send_add_balance.php');
                              }
                        } else {
                              Session::flush('error', 'Payment was not successful. No balance added.');
                              Redirect::to('send_add_balance.php');  
                        }

                  }

            }


      }
      /** Add Online Fund Request Start End */


      /** collection payment history */
      public static function collection()
      {
            $userid = Session::get('userId');
            $collection = Input::get('collection');
            $resel      = Input::get('resel');
            $type       = Input::get('type');
            $from1      = Input::get('from1');
            $to1        = Input::get('to1');
			
            if ($resel != '' && $type != '' && $from1 != '' && $to1 != '') {
                  $sql = "SELECT * FROM pay_receive WHERE sender='$userid' AND userid = '$resel' AND type ='$type' AND idate between '$from1' AND '$to1' ORDER BY id DESC ";
            } elseif ($resel != '' && $type != '') {
                  $sql = "SELECT * FROM pay_receive WHERE sender='$userid' AND userid = '$resel' AND type = '$type' ORDER BY id DESC ";
            } elseif ($resel != '' && $from1 != '' && $to1 != '') {
                  $sql = "SELECT * FROM pay_receive WHERE sender='$userid' AND userid = '$resel'  AND idate between '$from1' AND '$to1' ORDER BY id DESC ";
            } elseif ($from1 != '' && $to1 != '') {
                  $sql = "SELECT * FROM pay_receive WHERE sender='$userid' AND idate between '$from1' AND '$to1' ORDER BY id DESC ";
            } elseif ($resel != '') {
                  $sql = "SELECT * FROM pay_receive WHERE sender='$userid' AND userid = '$resel' ORDER BY id DESC ";
            } else {
                  if ($collection == 'all') {
                        $sql = "SELECT * FROM pay_receive WHERE sender = '$userid' ORDER BY id DESC ";
                  } else {
                        $sql = "SELECT * FROM pay_receive WHERE sender='$userid' AND userid = '$collection' ORDER BY id DESC ";
                  }
            }
            return $sql;
      }
	  
	  public static function collection_user($userid)
      {
            $sql = "SELECT * FROM pay_receive WHERE sender = '$userid' ORDER BY id DESC ";
            return $sql;
      }

      public static function total_user($userid)
      {
            return DB::getInstance()->get(self::collection_user($userid))->Count();
      }
	  
	  public static function collection_userid($userid)
      {
            $sql = "SELECT * FROM pay_receive WHERE userid = '$userid' ORDER BY id DESC ";
            return $sql;
      }

      public static function total_userid($userid)
      {
            return DB::getInstance()->get(self::collection_user($userid))->Count();
      }
	  
	  public static function total()
      {
            return DB::getInstance()->get(self::collection())->Count();
      }
	  
	  
	  public static function payTotal_userid($current)
      {
            $sql   = "SELECT SUM(credit) as payTotal FROM pay_receive WHERE type = 'Transfer' AND userid ='$current'";
            $total = DB::getInstance()->get($sql)->result();
            foreach ($total as $to) {
                  return $to->payTotal;
            }
      }

      public static function returnTotal_userid($current)
      {
            $sql   = "SELECT SUM(debit) as payDebit FROM pay_receive WHERE type = 'Return' AND userid ='$current'";
            $total = DB::getInstance()->get($sql)->result();
            foreach ($total as $to) {
                  return $to->payDebit;
            }
      }

      public static function totalAmount_userid($current)
      {
            return self::payTotal_userid($current) + self::returnTotal_userid($current);
      }

      public static function payTotal()
      {
            $current= Session::get('userId');
            $sql   = "SELECT SUM(credit) as payTotal FROM pay_receive WHERE type = 'Transfer' AND sender ='$current'";
            $total = DB::getInstance()->get($sql)->result();
            foreach ($total as $to) {
                  return $to->payTotal;
            }
      }

      public static function returnTotal()
      {
            $current= Session::get('userId');
            $sql   = "SELECT SUM(debit) as payDebit FROM pay_receive WHERE type = 'Return' AND sender ='$current'";
            $total = DB::getInstance()->get($sql)->result();
            foreach ($total as $to) {
                  return $to->payDebit;
            }
      }

      public static function totalAmount()
      {
            return self::payTotal() + self::returnTotal();
      }

      /** Last Receive at last 7 data show in home */
      public static function lastReceive()
      {
            $userId = Session::get('userId');
            $sql = "SELECT * FROM pay_receive WHERE userid ='$userId' AND type ='Transfer' ORDER BY id DESC";
            return DB::getInstance()->get($sql)->result();
      }

}
