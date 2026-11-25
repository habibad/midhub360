<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
class History
{

      /**
       * Atomically move a request out of a pending state.
       * Without this guard, double-clicking Cancel refunded twice and approving an
       * add-fund request twice credited the wallet (and upline commission) twice.
       * Rows already in a final state (1 = success, 3 = cancelled) are left untouched.
       */
      public static function claim_transition($table, $id, $set, $redirect)
      {
            global $connect;
            $tables = array('send_query', 'drive_request', 'bkash_send', 'bank_request', 'billpay_request', 'add_fund');
            if (!in_array($table, $tables, true)) {
                  die('Invalid table');
            }
            $id  = (int) $id;
            $sql = "UPDATE `$table` SET $set WHERE id = $id AND status NOT IN ('1','3','9')"; // 9 = bill fetched but never paid (no money taken)
            $result = mysqli_query($connect, $sql);
            if (!$result) {
                  die("Query Failed!" . mysqli_error($connect));
            }
            if (mysqli_affected_rows($connect) !== 1) {
                  Session::Flush('error', 'This request was already processed. No changes were made.');
                  Redirect::to($redirect);
                  exit();
            }
      }

	
       /* Function for success recharge start */


	 public static function rechargesuccess()
      {
            $f_id      = Input::get('id');
            $number    = Input::get('number');
            $amount    = Input::get('amount');
            $txnid     = Input::get('txnid');
            $msg       = Input::get('msg');
            $user_id   = Input::get('ucid');
            $company   = Input::get('company');
            $st_type   = Input::get('sttype');
            
            
             /* Function for prevent of sql injection */            
         
             global $connect;
             $txnid  = stripcslashes($txnid);
             $msg    = stripcslashes($msg);                     
            
             $txnid  = mysqli_real_escape_string($connect, $txnid);
             $msg    = mysqli_real_escape_string($connect, $msg);
            
          
         /* End of Function for prevent of sql injection */



            if (!preg_match("/^([A-Za-z0-9])+$/", $txnid))          
            {                  
                  Session::Flush('error', 'Transaction ID Only allow letter and number! Space not allowed.');
                  Redirect::to('pending_recharge.php');
            }
            elseif (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
            {      
                  Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                  Redirect::to('pending_recharge.php');            
            }
            else
            {
                     
			      self::claim_transition('send_query', $f_id, 'status=\'1\'', 'waiting_recharge.php'); // only settles a request once


                        $query  = "INSERT INTO reportserver (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,date)";
                        $query .= "VALUES(0,'$company','$msg','$user_id','$amount','$txnid','$number',$f_id,NOW(),'1','0',CURDATE())"; 
 
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed2!" . mysqli_error($connect));
                        }

                        // 5-level upline commission for the now-successful recharge (exactly once)
                        \Midhub\Commission\CommissionService::payForRecharge((int) $f_id);

                        if($st_type == "waiting")
                        {
                              Session::Flush('success', 'Successfully Updated!');
                              Redirect::to('waiting_recharge.php');
                        }
                        else
                        {
                              Session::Flush('success', 'Successfully Updated!');
                              Redirect::to('pending_recharge.php');
                        }

             
                   
            }
                  
       
      }


      /* Function for success recharge end */


       /* Function for Resend recharge start */


	 public static function resendrecharge()
       {
             $f_id      = Input::get('id');
             $number    = Input::get('number');
             $amount    = Input::get('amount');
             $txnid     = Input::get('txnid');
             $msg       = Input::get('msg');
             $user_id   = Input::get('ucid');
             $company   = Input::get('company');
             
             
              /* Function for prevent of sql injection */            
          
              global $connect;
              $txnid  = stripcslashes($txnid);
              $msg    = stripcslashes($msg);                     
             
              $txnid  = mysqli_real_escape_string($connect, $txnid);
              $msg    = mysqli_real_escape_string($connect, $msg);
             
           
          /* End of Function for prevent of sql injection */
 
                 
                         self::claim_transition('send_query', $f_id, 'status=\'0\'', 'waiting_recharge.php'); // only settles a request once
 
 
                         Session::Flush('success', 'Successfully Resend!');
                         Redirect::to('waiting_recharge.php');
                                
        
       }
 
 
       /* Function for Resend recharge end */
 


       /* Function for cancel recharge start */
      
