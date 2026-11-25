<?php
require_once dirname(__DIR__, 3) . '/lib/bootstrap.php';
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
                  Redirect::to('customer_list.php');
                  
            }
            elseif(!is_numeric($userPin)) 
            {                
                  Session::Flush('error', 'Pin Code allow only number!');
                  Redirect::to('customer_list.php');
            }
            elseif (empty($userPin)) 
            {               
                  Session::Flush('error', 'You must enter your Pin Code!');
                  Redirect::to('customer_list.php');
            }
            elseif(!is_numeric($amount)) 
            {                
                  Session::Flush('error', 'Amount allow only number!');
                  Redirect::to('customer_list.php');
            }
		elseif(intval($amount)==0)
		{
		      Session::Flush('error', 'Amount is not valid');
			Redirect::to('customer_list.php');
		}
		elseif($amount<1 || $amount>1000000)
		{
			Session::Flush('error', 'Amount is not valid');
			Redirect::to('customer_list.php');
		}
		elseif(strlen($amount)<1)
		{
			Session::Flush('error', 'Amount is not valid');
			Redirect::to('customer_list.php');
		}                       
            elseif (!preg_match("/^([A-Za-z0-9,-: ])*$/", $note))                                    
            {
                  Session::Flush('error', 'Note allow only letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                  Redirect::to('customer_list.php');                           
            }
            else
            {
                  if($type == "Transfer")
                  {		
                                              
                        if($get_selfbalance < $amount)
                        {
                              Session::Flush('error', 'Insufficient Your Recharge Balance!');
                              Redirect::to('customer_list.php');
                        }
                        else 
                        {

                              /* Atomic upline -> direct-downline transfer (lib/Midhub/Wallet/TransferService.php).
                                       The legacy code accepted any user id as recipient and could
                                       send the same balance twice under parallel requests. */
                                    try {
                                          $mv = \Midhub\Wallet\TransferService::toDownline((int) $us_id, (int) $resId, 'balance2', (string) $amount, (string) $note);
                                    } catch (\Midhub\Wallet\InsufficientBalanceException $e) {
                                          Session::Flush('error', 'Insufficient Your Recharge Balance!');
                                          Redirect::to('cs-profile.php?reseller_id=' . (int) $resId);
                                          exit();
                                    } catch (\DomainException $e) {
                                          Session::Flush('error', $e->getMessage());
                                          Redirect::to('customer_list.php');
                                          exit();
                                    }
                                    $resId = (int) $resId;
                                    $balance = $mv['to_before'];
                                    $update_balance = $mv['to_after'];
                                    /*Update User Balance End*/

                              $queryin  = "INSERT INTO  reseller_balance (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
                              $queryin .= "VALUES(0,'$resId','$amount',NOW(),'$us_id',NOW(),'$note','$ip','0','$balance','$update_balance')"; 
                                          
                              $resultin = mysqli_query($connect, $queryin);    
                              if(!$resultin)
                              {                                          
                              die("Query Failed2!" . mysqli_error($connect));
                              }
                  
                                    $notifica_msgre = "Recharge Balance added to your account from " . $get_selfusername . ". Amount: " . $amount . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($resId,$notifica_msgre,$notifica_from);      
                        
                                    Session::Flush('success', 'Successfully Balance Added!');
                                    Redirect::to('customer_list.php'); 
                                    
                        }
                        
                  }
                  elseif($type == "TransferMb")
                  {
                         
                        if($get_selfmbbalance < $amount)
                        {
                              Session::Flush('error', 'Insufficient Your M. Banking Balance');
                              Redirect::to('customer_list.php');
                        }
                        else 
                        {

                                    /* Atomic upline -> direct-downline transfer (lib/Midhub/Wallet/TransferService.php).
                                       The legacy code accepted any user id as recipient and could
                                       send the same balance twice under parallel requests. */
                                    try {
                                          $mv = \Midhub\Wallet\TransferService::toDownline((int) $us_id, (int) $resId, 'bkash_balance', (string) $amount, (string) $note);
                                    } catch (\Midhub\Wallet\InsufficientBalanceException $e) {
                                          Session::Flush('error', 'Insufficient Your M. Banking Balance');
                                          Redirect::to('cs-profile.php?reseller_id=' . (int) $resId);
                                          exit();
                                    } catch (\DomainException $e) {
                                          Session::Flush('error', $e->getMessage());
                                          Redirect::to('customer_list.php');
                                          exit();
                                    }
                                    $resId = (int) $resId;
                                    $balance = $mv['to_before'];
                                    $update_balance = $mv['to_after'];
                                    /*Update User Balance End*/

                                    $queryin  = "INSERT INTO  bkash_balance (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
                                    $queryin .= "VALUES(0,'$resId','$amount',NOW(),'$us_id',NOW(),'$note','$ip','0','$balance','$update_balance')"; 
                                          
                                    $resultin = mysqli_query($connect, $queryin);    
                                    if(!$resultin)
                                    {                                                
                                    die("Query Failed2!" . mysqli_error($connect));
                                    }

                                    $notifica_msgre = "M. Bank Balance added to your account from " . $get_selfusername . ". Amount: " . $amount . " Tk. New Balance: " . $update_balance . " Tk.";
                                    $notifica_from  = "Balance Added";
                                    Notice::InsertNotification($resId,$notifica_msgre,$notifica_from);      
                                          
                                    Session::Flush('success', 'Successfully Balance Added!');
                                    Redirect::to('customer_list.php');
                                    
                        }
                  }
                  else 
                  {
                        Session::Flush('error', 'Invalid!');
                        Redirect::to('customer_list.php');
                  }


            }       

			
      }
  
     /* Customer Add Balance End */

      

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