	 public static function rechargecancel()
       {
             $f_id      = Input::get('id');
             $number    = Input::get('number');
             $amount    = Input::get('amount');
             $txnid     = 'N/A';
             $msg       = Input::get('remsg');
             $user_id   = Input::get('ucid');
             $company   = Input::get('company');
             $st_type   = Input::get('sttype');
             
             
              /* Function for prevent of sql injection */            
          
              global $connect;
              $txnid  = stripcslashes($txnid);
              $msg    = stripcslashes($msg);                     
             
              $txnid  = mysqli_real_escape_string($connect, $txnid);
              $msg    = mysqli_real_escape_string($connect, $msg);
             
           
          /* End of Function for prevent of sql injection */
 
          
             if (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
             {      
                   Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                   Redirect::to('pending_recharge.php');            
             }
             else
             {
                      
                         self::claim_transition('send_query', $f_id, 'status=\'3\'', 'waiting_recharge.php'); // only settles a request once
 
 
                         $queryin  = "INSERT INTO reportserver (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,date)";
                         $queryin .= "VALUES(0,'$company','$msg','$user_id','$amount','$txnid','$number',$f_id,NOW(),'3','0',CURDATE())"; 
  
                         $resultin = mysqli_query($connect, $queryin);    
                         if(!$resultin)
                         {
                             
                             die("Query Failed2!" . mysqli_error($connect));
                         }

                         /*Update User Balance */

                        $querysl= "SELECT balance2 from user where id='$user_id'";                        
                        $resultsl = mysqli_query($connect, $querysl);

                        if(!$resultsl){
                        die("Query Failed!3" . mysqli_error($connect));      
                        } 

                        $reseller_data= mysqli_fetch_object($resultsl);
                        $balance =$reseller_data->balance2;

                        $update_balance = $balance + $amount;

                        $queryup = "UPDATE user SET balance2 = balance2 + '" . (float) $amount . "' WHERE id='" . (int) $user_id . "'"; // relative refund
                        
                         $resultup = mysqli_query($connect, $queryup);    
                         if(!$resultup)
                         {                      
                         die("Query Failed4!" . mysqli_error($connect));
                         }

                          /*Update User Balance End*/
                        
                          if($st_type == "waiting")
                          {
                                Session::Flush('success', 'Successfully Updated!');
                                Redirect::to('waiting_recharge.php');
                          }
                          else
                          {
                                Session::Flush('success', 'Successfully Updated!');
                                Redirect::to('pending_recharge.php');
                          }
                   
              
                    
             }
                   
        
       }
 
 
       /* Function for Cancel recharge end */


        /* Function for success drive start */


	 public static function drivesuccess()
       {
             $f_id      = Input::get('id');
             $number    = Input::get('number');
             $amount    = Input::get('amount');
             $txnid     = Input::get('txnid');
             $msg       = Input::get('msg');
             $user_id   = Input::get('ucid');
             $company   = Input::get('company');
             
             
              /* Function for prevent of sql injection */            
          
              global $connect;
              $txnid  = stripcslashes($txnid);
              $msg    = stripcslashes($msg);                     
             
              $txnid  = mysqli_real_escape_string($connect, $txnid);
              $msg    = mysqli_real_escape_string($connect, $msg);
             
           
          /* End of Function for prevent of sql injection */
 
 
 
             if (!preg_match("/^([A-Za-z0-9])+$/", $txnid))          
             {                  
                   Session::Flush('error', 'Transaction ID Only allow letter and number! Space not allowed.');
                   Redirect::to('pending_drive.php');
             }
             elseif (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
             {      
                   Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                   Redirect::to('pending_drive.php');            
             }
             else
             {
                      
                         self::claim_transition('drive_request', $f_id, 'status=\'1\'', 'pending_drive.php'); // only settles a request once
 
 
                         $query  = "INSERT INTO drive_msg (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,date)";
                         $query .= "VALUES(0,'$company','$msg','$user_id','$amount','$txnid','$number',$f_id,NOW(),'1','0',CURDATE())"; 
  
                         $result = mysqli_query($connect, $query);    
                         if(!$result)
                         {
                             
                             die("Query Failed2!" . mysqli_error($connect));
                         }
 
                         Session::Flush('success', 'Successfully Updated!');
                         Redirect::to('pending_drive.php');
                   
              
                    
             }
                   
        
       }
 
 
       /* Function for Success drive end */
 
       /* Function for Cancel drive start */
       
        public static function drivecancel()
        {
              $f_id      = Input::get('id');
              $number    = Input::get('number');
              $amount    = Input::get('amount');
              $txnid     = 'N/A';
              $msg       = Input::get('remsg');
              $user_id   = Input::get('ucid');
              $company   = Input::get('company');
              $cost      = Input::get('cost');
              
              
               /* Function for prevent of sql injection */            
           
               global $connect;
               $txnid  = stripcslashes($txnid);
               $msg    = stripcslashes($msg);                     
              
               $txnid  = mysqli_real_escape_string($connect, $txnid);
               $msg    = mysqli_real_escape_string($connect, $msg);
              
            
           /* End of Function for prevent of sql injection */
  
           
              if (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
              {      
                    Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                    Redirect::to('pending_drive.php');            
              }
              else
              {
                       
                          self::claim_transition('drive_request', $f_id, 'status=\'3\'', 'pending_drive.php'); // only settles a request once
  
  
                          $queryin  = "INSERT INTO drive_msg (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,date)";
                          $queryin .= "VALUES(0,'$company','$msg','$user_id','$amount','$txnid','$number',$f_id,NOW(),'3','0',CURDATE())"; 
   
                          $resultin = mysqli_query($connect, $queryin);    
                          if(!$resultin)
                          {
                              
                              die("Query Failed2!" . mysqli_error($connect));
                          }
 
                          /*Update User Balance */
 
                         $querysl= "SELECT balance2 from user where id='$user_id'";                        
                         $resultsl = mysqli_query($connect, $querysl);
 
                         if(!$resultsl){
                         die("Query Failed!3" . mysqli_error($connect));      
                         } 
 
                         $reseller_data= mysqli_fetch_object($resultsl);
                         $balance =$reseller_data->balance2;
 
                         $update_balance = $balance + $cost;
 
                         $queryup = "UPDATE user SET balance2 = balance2 + '" . (float) $cost . "' WHERE id='" . (int) $user_id . "'"; // relative refund
                         
                          $resultup = mysqli_query($connect, $queryup);    
                          if(!$resultup)
                          {                      
                          die("Query Failed4!" . mysqli_error($connect));
                          }
 
                           /*Update User Balance End*/
                         
                          Session::Flush('success', 'Successfully Updated!');
                          Redirect::to('pending_drive.php');
                    
               
                     
              }
                    
         
        }
  
  
        /* Function for cancel drive end */




      /* Function for success Mbank start */


	 public static function mabnksuccess()
       {
             $f_id      = Input::get('id');
             $number    = Input::get('number');
             $amount    = Input::get('amount');
             $txnid     = Input::get('txnid');
             $msg       = Input::get('msg');
             $user_id   = Input::get('ucid');
             $company   = Input::get('company');
             
             
             
              /* Function for prevent of sql injection */            
          
              global $connect;
              $txnid  = stripcslashes($txnid);
              $msg    = stripcslashes($msg);                     
             
              $txnid  = mysqli_real_escape_string($connect, $txnid);
              $msg    = mysqli_real_escape_string($connect, $msg);
             
           
          /* End of Function for prevent of sql injection */
 
 
 
             if (!preg_match("/^([A-Za-z0-9])+$/", $txnid))          
             {                  
                   Session::Flush('error', 'Transaction ID Only allow letter and number! Space not allowed.');
                   Redirect::to('pending_mbank.php');
             }
             elseif (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
             {      
                   Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                   Redirect::to('pending_mbank.php');            
             }
             else
             {
                      
                         self::claim_transition('bkash_send', $f_id, 'status=\'1\'', 'pending_mbank.php'); // only settles a request once
 
 
                         $query  = "INSERT INTO bkash_report (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,sending_number,date)";
                         $query .= "VALUES(0,'$company','handrefill','$user_id','$amount','$txnid','$number',$f_id,NOW(),'1','0','$msg',CURDATE())"; 
  
                         $result = mysqli_query($connect, $query);    
                         if(!$result)
                         {
                             
                             die("Query Failed2!" . mysqli_error($connect));
                         }
 
                         Session::Flush('success', 'Successfully Updated!');
                         Redirect::to('pending_mbank.php');
                   
              
                    
             }
                   
        
       }
 
 
       /* Function for Success Mbank end */
 
       /* Function for Cancel Mbank start */
       
        public static function mbankcancel()
        {
              $f_id      = Input::get('id');
              $number    = Input::get('number');
              $amount    = Input::get('amount');
              $txnid     = 'N/A';
              $msg       = Input::get('remsg');
              $user_id   = Input::get('ucid');
              $company   = Input::get('company');
              $cost      = Input::get('cost');
              
              
               /* Function for prevent of sql injection */            
           
               global $connect;
               $txnid  = stripcslashes($txnid);
               $msg    = stripcslashes($msg);                     
              
               $txnid  = mysqli_real_escape_string($connect, $txnid);
               $msg    = mysqli_real_escape_string($connect, $msg);
              
            
           /* End of Function for prevent of sql injection */
  
           
              if (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
              {      
                    Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                    Redirect::to('pending_mbank.php');            
              }
              else
              {
                       
                          self::claim_transition('bkash_send', $f_id, 'status=\'3\'', 'pending_mbank.php'); // only settles a request once
  
  
                         $queryin  = "INSERT INTO bkash_report (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,sending_number,date)";
                         $queryin .= "VALUES(0,'$company','$msg','$user_id','$amount','N/A','$number',$f_id,NOW(),'1','3','N/A',CURDATE())"; 
  
                          $resultin = mysqli_query($connect, $queryin);    
                          if(!$resultin)
                          {
                              
                              die("Query Failed2!" . mysqli_error($connect));
                          }
 
                          /*Update User Balance */
 
                         $querysl= "SELECT bkash_balance from user where id='$user_id'";                        
                         $resultsl = mysqli_query($connect, $querysl);
 
                         if(!$resultsl){
                         die("Query Failed!3" . mysqli_error($connect));      
                         } 
 
                         $reseller_data= mysqli_fetch_object($resultsl);
                         $balance =$reseller_data->bkash_balance;
 
                         $update_balance = $balance + $cost;
 
                         $queryup = "UPDATE user SET bkash_balance = bkash_balance + '" . (float) $cost . "' WHERE id='" . (int) $user_id . "'"; // relative refund
                         
                          $resultup = mysqli_query($connect, $queryup);    
                          if(!$resultup)
                          {                      
                          die("Query Failed4!" . mysqli_error($connect));
                          }
 
                           /*Update User Balance End*/
                         
                          Session::Flush('success', 'Successfully Updated!');
                          Redirect::to('pending_mbank.php');
                    
               
                     
              }
                    
         
        }
  
  
        /* Function for cancel MBnak end */

      /* Function for success Banking start */


	 public static function bankingsuccess()
       {
             $f_id      = Input::get('id');
             $number    = Input::get('number');
             $amount    = Input::get('amount');
             $txnid     = Input::get('txnid');
             //$msg       = Input::get('msg');
             $user_id   = Input::get('ucid');
             $company   = Input::get('company');
             
           
             
              /* Function for prevent of sql injection */            
          
              global $connect;
              $txnid  = stripcslashes($txnid);
              //$msg    = stripcslashes($msg);                     
             
              $txnid  = mysqli_real_escape_string($connect, $txnid);
              //$msg    = mysqli_real_escape_string($connect, $msg);
             
           
          /* End of Function for prevent of sql injection */
 
 
 
             if (!preg_match("/^([A-Za-z0-9])+$/", $txnid))          
             {                  
                   Session::Flush('error', 'Transaction ID Only allow letter and number! Space not allowed.');
                   Redirect::to('pending_banking.php');
             }             
             else
             {
                      
                         self::claim_transition('bank_request', $f_id, 'status=\'1\'', 'pending_banking.php'); // only settles a request once
 
 
                         $query  = "INSERT INTO bank_msg (id,bank_name,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,sending_number,date)";
                         $query .= "VALUES(0,'$company','handrefill','$user_id','$amount','$txnid','$number',$f_id,NOW(),'1','0','0',CURDATE())"; 
  
                         $result = mysqli_query($connect, $query);    
                         if(!$result)
                         {
                             
                             die("Query Failed2!" . mysqli_error($connect));
                         }
 
                         Session::Flush('success', 'Successfully Updated!');
                         Redirect::to('pending_banking.php');
                   
              
                    
             }
                   
        
       }
 
 
       /* Function for Success Banking end */
 
       /* Function for Cancel Banking start */
       
        public static function bankingcancel()
        {
              $f_id      = Input::get('id');
              $number    = Input::get('number');
              $amount    = Input::get('amount');
              $txnid     = 'N/A';
              $msg       = Input::get('remsg');
              $user_id   = Input::get('ucid');
              $company   = Input::get('company');
              $cost      = Input::get('cost');
              
              
               /* Function for prevent of sql injection */            
           
               global $connect;
               $txnid  = stripcslashes($txnid);
               $msg    = stripcslashes($msg);                     
              
               $txnid  = mysqli_real_escape_string($connect, $txnid);
               $msg    = mysqli_real_escape_string($connect, $msg);
              
            
           /* End of Function for prevent of sql injection */
  
           
              if (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
              {      
                    Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                    Redirect::to('pending_banking.php');            
              }
              else
              {
                       
                          self::claim_transition('bank_request', $f_id, 'status=\'3\'', 'pending_banking.php'); // only settles a request once
  
  
                         $queryin  = "INSERT INTO bank_msg (id,bank_name,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,sending_number,date)";
                         $queryin .= "VALUES(0,'$company','$msg','$user_id','$amount','N/A','$number',$f_id,NOW(),'1','3','N/A',CURDATE())"; 
  
                          $resultin = mysqli_query($connect, $queryin);    
                          if(!$resultin)
                          {
                              
                              die("Query Failed2!" . mysqli_error($connect));
                          }
 
                          /*Update User Balance */
 
                         $querysl= "SELECT bkash_balance from user where id='$user_id'";                        
                         $resultsl = mysqli_query($connect, $querysl);
 
                         if(!$resultsl){
                         die("Query Failed!3" . mysqli_error($connect));      
                         } 
 
                         $reseller_data= mysqli_fetch_object($resultsl);
                         $balance =$reseller_data->bkash_balance;
 
                         $update_balance = $balance + $amount;
 
                         $queryup = "UPDATE user SET bkash_balance = bkash_balance + '" . (float) $amount . "' WHERE id='" . (int) $user_id . "'"; // relative refund
                         
                          $resultup = mysqli_query($connect, $queryup);    
                          if(!$resultup)
                          {                      
                          die("Query Failed4!" . mysqli_error($connect));
                          }
 
                           /*Update User Balance End*/
                         
                          Session::Flush('success', 'Successfully Updated!');
                          Redirect::to('pending_banking.php');
                    
               
                     
              }
                    
         
        }
  
  
        /* Function for cancel Banking end */


        
      /* Function for success BillPay start */


	 public static function billpaysuccess()
       {
             $f_id      = Input::get('id');
             $number    = Input::get('number');
             $amount    = Input::get('amount');
             $txnid     = Input::get('txnid');
             $msg       = Input::get('msg');
             $user_id   = Input::get('ucid');
             $company   = Input::get('company');
             
             
             
              /* Function for prevent of sql injection */            
          
              global $connect;
              $txnid  = stripcslashes($txnid);
              $msg    = stripcslashes($msg);                     
             
              $txnid  = mysqli_real_escape_string($connect, $txnid);
              $msg    = mysqli_real_escape_string($connect, $msg);
             
           
          /* End of Function for prevent of sql injection */
 
 
 
             if (!preg_match("/^([A-Za-z0-9])+$/", $txnid))          
             {                  
                   Session::Flush('error', 'Transaction ID Only allow letter and number! Space not allowed.');
                   Redirect::to('pending_mbank.php');
             }
             elseif (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
             {      
                   Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                   Redirect::to('pending_billpay.php');            
             }
             else
             {
                      
                         self::claim_transition('billpay_request', $f_id, 'status=\'1\'', 'pending_billpay.php'); // only settles a request once
 
 
                         $query  = "INSERT INTO billpay_msg (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,date)";
                         $query .= "VALUES(0,'$company','$msg','$user_id','$amount','$txnid','$number',$f_id,NOW(),'1','0',CURDATE())"; 
  
                         $result = mysqli_query($connect, $query);    
                         if(!$result)
                         {
                             
                             die("Query Failed2!" . mysqli_error($connect));
                         }
 
                         Session::Flush('success', 'Successfully Updated!');
                         Redirect::to('pending_billpay.php');
                   
              
                    
             }
                   
        
       }
 
 
       /* Function for Success Billpay end */
 
       /* Function for Cancel Mbank start */
       
        public static function billpaycancel()
        {
              $f_id      = Input::get('id');
              $number    = Input::get('number');
              $amount    = Input::get('amount');
              $txnid     = 'N/A';
              $msg       = Input::get('remsg');
              $user_id   = Input::get('ucid');
              $company   = Input::get('company');
              $cost      = Input::get('cost');
              
              
               /* Function for prevent of sql injection */            
           
               global $connect;
               $txnid  = stripcslashes($txnid);
               $msg    = stripcslashes($msg);                     
              
               $txnid  = mysqli_real_escape_string($connect, $txnid);
               $msg    = mysqli_real_escape_string($connect, $msg);
              
            
           /* End of Function for prevent of sql injection */
  
           
              if (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
              {      
                    Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                    Redirect::to('pending_billpay.php');            
              }
              else
              {
                       
                          self::claim_transition('billpay_request', $f_id, 'status=\'3\'', 'pending_billpay.php'); // only settles a request once
  
  
                         $queryin  = "INSERT INTO billpay_msg (id,company,msgbody,ucid,amt,transactionid,number,f_id,datetime,status,balance,date)";
                         $queryin .= "VALUES(0,'$company','$msg','$user_id','$amount','N/A','$number',$f_id,NOW(),'1','3',CURDATE())"; 
  
                          $resultin = mysqli_query($connect, $queryin);    
                          if(!$resultin)
                          {
                              
                              die("Query Failed2!" . mysqli_error($connect));
                          }
 
                          /*Update User Balance */
 
                         $querysl= "SELECT bkash_balance from user where id='$user_id'";                        
                         $resultsl = mysqli_query($connect, $querysl);
 
                         if(!$resultsl){
                         die("Query Failed!3" . mysqli_error($connect));      
                         } 
 
                         $reseller_data= mysqli_fetch_object($resultsl);
                         $balance =$reseller_data->bkash_balance;
 
                         $update_balance = $balance + $cost;
 
                         $queryup = "UPDATE user SET bkash_balance = bkash_balance + '" . (float) $cost . "' WHERE id='" . (int) $user_id . "'"; // relative refund
                         
                          $resultup = mysqli_query($connect, $queryup);    
                          if(!$resultup)
                          {                      
                          die("Query Failed4!" . mysqli_error($connect));
                          }
 
                           /*Update User Balance End*/
                         
                          Session::Flush('success', 'Successfully Updated!');
                          Redirect::to('pending_billpay.php');
                    
               
                     
              }
                    
         
        }
  
  
        /* Function for cancel Billpay end */



        /* Function for success Balance Add resquest start */


	 public static function baladduccess()
       {
             $id           = Input::get('id');
             $username     = Input::get('username');
             $amount       = Input::get('amount');
             $commission_m = Input::get('commission');             
             $user_id      = Input::get('ucid');            
             //$withcomm   = $amount + $commission;
             $userId       = Session::get('us_id');

            /*Find fund request Data*/
             $queryfund= "SELECT * from add_fund where id='$id'"; 
             
             global $connect;
             $resultfund = mysqli_query($connect, $queryfund);
 
             if(!$resultfund){
                die("Query Failed!3" . mysqli_error($connect));      
             } 
 
             $reseller_data= mysqli_fetch_object($resultfund);
             $balance_type =$reseller_data->balance_type;  
             $sending_number =$reseller_data->sending_number;  
             $transaction_id =$reseller_data->transaction_id; 
             $account_number =$reseller_data->account_number;  

             $note = "Send Number: " . $sending_number . " TXN: " . $transaction_id .  " Ac No: " . $account_number;
             $ip = $_SERVER["REMOTE_ADDR"]; 

              /*Find fund request Data end*/
             
             
              /*Find User Data*/
            
              $querysl= "SELECT * FROM user WHERE id='$user_id'";                        
              $resultsl = mysqli_query($connect, $querysl);

              if(!$resultsl){
              die("Query Failed!3" . mysqli_error($connect));      
              } 

              $reseller_data= mysqli_fetch_object($resultsl);
              $recharge_balance = $reseller_data->balance2;
              $mbbalance = $reseller_data->bkash_balance;
              $uslevel   = $reseller_data->level;

              /*Find User Data*/
             
             
             /* Function for prevent of sql injection */            
          
              global $connect;
              $commission_m  = stripcslashes($commission_m);                        
             
              $commission_m  = mysqli_real_escape_string($connect, $commission_m);                          
           
             /* End of Function for prevent of sql injection */ 


             
              /* Get Direct Recharge Commission */

              $subadmin_comm   = Mixedfn::findcommission('1','subadmin');
              $l4_comm         = Mixedfn::findcommission('1','level4');
              $l3_comm         = Mixedfn::findcommission('1','level3');
              $l2_comm         = Mixedfn::findcommission('1','level2');
              $l1_comm         = Mixedfn::findcommission('1','level1');

              /* Get Direct Recharge Commission End*/

              
              /* Get Upper Recharge Commission */

              $subadmin_upcomm   = Mixedfn::findcommission('2','subadmin');
              $l4_upcomm         = Mixedfn::findcommission('2','level4');
              $l3_upcomm         = Mixedfn::findcommission('2','level3');
              $l2_upcomm         = Mixedfn::findcommission('2','level2');              

              /* Get Upper Recharge Commission End*/


              /* Get Direct M. Bank Commission */

              $subadmin_mbcomm   = Mixedfn::findcommission('3','subadmin');
              $l4_mbcomm         = Mixedfn::findcommission('3','level4');
              $l3_mbcomm         = Mixedfn::findcommission('3','level3');
              $l2_mbcomm         = Mixedfn::findcommission('3','level2');
              $l1_mbcomm         = Mixedfn::findcommission('3','level1');

              /* Get Direct M. Bank Commission End*/

              
              /* Get Upper M. Bank Commission */

              $subadmin_mbupcomm   = Mixedfn::findcommission('4','subadmin');
              $l4_mbupcomm         = Mixedfn::findcommission('4','level4');
              $l3_mbupcomm         = Mixedfn::findcommission('4','level3');
              $l2_mbupcomm         = Mixedfn::findcommission('4','level2');              

              /* Get Upper M. Bank Commission End*/

 
             /*Update fund request*/
            
              
              self::claim_transition('add_fund', $id, 'status=\'1\'', 'pending_add_balance.php'); // only settles a request once
             

             /*Update fund request end*/

                        

                  if($uslevel == 0) //Level1/Retailer Start
                  {
                        
                                          
                        /* Get Boss User ID and Balance */

                        $bossid_l2   = Mixedfn::boss_id($user_id); //Dealer/Level2
                        $bossrebl_l2 = Mixedfn::rebalance($bossid_l2);
                        $bossmbbl_l2 = Mixedfn::mbbalance($bossid_l2);

                        $bossid_l3   = Mixedfn::boss_id($bossid_l2); // DGM/Level3
                        $bossrebl_l3 = Mixedfn::rebalance($bossid_l3);
                        $bossmbbl_l3 = Mixedfn::mbbalance($bossid_l3);

                        $bossid_l4   = Mixedfn::boss_id($bossid_l3); // Distributor/Level4
                        $bossrebl_l4 = Mixedfn::rebalance($bossid_l4);
                        $bossmbbl_l4 = Mixedfn::mbbalance($bossid_l4);

                        $bossid_l5   = Mixedfn::boss_id($bossid_l4); // Subadmin
                        $bossrebl_l5 = Mixedfn::rebalance($bossid_l5);
                        $bossmbbl_l5 = Mixedfn::mbbalance($bossid_l5);

                        /* Get Boss User ID and Balance End*/


                        if ($balance_type == "Recharge")
                        {
                                                                
                                    $commission = ($l1_comm / 100) * $amount; 
                                    //$commission = (int)$commission; 
                                    
                                    $withcomm = $amount + $commission;
                                    
                                    $update_balance = $recharge_balance + $withcomm;
            
                                    Mixedfn::upbalance_autofund("balance2","$update_balance","$user_id"); //Update Customer Balance

                                    Mixedfn::insertpayhist_autofund("reseller_balance",$withcomm,$user_id,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                 
                                    $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from);                                    
                                   

                              /*Give commission to uppper level */

                                    if($bossid_l2 !=0 && $l2_upcomm !=0) //Dealer
                                    {
                                        //echo "ID True"; 

                                        $commission = ($l2_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l2 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l2"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l2,"Commission from Customer",$userId,$bossrebl_l2,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l2,$notifica_msgre,$notifica_from);
                                     

                                    }
                                    if($bossid_l3 !=0 && $l3_upcomm !=0) //DGM
                                    {
                                       
                                        $commission = ($l3_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l3 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l3"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l3,"Commission from Customer",$userId,$bossrebl_l3,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l3,$notifica_msgre,$notifica_from);
                                     

                                    }
                                    if($bossid_l4 !=0 && $l4_upcomm !=0) //Distributor House
                                    {
                                       
                                        $commission = ($l4_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l4 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l4"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossrebl_l4,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                     

                                    }
                                    if($bossid_l5 !=0 && $subadmin_upcomm !=0) //Sub Admin
                                    {
                                       
                                        $commission = ($subadmin_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l5 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l5"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossrebl_l5,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                     

                                    }                                                                   
                                  
                              /*Give commission to uppper level end*/
                  
                   
                                     Session::Flush('success', 'Successfully Recharge Balance Added!');
                                     Redirect::to('pending_add_balance.php');         


                        } 
                        elseif ($balance_type == "Mobile Banking")          
                        {
                              $commission = ($l1_mbcomm / 100) * $amount; 
                              //$commission = (int)$commission; 
                              
                              $withcomm = $amount + $commission;

                              $update_balance = $mbbalance + $withcomm;

                              Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$user_id"); //Update Customer Balance

                              Mixedfn::insertpayhist_autofund("bkash_balance",$withcomm,$user_id,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                        
                              $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                              $notifica_from  = "Balance Added";
                              Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from); 


                              /*Give commission to uppper level */

                              if($bossid_l2 !=0 && $l2_mbupcomm !=0) //Dealer
                              {
                                  //echo "ID True"; 

                                  $commission = ($l2_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l2 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l2"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l2,"Commission from Customer",$userId,$bossmbbl_l2,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l2,$notifica_msgre,$notifica_from);
                               

                              }
                              if($bossid_l3 !=0 && $l3_mbupcomm !=0) //DGM
                              {
                                 
                                  $commission = ($l3_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l3 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l3"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l3,"Commission from Customer",$userId,$bossmbbl_l3,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l3,$notifica_msgre,$notifica_from);
                               

                              }
                              if($bossid_l4 !=0 && $l4_mbupcomm !=0) //Distributor House
                              {
                                 
                                  $commission = ($l4_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l4 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l4"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossmbbl_l4,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                               

                              }
                              if($bossid_l5 !=0 && $subadmin_mbupcomm !=0) //Sub Admin
                              {
                                 
                                  $commission = ($subadmin_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l5 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l5"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossmbbl_l5,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                               

                              }                                                                   
                            
                              /*Give commission to uppper level end*/            
                    
                               Session::Flush('success', 'Successfully M. Banking Balance Added!');
                               Redirect::to('pending_add_balance.php');     


                        }
                        else
                        {
                           echo "Invalid!";
                        }
                        
                  } //Level1/Retailer End                  
                  elseif($uslevel == 1) //Level2/Dealer Start
                  {


                         /* Get Boss User ID and Balance */
                          
                         $bossid_l3   = Mixedfn::boss_id($user_id); // DGM/Level3
                         $bossrebl_l3 = Mixedfn::rebalance($bossid_l3);
                         $bossmbbl_l3 = Mixedfn::mbbalance($bossid_l3);
 
                         $bossid_l4   = Mixedfn::boss_id($bossid_l3); // Distributor/Level4
                         $bossrebl_l4 = Mixedfn::rebalance($bossid_l4);
                         $bossmbbl_l4 = Mixedfn::mbbalance($bossid_l4);
 
                         $bossid_l5   = Mixedfn::boss_id($bossid_l4); // Subadmin
                         $bossrebl_l5 = Mixedfn::rebalance($bossid_l5);
                         $bossmbbl_l5 = Mixedfn::mbbalance($bossid_l5);
 
                         /* Get Boss User ID and Balance End*/
                        
                        if ($balance_type == "Recharge")
                        {
                                                                
                                    $commission = ($l2_comm / 100) * $amount; 
                                    //$commission = (int)$commission; 
                                    
                                    $withcomm = $amount + $commission;
                                    
                                    $update_balance = $recharge_balance + $withcomm;
            
                                    Mixedfn::upbalance_autofund("balance2","$update_balance","$user_id"); //Update Customer Balance

                                    Mixedfn::insertpayhist_autofund("reseller_balance",$withcomm,$user_id,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                 
                                    $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from);                                    
                                   

                              /*Give commission to uppper level */
                                    
                                    if($bossid_l3 !=0 && $l3_upcomm !=0) //DGM
                                    {
                                       
                                        $commission = ($l3_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l3 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l3"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l3,"Commission from Customer",$userId,$bossrebl_l3,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l3,$notifica_msgre,$notifica_from);
                                     

                                    }
                                    if($bossid_l4 !=0 && $l4_upcomm !=0) //Distributor House
                                    {
                                       
                                        $commission = ($l4_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l4 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l4"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossrebl_l4,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                     

                                    }
                                    if($bossid_l5 !=0 && $subadmin_upcomm !=0) //Sub Admin
                                    {
                                       
                                        $commission = ($subadmin_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l5 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l5"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossrebl_l5,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                     

                                    }                                                                   
                                  
                              /*Give commission to uppper level end*/
                  
                   
                                     Session::Flush('success', 'Successfully Recharge Balance Added!');
                                     Redirect::to('pending_add_balance.php');         


                        } 
                        elseif ($balance_type == "Mobile Banking")          
                        {
                              $commission = ($l2_mbcomm / 100) * $amount; 
                              //$commission = (int)$commission; 
                              
                              $withcomm = $amount + $commission;

                              $update_balance = $mbbalance + $withcomm;

                              Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$user_id"); //Update Customer Balance

                              Mixedfn::insertpayhist_autofund("bkash_balance",$withcomm,$user_id,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                        
                              $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                              $notifica_from  = "Balance Added";
                              Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from); 


                              /*Give commission to uppper level */
                              
                              if($bossid_l3 !=0 && $l3_mbupcomm !=0) //DGM
                              {
                                 
                                  $commission = ($l3_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l3 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l3"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l3,"Commission from Customer",$userId,$bossmbbl_l3,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l3,$notifica_msgre,$notifica_from);
                               

                              }
                              if($bossid_l4 !=0 && $l4_mbupcomm !=0) //Distributor House
                              {
                                 
                                  $commission = ($l4_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l4 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l4"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossmbbl_l4,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                               

                              }
                              if($bossid_l5 !=0 && $subadmin_mbupcomm !=0) //Sub Admin
                              {
                                 
                                  $commission = ($subadmin_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l5 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l5"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossmbbl_l5,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                               

                              }                                                                   
                            
                              /*Give commission to uppper level end*/            
                    
                               Session::Flush('success', 'Successfully M. Banking Balance Added!');
                               Redirect::to('pending_add_balance.php');     


                        }
                        else
                        {
                           echo "Invalid!";
                        }
                        
                  } //Level2/Dealer End                  
                  elseif($uslevel == 2) //Level3/DGM Start
                  {


                         /* Get Boss User ID and Balance */                    
                        
                         $bossid_l4   = Mixedfn::boss_id($user_id); // Distributor/Level4
                         $bossrebl_l4 = Mixedfn::rebalance($bossid_l4);
                         $bossmbbl_l4 = Mixedfn::mbbalance($bossid_l4);
 
                         $bossid_l5   = Mixedfn::boss_id($bossid_l4); // Subadmin
                         $bossrebl_l5 = Mixedfn::rebalance($bossid_l5);
                         $bossmbbl_l5 = Mixedfn::mbbalance($bossid_l5);
 
                         /* Get Boss User ID and Balance End*/
                        
                        if ($balance_type == "Recharge")
                        {
                                                                
                                    $commission = ($l3_comm / 100) * $amount; 
                                    //$commission = (int)$commission; 
                                    
                                    $withcomm = $amount + $commission;
                                    
                                    $update_balance = $recharge_balance + $withcomm;
            
                                    Mixedfn::upbalance_autofund("balance2","$update_balance","$user_id"); //Update Customer Balance

                                    Mixedfn::insertpayhist_autofund("reseller_balance",$withcomm,$user_id,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                 
                                    $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from);                                    
                                   

                              /*Give commission to uppper level */
                                    
                                   
                                    if($bossid_l4 !=0 && $l4_upcomm !=0) //Distributor House
                                    {
                                       
                                        $commission = ($l4_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l4 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l4"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossrebl_l4,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                     

                                    }
                                    if($bossid_l5 !=0 && $subadmin_upcomm !=0) //Sub Admin
                                    {
                                       
                                        $commission = ($subadmin_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l5 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l5"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossrebl_l5,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                     

                                    }                                                                   
                                  
                              /*Give commission to uppper level end*/
                  
                   
                                     Session::Flush('success', 'Successfully Recharge Balance Added!');
                                     Redirect::to('pending_add_balance.php');         


                        } 
                        elseif ($balance_type == "Mobile Banking")          
                        {
                              $commission = ($l3_mbcomm / 100) * $amount; 
                              //$commission = (int)$commission; 
                              
                              $withcomm = $amount + $commission;

                              $update_balance = $mbbalance + $withcomm;

                              Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$user_id"); //Update Customer Balance

                              Mixedfn::insertpayhist_autofund("bkash_balance",$withcomm,$user_id,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                        
                              $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                              $notifica_from  = "Balance Added";
                              Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from); 


                              /*Give commission to uppper level */                              
                             
                              if($bossid_l4 !=0 && $l4_mbupcomm !=0) //Distributor House
                              {
                                 
                                  $commission = ($l4_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l4 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l4"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossmbbl_l4,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                               

                              }
                              if($bossid_l5 !=0 && $subadmin_mbupcomm !=0) //Sub Admin
                              {
                                 
                                  $commission = ($subadmin_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l5 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l5"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossmbbl_l5,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                               

                              }                                                                   
                            
                              /*Give commission to uppper level end*/            
                    
                               Session::Flush('success', 'Successfully M. Banking Balance Added!');
                               Redirect::to('pending_add_balance.php');     


                        }
                        else
                        {
                           echo "Invalid!";
                        }
                        
                  } //Level3/DGM End                  
                  elseif($uslevel == 3) //Level4/Distributor Start
                  {


                         /* Get Boss User ID and Balance */                    
                        
                         $bossid_l5   = Mixedfn::boss_id($user_id); // Subadmin
                         $bossrebl_l5 = Mixedfn::rebalance($bossid_l5);
                         $bossmbbl_l5 = Mixedfn::mbbalance($bossid_l5);
 
                         /* Get Boss User ID and Balance End*/
                        
                        if ($balance_type == "Recharge")
                        {
                                                                
                                    $commission = ($l4_comm / 100) * $amount; 
                                    //$commission = (int)$commission; 
                                    
                                    $withcomm = $amount + $commission;
                                    
                                    $update_balance = $recharge_balance + $withcomm;
            
                                    Mixedfn::upbalance_autofund("balance2","$update_balance","$user_id"); //Update Customer Balance

                                    Mixedfn::insertpayhist_autofund("reseller_balance",$withcomm,$user_id,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                 
                                    $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from);                                    
                                   

                              /*Give commission to uppper level */                                   
                                   
                                   
                                    if($bossid_l5 !=0 && $subadmin_upcomm !=0) //Sub Admin
                                    {
                                       
                                        $commission = ($subadmin_upcomm / 100) * $amount; 
                                        //$commission = (int)$commission;                                       
                                      
                                        $update_balance = $bossrebl_l5 + $commission;
                
                                        Mixedfn::upbalance_autofund("balance2","$update_balance","$bossid_l5"); //Update Customer Balance
    
                                        Mixedfn::insertpayhist_autofund("reseller_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossrebl_l5,$update_balance); //Insert Payment History      
                                     
                                        $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                        $notifica_from  = "Balance Added";
                                        Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                     

                                    }                                                                   
                                  
                              /*Give commission to uppper level end*/
                  
                   
                                     Session::Flush('success', 'Successfully Recharge Balance Added!');
                                     Redirect::to('pending_add_balance.php');         


                        } 
                        elseif ($balance_type == "Mobile Banking")          
                        {
                              $commission = ($l4_mbcomm / 100) * $amount; 
                              //$commission = (int)$commission; 
                              
                              $withcomm = $amount + $commission;

                              $update_balance = $mbbalance + $withcomm;

                              Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$user_id"); //Update Customer Balance

                              Mixedfn::insertpayhist_autofund("bkash_balance",$withcomm,$user_id,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                        
                              $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                              $notifica_from  = "Balance Added";
                              Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from); 


                              /*Give commission to uppper level */                            
                             
                              if($bossid_l5 !=0 && $subadmin_mbupcomm !=0) //Sub Admin
                              {
                                 
                                  $commission = ($subadmin_mbupcomm / 100) * $amount; 
                                  //$commission = (int)$commission;                                       
                                
                                  $update_balance = $bossmbbl_l5 + $commission;
          
                                  Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$bossid_l5"); //Update Customer Balance

                                  Mixedfn::insertpayhist_autofund("bkash_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossmbbl_l5,$update_balance); //Insert Payment History      
                               
                                  $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                  $notifica_from  = "Balance Added";
                                  Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                               

                              }                                                                   
                            
                              /*Give commission to uppper level end*/            
                    
                               Session::Flush('success', 'Successfully M. Banking Balance Added!');
                               Redirect::to('pending_add_balance.php');     


                        }
                        else
                        {
                           echo "Invalid!";
                        }
                        
                  } //Level4/Distributor End
                  elseif($uslevel == 4) //Subadmin Start
                  {
                        
                        if ($balance_type == "Recharge")
                        {
                                                                
                                    $commission = ($subadmin_comm / 100) * $amount; 
                                    //$commission = (int)$commission; 
                                    
                                    $withcomm = $amount + $commission;
                                    
                                    $update_balance = $recharge_balance + $withcomm;
            
                                    Mixedfn::upbalance_autofund("balance2","$update_balance","$user_id"); //Update Customer Balance

                                    Mixedfn::insertpayhist_autofund("reseller_balance",$withcomm,$user_id,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                 
                                    $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from);                                    
                                
                                     Session::Flush('success', 'Successfully Recharge Balance Added!');
                                     Redirect::to('pending_add_balance.php');         


                        } 
                        elseif ($balance_type == "Mobile Banking")          
                        {
                              $commission = ($subadmin_mbcomm / 100) * $amount; 
                              //$commission = (int)$commission; 
                              
                              $withcomm = $amount + $commission;

                              $update_balance = $mbbalance + $withcomm;

                              Mixedfn::upbalance_autofund("bkash_balance","$update_balance","$user_id"); //Update Customer Balance

                              Mixedfn::insertpayhist_autofund("bkash_balance",$withcomm,$user_id,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                        
                              $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                              $notifica_from  = "Balance Added";
                              Notice::InsertNotification($user_id,$notifica_msgre,$notifica_from); 

                               Session::Flush('success', 'Successfully M. Banking Balance Added!');
                               Redirect::to('pending_add_balance.php');     


                        }
                        else
                        {
                           echo "Invalid!";
                        }
                        
                  } //Subadmin End
                  else
                  {
                        echo "Invalid!";
                  }
                  
    
        
       } 
 
       /* Function for Success Balance Add resquest end */

 
       /* Function for Cancel Balance Add resquest start */
       
        public static function baladdcancel()
        {
              $f_id      = Input::get('id');             
              $msg       = Input::get('remsg');
              
              
               /* Function for prevent of sql injection */            
           
               global $connect;
              
               $msg    = stripcslashes($msg);                   
              
               $msg    = mysqli_real_escape_string($connect, $msg);
              
               
           /* End of Function for prevent of sql injection */
  
           
              if (!preg_match("/^([A-Za-z0-9,-: ])*$/", $msg))         
              {      
                    Session::Flush('error', 'Message only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                    Redirect::to('pending_billpay.php');            
              }
              else
              {
                       
                          self::claim_transition('add_fund', $f_id, "status='3',comment='$msg'", 'pending_add_balance.php'); // only settles a request once
  
                         
                          Session::Flush('success', 'Successfully Updated!');
                          Redirect::to('pending_add_balance.php');
                    
               
                     
              }
                    
         
        }
  
  
        /* Function for cancel Balance Add resquest end */


	
	
}