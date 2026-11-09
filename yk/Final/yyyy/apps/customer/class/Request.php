<?php
class Request
{
    public static $rates = [];

        
    public static function get_low_balance($user_id)
    {
        $query="SELECT * from user where id='$user_id'";
        global $connect;
        $row=mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($row);
        $low_balance=$reseller_data->limit_balance;
        return $low_balance;
    }

    public static function get_default_low_balance()
    {
        $lowbl_query="SELECT * from low_balance where id='1'";
        global $connect;
        $row=mysqli_query($connect, $lowbl_query);
        $reseller_data= mysqli_fetch_object($row);
        $default_low_balance=$reseller_data->amount;
        return $default_low_balance;
    }
	
	public static function send_flexiload()
    {
        $num = $_POST['number'];
        $load_type = $_POST['type'];
        $ownpin = $_POST['pincode'];
        $userlevel = Session::get('level');
        $us_id = Session::get('us_id');
        $amount = $_POST['amount'];


         /* Function for prevent of sql injection */
         global $connect;
         $num               = stripcslashes($num); 
         $load_type         = stripcslashes($load_type); 
         $amount            = stripcslashes($amount);               
         $ownpin            = stripcslashes($ownpin); 
               
         $num            = mysqli_real_escape_string($connect, $num); 
         $load_type      = mysqli_real_escape_string($connect, $load_type);         
         $amount         = mysqli_real_escape_string($connect, $amount);         
         $ownpin         = mysqli_real_escape_string($connect, $ownpin);       
     
         /* End of Function for prevent of sql injection */

        self::make_flexiload($us_id, $num, $amount, $load_type, $ownpin, $userlevel);
    }
	
	
	public static function send_flexiload_mnp()
    {
        $num        = $_POST['number'];
        $load_type  = $_POST['type'];
        $ownpin     = $_POST['pincode'];
        $userlevel  = Session::get('level');
        $us_id      = Session::get('us_id');
        $amount     = $_POST['amount'];
		$operator   = $_POST['operator'];


        /* Function for prevent of sql injection */
        global $connect;
        $num               = stripcslashes($num); 
        $load_type         = stripcslashes($load_type); 
        $amount            = stripcslashes($amount); 
        $operator          = stripcslashes($operator);         
        $ownpin            = stripcslashes($ownpin); 
              
        $num            = mysqli_real_escape_string($connect, $num); 
        $load_type      = mysqli_real_escape_string($connect, $load_type); 
        $operator       = mysqli_real_escape_string($connect, $operator); 
        $amount         = mysqli_real_escape_string($connect, $amount);         
        $ownpin         = mysqli_real_escape_string($connect, $ownpin);       
    
        /* End of Function for prevent of sql injection */
		
		
        self::make_flexiload_mnp($us_id, $num, $amount, $load_type, $operator, $ownpin, $userlevel);
    }
	


    public static function send_billpay_request()
    {
        
        $number          = $_POST['number'];
        $amount          = $_POST['amount'];
        $type            = $_POST['type'];
        $company         = $_POST['company'];
        $company_type    = $_POST['company_type'];
        $cs_name         = $_POST['cs_name'];
        $month           = $_POST['month'];
        $ownpin          = $_POST['pincode'];
        $contact_number  = $_POST['contact_number']; 
        $userlevel       = Session::get('level');
        $us_id           = Session::get('us_id');
        $cost            = $amount; 
        


         /* Function for prevent of sql injection */
         global $connect;
         $number         = stripcslashes($number);     
         $amount         = stripcslashes($amount);
         $type           = stripcslashes($type);
         $company        = stripcslashes($company);
         $company_type   = stripcslashes($company_type);
         $cs_name        = stripcslashes($cs_name);
         $month          = stripcslashes($month); 
         $contact_number = stripcslashes($contact_number);     
         $ownpin         = stripcslashes($ownpin);

               
         $number         = mysqli_real_escape_string($connect, $number); 
         $amount         = mysqli_real_escape_string($connect, $amount);
         $type           = mysqli_real_escape_string($connect, $type);
         $company        = mysqli_real_escape_string($connect, $company);
         $company_type   = mysqli_real_escape_string($connect, $company_type);
         $cs_name        = mysqli_real_escape_string($connect, $cs_name);
         $month          = mysqli_real_escape_string($connect, $month); 
         $contact_number = mysqli_real_escape_string($connect, $contact_number);        
         $ownpin = mysqli_real_escape_string($connect, $ownpin);       
     
         /* End of Function for prevent of sql injection */
               
        

        self::make_billpay_request($us_id, $number, $amount, $cost, $type, $company, $company_type, $cs_name, $month, $contact_number, $ownpin, $userlevel);
    }



    public static function send_drive_request()
    {
        $pkg_id = $_POST['pkg_id'];
        $num = $_POST['number'];
        $load_type = $_POST['type'];
        $ownpin = $_POST['pincode'];
        $userlevel = Session::get('level');
        $us_id = Session::get('us_id');
        //$amount = $_POST['amount'];


         /* Function for prevent of sql injection */
         global $connect;
         $num         = stripcslashes($num);
         $load_type   = stripcslashes($load_type);           
         $ownpin      = stripcslashes($ownpin); 
               
         $num       = mysqli_real_escape_string($connect, $num);
         $load_type = mysqli_real_escape_string($connect, $load_type);           
         $ownpin    = mysqli_real_escape_string($connect, $ownpin);       
     
         /* End of Function for prevent of sql injection */

        
        $sql = "SELECT * FROM drive WHERE id = '$pkg_id'";
        
        $package = DB::getInstance()->get($sql)->result()[0];
        
		$amount = $package->amount;
        $cost   = $package->balance;

        self::make_drive_request($us_id, $num, $amount, $cost, $load_type, $ownpin, $userlevel);
    }



    public static function send_package_request()
    {
        $pkg_id = $_POST['pkg_id'];
        $num = $_POST['number'];
        $load_type = $_POST['type'];
        $ownpin = $_POST['pincode'];
        $userlevel = Session::get('level');
        $us_id = Session::get('us_id');
        //$amount = $_POST['amount'];


         /* Function for prevent of sql injection */
         global $connect;
         $num         = stripcslashes($num);
         $load_type   = stripcslashes($load_type);          
         $ownpin      = stripcslashes($ownpin); 
               
         $num       = mysqli_real_escape_string($connect, $num); 
         $load_type = mysqli_real_escape_string($connect, $load_type);         
         $ownpin    = mysqli_real_escape_string($connect, $ownpin);       
     
         /* End of Function for prevent of sql injection */

        
        $sql = "SELECT * FROM package WHERE id = '$pkg_id'";
        
        $package = DB::getInstance()->get($sql)->result()[0];
        
		$amount = $package->amount;
        $cost   = $package->balance;

        self::make_package_request($us_id, $num, $amount, $cost, $load_type, $ownpin, $userlevel);
    }


    
	public static function send_m_banking()
    {
        $num = $_POST['number'];
        $type = $_POST['type'];
        $m_banking_type = $_POST['m_banking_type'];
        $ownpin = $_POST['pincode'];
        $userlevel = Session::get('level');
        $us_id = Session::get('us_id');
        $amount = $_POST['amount'];


        /* Function for prevent of sql injection */
        global $connect;
        $num               = stripcslashes($num); 
        $type              = stripcslashes($type); 
        $m_banking_type    = stripcslashes($m_banking_type); 
        $amount            = stripcslashes($amount);         
        $ownpin            = stripcslashes($ownpin); 
              
        $num            = mysqli_real_escape_string($connect, $num); 
        $type           = mysqli_real_escape_string($connect, $type); 
        $m_banking_type = mysqli_real_escape_string($connect, $m_banking_type); 
        $amount         = mysqli_real_escape_string($connect, $amount);         
        $ownpin         = mysqli_real_escape_string($connect, $ownpin);       
    
        /* End of Function for prevent of sql injection */


        self::make_m_banking($us_id, $num, $amount, $type, $m_banking_type,$ownpin, $userlevel);		
    }


    public static function send_bank_request()
    {
        $bank_name = $_POST['bankname'];
        $branch_name = $_POST['branch'];
        $ac_type = $_POST['ac_type'];
        $account_name = $_POST['acname'];
        $account_number = $_POST['acnumber'];
        $amount = $_POST['amount'];
        $contact_number = $_POST['contact_number'];
        $note = $_POST['note'];
        $ownpin = $_POST['pincode'];
        $userlevel = Session::get('level');
        $us_id = Session::get('us_id');
        

        /* Function for prevent of sql injection */
        global $connect;
        $bank_name         = stripcslashes($bank_name); 
        $branch_name       = stripcslashes($branch_name); 
        $ac_type           = stripcslashes($ac_type); 
        $account_name      = stripcslashes($account_name); 
        $account_number    = stripcslashes($account_number); 
        $amount            = stripcslashes($amount); 
        $contact_number    = stripcslashes($contact_number); 
        $note              = stripcslashes($note); 
        $ownpin            = stripcslashes($ownpin); 
              
        $bank_name      = mysqli_real_escape_string($connect, $bank_name); 
        $branch_name    = mysqli_real_escape_string($connect, $branch_name); 
        $ac_type        = mysqli_real_escape_string($connect, $ac_type); 
        $account_name   = mysqli_real_escape_string($connect, $account_name); 
        $account_number = mysqli_real_escape_string($connect, $account_number);         
        $amount         = mysqli_real_escape_string($connect, $amount);         
        $contact_number = mysqli_real_escape_string($connect, $contact_number);         
        $note           = mysqli_real_escape_string($connect, $note);         
        $ownpin         = mysqli_real_escape_string($connect, $ownpin);       
    
        /* End of Function for prevent of sql injection */


        self::make_bank_request($us_id,$bank_name,$branch_name,$ac_type,$account_name,$account_number,$amount,$contact_number,$note,$ownpin,$userlevel);		
    }
	
	
	
	public static function get_company($phone)
    {
        $three = $phone[0] . $phone[1] . $phone[2];
        if ($three == "017")
        {
            $abc = "grameenphone";
        }
        if ($three == "016")
        {
            $abc = "airtel";
        }
        if ($three == "018")
        {
            $abc = "robi";
        }
        if ($three == "019")
        {
            $abc = "banglalink";
        }
        if ($three == "013")
        {
            $abc = "grameenphone";
        }
        if ($three == "015")
        {
            $abc = "teletalk";
        }
		if ($three == "014")
        {
            $abc = "banglalink";
        }
        return $abc;
    }

    public static function get_transaction_id($user_id)
    {
        $user_query = "SELECT transactionid from reportserver where f_id='$user_id'";
        global $connect;
        $user_row = mysqli_query($connect, $user_query);
        $reseller_data= mysqli_fetch_object($user_row);        
        $transactionid = !isset($reseller_data->transactionid) ? "" : $reseller_data->transactionid;        
        return $transactionid;
    }


    public static function get_drive_transaction_id($user_id)
    {
        $user_query = "SELECT transactionid from drive_msg where f_id='$user_id'";
        global $connect;
        $user_row = mysqli_query($connect, $user_query);
        $reseller_data= mysqli_fetch_object($user_row);        
        $transactionid = !isset($reseller_data->transactionid) ? "" : $reseller_data->transactionid;        
        return $transactionid;
    }

	public static function get_m_transaction_id($user_id)
    {
        $user_query = "SELECT transactionid from bkash_report where f_id='$user_id'";        
        global $connect;
        $user_row = mysqli_query($connect, $user_query);
        $reseller_data= mysqli_fetch_object($user_row);  
        $transactionid = !isset($reseller_data->transactionid) ? "" : $reseller_data->transactionid;
        return $transactionid;
    }

	public static function get_m_sending_number($user_id)
    {
        $user_query = "SELECT sending_number from bkash_report where f_id='$user_id'";
        global $connect;
        $user_row = mysqli_query($connect, $user_query);
        $reseller_data= mysqli_fetch_object($user_row);      
        $sending_number = empty($reseller_data->sending_number) ? "" : $reseller_data->sending_number;
        return $sending_number;
    }


    public static function get_pin($user_id)
    {
        $user_query = "SELECT *from user where id='$user_id'";
        global $connect;
        $user_row = mysqli_query($connect, $user_query);
        $reseller_data= mysqli_fetch_object($user_row);
        $usersname = ($reseller_data->pin_code);
        return $usersname;
    }

    public static function get_balance($user_id)
    {
        $query = "SELECT * from user where id='$user_id'";
        global $connect;
        $row = mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($row);       
        $balance = $reseller_data->balance2;
        return $balance;
    }

	public static function get_bkash_balance($user_id)
    {
        $query = "SELECT * from user where id='$user_id'";
        global $connect;
        $row = mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($row);       
        $balance = $reseller_data->bkash_balance;
        return $balance;
    }

    public static function get_boss_id($user_id)
    {
        $query = "SELECT * from user where id='$user_id'";
        global $connect;
        $row = mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($row);        
        $balance = $reseller_data->created_by;
        return $balance;
    }

    public static function my_boss_balance($user_id)
    {
        $query = "SELECT * from user where id='$user_id'";
        global $connect;
        $row = mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($row);  
        $boss_balance = self::get_balance($reseller_data->created_by);
        return $boss_balance;
    }

	public static function my_boss_bkash_balance($user_id)
    {
        $query = "SELECT * from user where id='$user_id'";
        global $connect;
        $row = mysqli_query($connect, $query);
        $reseller_data= mysqli_fetch_object($row);  
        $boss_balance = self::get_bkash_balance($reseller_data->created_by);
        return $boss_balance;
    }

	public static function get_number_exits($mobile)
    {
        $user_query="SELECT count(*) as cnt from send_query where phone='$mobile' and status=0";
        global $connect;
        $row = mysqli_query($connect, $user_query);
        $reseller_data= mysqli_fetch_object($row);  
        $level=$reseller_data->cnt;
        return $level;
    }

    public static function getSendingRestriction($phone)
    {
        $error = 0;
        $sql = "SELECT phone from send_query where phone='$phone' and status='0' limit 1";
        global $connect;
        $result = mysqli_query($connect, $sql); 

        $count = mysqli_num_rows($result); 

        if ($count > 0)
        {
            $error = 1;
        }

        $sql2 = "SELECT phone from send_query where phone='$phone' and status='4' limit 1";
        global $connect;
        $result = mysqli_query($connect, $sql2); 
        $count = mysqli_num_rows($result); 

        if ($count > 0)
        {
            $error = 1;
        }

        return $error;
    }


    public static function mbank_getSendingRestriction($phone)
    {
        $error = 0;
        $sql = "SELECT account_number from bkash_send where account_number='$phone' and status='0' limit 1";
        global $connect;
        $result = mysqli_query($connect, $sql); 

        $count = mysqli_num_rows($result); 

        if ($count > 0)
        {
            $error = 1;
        }

        $sql2 = "SELECT account_number from bkash_send where account_number='$phone' and status='4' limit 1";
        global $connect;
        $result = mysqli_query($connect, $sql); 
        $count = mysqli_num_rows($result); 

        if ($count > 0)
        {
            $error = 1;
        }

        return $error;
    }


    public static function make_flexiload($us_id, $num, $amount, $load_type, $ownpin, $userlevel)
    {

        $mybalance = self::get_balance($us_id);
        $db_pin = self::get_pin($us_id);
        $time = time();

        //echo $db_pin."--".$ownpin; exit;
		
		$lowbalance = Mixedfn::low_balance($us_id);						
		$dlowbalance = Mixedfn::default_low_balance("recharge_low");

        $current_balance = $mybalance - $amount;


		$ip=$_SERVER["REMOTE_ADDR"];
		$refrom='web';
        $apirid = mt_rand(100000,999999);
        $apicount = Api::apistatus("1","eFlexiload","Recharge"); //API Active/Inactive Function

        
		if($current_balance <= $lowbalance)
			{								
				Session::Flush('error', 'You have a low balance.');
				Redirect::to('send_recharge.php');
			}
		elseif($current_balance <= $dlowbalance)
			{			                
				Session::Flush('error', 'Cannot transaction with your limit balance');
				Redirect::to('send_recharge.php');							
			}
        elseif(empty($num)) 
            {                
                Session::Flush('error', 'You must enter mobile Number!');
                Redirect::to('send_recharge.php');
            } 
        elseif(!is_numeric($num)) 
            {                
                Session::Flush('error', 'Mobile Number allow only Number!');
                Redirect::to('send_recharge.php');
            }     		
        elseif ($num == "")
            {
                Session::Flush('error', 'Invalid number. Please input a valid number.');
                Redirect::to('send_recharge.php');
            }
        elseif (strlen($num) < 11)
            {                
                Session::Flush('error', 'Phone number must be 11 digit');
                Redirect::to('send_recharge.php');
            }
        elseif(empty($amount)) 
            {                
                Session::Flush('error', 'You must enter amount!');
                Redirect::to('send_recharge.php');
            } 
        elseif(!is_numeric($amount)) 
            {                
                Session::Flush('error', 'Amount allow only Number!');
                Redirect::to('send_recharge.php');
            }        
        elseif ($amount < 5 || $amount > 10000)
            {
                Session::Flush('error', 'Invalid Amount. Amount must be between 9 to 10000');
                Redirect::to('send_recharge.php');
            }
        elseif ($mybalance < $amount)
            {
                Session::Flush('error', 'Insufficient balance. Please contact with your boss!');
                Redirect::to('send_recharge.php');
            }
        elseif (self::getSendingRestriction($num) == 1)
            {
                Session::Flush('error', 'You already request in this number. Please wait for completed before requested.');
                Redirect::to('send_recharge.php');
            }
        elseif(empty($ownpin)) 
            {                
                Session::Flush('error', 'You must enter pincode!');
                Redirect::to('send_recharge.php');
            } 
        elseif(!is_numeric($ownpin)) 
            {                
                Session::Flush('error', 'Pincode allow only Number!');
                Redirect::to('send_recharge.php');
            }   
        elseif ($db_pin != md5($ownpin))
            {
                Session::Flush('error', 'Pin code is invalid!');
                Redirect::to('send_recharge.php');
            }                       
        else
            {           
			
			if ($userlevel == 0) /* Retailer/Level1 Request Start */
            {

                $r1 = $us_id;
                $r1B = $mybalance;

                $r2 = self::get_boss_id($us_id);
                $r2B = self::get_balance($r2);

                $r3 = self::get_boss_id($r2);
                $r3B = self::get_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('send_recharge.php');
                }
                
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result)
                    {
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    $operator = self::get_company($num);


                    if($apicount == 1)  //check API active or inactive
                    {
                        $status = Api::apirequest("$num","$amount","$load_type","$apirid"); //recharge request pass to API

                            if($status == "SUCCESS")
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'6','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom','$apirid')";
                               
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {                            
                                    die("Query Failed!" . mysqli_error($connect));
                                }
                            }
                            else //if api sent not working
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom','0')";
                               
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {                            
                                    die("Query Failed!" . mysqli_error($connect));
                                }
                            }

                    }
                    else //if api is inative
                    {

                        $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                        $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom','0')";
                       
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {                            
                            die("Query Failed!" . mysqli_error($connect));
                        }

                    }

                    Session::Flush('success', 'Your Requested Transaction of service (<b>Recharge</b>) has been initiated.');
                    Redirect::to('send_recharge.php');

                } 
                
            } /* Retailer/Level1 Request End */
            elseif ($userlevel == 1) /* Dealer/Level2 Request Start */
            {
                $r1 = $us_id;
                $r1B = $mybalance;

                $r3 = self::get_boss_id($r1);
                $r3B = self::get_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);
                
				if ($r1B  < $amount)
                {
                    Session::Flush('error', 'Insufficient Balance Your Balance');
                    Redirect::to('send_recharge.php');
                }               
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query ="UPDATE user set balance2='$r1Nb' where id='$r1'";
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
					
                    
                    $operator = self::get_company($num);
                    
                    if($apicount == 1) //check API is active/inactive
                    {
                        $status = Api::apirequest("$num","$amount","$load_type","$apirid"); //recharge request pass to API
                            
                            if($status == "SUCCESS")
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'6','$time','$operator',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom','$apirid')";
                                
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {
                                    
                                    die("Query Failed!" . mysqli_error($connect));
                                }
                            }
                            else //if api is not working
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom,'0')";
                                
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {
                                    
                                    die("Query Failed!" . mysqli_error($connect));
                                }
                            }
                    }
                    else //If API is inactive
                    {
                        $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                        $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom','0')";
                        
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
                    }                  
                  
                    Session::Flush('success', 'Your Requested Transaction of service (<b>Recharge</b>) has been initiated.');
                    Redirect::to('send_recharge.php');
                }
            } /* Dealer/Level2 Request End */          
			elseif ($userlevel == 2) /* DGM/Level3 Request Start */
            {

                $r1 = $us_id;
                $r1B = $mybalance;

                $r4 = self::get_boss_id($r1);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Balance Your Balance');
                    Redirect::to('send_recharge.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query ="UPDATE user set balance2='$r1Nb' where id='$r1'";
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    $operator = self::get_company($num);


                    if($apicount == 1) //Check API active/Inactive
                    {
                        $status = Api::apirequest("$num","$amount","$load_type","$apirid"); //recharge request pass to API
                            
                            if($status == "SUCCESS")
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'6','$time','$operator',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom','$apirid')";
            
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {
                                    
                                    die("Query Failed!" . mysqli_error($connect));
                                }
                            }
                            else //If API is not working
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom','0')";
            
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {
                                    
                                    die("Query Failed!" . mysqli_error($connect));
                                }
                            }
                    }
                    else //If API is Inactive
                    {
                        $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                        $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom','0')";
    
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
    
                    }

                    Session::Flush('success', 'Your Requested Transaction of service (<b>Recharge</b>) has been initiated.');
                    Redirect::to('send_recharge.php');
                }
            } /* DGM/Level3 Request End */			
			elseif ($userlevel == 3) /* Distributor/Level4 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;               

                $r5 = self::get_boss_id($r1);
                $r5B = self::get_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Balance Your Balance');
                    Redirect::to('send_recharge.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                  

                    $operator = self::get_company($num);

                    if($apicount == 1) //Check API Active of Inactive
                    {
                        $status = Api::apirequest("$num","$amount","$load_type","$apirid"); //recharge request pass to API
                            
                            if($status == "SUCCESS")
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'6','$time','$operator',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom','$apirid')";
               
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {
                                    
                                    die("Query Failed!" . mysqli_error($connect));
                                }       
                            }
                            else //If API is not working
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom','0')";
                                         
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {
                                    
                                    die("Query Failed!" . mysqli_error($connect));
                                }       
                            }
                    }
                    else //If API is Inactive
                    {
                        $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                        $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom','0')";
    
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }                   
                         
                    }

                      Session::Flush('success', 'Your Requested Transaction of service (<b>Recharge</b>) has been initiated.');
                      Redirect::to('send_recharge.php');
 
                }
            } /* Distributor/Level4 Request End*/				
			elseif ($userlevel == 4) /* Subadmin/Level5 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;            
              
                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Balance');
                    Redirect::to('send_recharge.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result)
                    {
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                


                    $operator = self::get_company($num);

                    if($apicount == 1) //Check API active or Inactive
                    {
                        $status = Api::apirequest("$num","$amount","$load_type","$apirid"); //recharge request pass to API
                            
                            if($status == "SUCCESS")
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'6','$time','$operator',CURDATE(),'0','0','0','0','$r1','$ip','$refrom','$apirid')";
               
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {
                                    
                                    die("Query Failed!" . mysqli_error($connect));
                                }
                            }
                            else //If API is not Working
                            {
                                $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                                $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','0','$r1','$ip','$refrom','0')";
               
                                $result = mysqli_query($connect, $query);    
                                if(!$result)
                                {
                                    
                                    die("Query Failed!" . mysqli_error($connect));
                                }
                            }
                    }
                    else //If API is Inactive
                    {
                        $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                        $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','0','$r1','$ip','$refrom','0')";
       
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
    
                       
                    }   

                    Session::Flush('success', 'Your Requested Transaction of service (<b>Recharge</b>) has been initiated.');
                    Redirect::to('send_recharge.php');
                }
            } /* Subadmin/Level5 Request End*/
		
			
        }
    }




public static function make_flexiload_mnp($us_id, $num, $amount, $load_type, $operator, $ownpin, $userlevel)
    {

        $mybalance = self::get_balance($us_id);
        $db_pin = self::get_pin($us_id);
        $time = time();
		
	
        //echo $db_pin."--".$ownpin; exit;
		
		$lowbalance = Mixedfn::low_balance($us_id);						
		$dlowbalance = Mixedfn::default_low_balance("recharge_low");

        $current_balance = $mybalance - $amount;

		$ip=$_SERVER["REMOTE_ADDR"];
		$refrom='web';

        /* Function for prevent of sql injection */
        global $connect;
        $num = stripcslashes($num);
        $amount = stripcslashes($amount);
        $ownpin= stripcslashes($ownpin);
        $num = mysqli_real_escape_string($connect, $num);
        $amount = mysqli_real_escape_string($connect, $amount);
        $ownpin = mysqli_real_escape_string($connect, $ownpin);
    
        /* End of Function for prevent of sql injection */

		if($current_balance <= $lowbalance)
		{								
			Session::Flush('error', 'You have a low balance.');
			Redirect::to('send_mnp.php');
		}
		elseif($current_balance <= $dlowbalance)
		{			                
			Session::Flush('error', 'Cannot transaction with your limit balance');
			Redirect::to('send_mnp.php');							
		}
        elseif(empty($num)) 
        {                
            Session::Flush('error', 'You must enter Phone Number!');
            Redirect::to('send_mnp.php');
        } 
        elseif(!is_numeric($num)) 
        {                
            Session::Flush('error', 'Phone number allow only Number!');
            Redirect::to('send_mnp.php');
        }  		
        elseif ($num == "")
        {
            Session::Flush('error', 'Invalid number. Please input a valid number.');
            Redirect::to('send_mnp.php');
        }
        elseif (strlen($num) < 11)
        {
            Session::Flush('error', 'Phone number must be 11 digit');
            Redirect::to('send_mnp.php');
        }
        elseif(empty($amount)) 
        {                
            Session::Flush('error', 'You must enter amount!');
            Redirect::to('send_mnp.php');
        } 
        elseif(!is_numeric($amount)) 
        {                
            Session::Flush('error', 'Amount allow only Number!');
            Redirect::to('send_mnp.php');
        }     
        elseif ($amount < 5 || $amount > 10000)
        {
            Session::Flush('error', 'Invalid Amount. Amount must be between 9 to 10000');
            Redirect::to('send_mnp.php');
        }
        elseif ($mybalance < $amount)
        {
            Session::Flush('error', 'Insufficient balance. Please contact with your boss!');
            Redirect::to('send_mnp.php');
        }
        elseif (self::getSendingRestriction($num) == 1)
        {
            Session::Flush('error', 'Wait for completed your already request in this number.');
            Redirect::to('send_mnp.php');
        }
        elseif(empty($ownpin)) 
        {                
            Session::Flush('error', 'You must enter pincode!');
            Redirect::to('send_mnp.php');
        } 
        elseif(!is_numeric($ownpin)) 
        {                
            Session::Flush('error', 'Pincode allow only Number!');
            Redirect::to('send_mnp.php');
        }     
        elseif ($db_pin != md5($ownpin))
        {
            Session::Flush('error', 'Pin code is not correct.');
            Redirect::to('send_mnp.php');
        }                     
        else
        {
			
			if ($userlevel == 0)  /* Retailer/Level1 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;

                $r2 = self::get_boss_id($us_id);
                $r2B = self::get_balance($r2);

                $r3 = self::get_boss_id($r2);
                $r3B = self::get_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'nsufficient Balance Your Balance');
                    Redirect::to('send_mnp.php');
                }
                
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }

                    //$operator = $operator;

                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom','0')";

                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom')";
                    
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                    Session::Flush('success', 'Your Requested Transaction of service (<b>MNP Recharge</b>) has been initiated.');
                    Redirect::to('send_mnp.php');

                } 
                
            } /*Retailer/Level1 Request End*/
            elseif ($userlevel == 1) /* Dealer/Level2 Request Start*/
            {
                $r1 = $us_id;
                $r1B = $mybalance;

                $r3 = self::get_boss_id($r1);
                $r3B = self::get_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);
				
                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('send_mnp.php');
                }               

                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";				
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }

                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom','0')";

                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','$r1','$r3','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                    Session::Flush('success', 'Your Requested Transaction of service (<b>MNP Recharge</b>) has been initiated.');
                    Redirect::to('send_mnp.php');
                }
            } /* Dealer/Level2 Request END*/            
			elseif ($userlevel == 2) /* DGM/Level3 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;

                $r4 = self::get_boss_id($r1);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('send_mnp.php');
                }
                
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";                    

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom','0')";

                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','$r1','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                    Session::Flush('success', 'Your Requested Transaction of service (<b>MNP Recharge</b>) has been initiated.');
                    Redirect::to('send_mnp.php');
                }
            } /* DGM/Level3 Request End*/		
			elseif ($userlevel == 3) /* Distributor/Level4 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;               

                $r5 = self::get_boss_id($r1);
                $r5B = self::get_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('send_mnp.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";                 

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom','0')";

                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','','$r1','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                   
                    Session::Flush('success', 'Your Requested Transaction of service (<b>MNP Recharge</b>) has been initiated.');
                    Redirect::to('send_mnp.php');
                }
            } /* Dealer Request End*/		
			elseif ($userlevel == 4) /* Subadmin/Level5 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;               

                //$r5 = self::get_boss_id($r1);
                //$r5B = self::get_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Balance');
                    Redirect::to('send_mnp.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $amount;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }


                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','0','$r1','$ip','$refrom','0')";

                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','','','$r1','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                    Session::Flush('success', 'Your Requested Transaction of service (<b>MNP Recharge</b>) has been initiated.');
                    Redirect::to('send_mnp.php');
                }
            } 	
			
			/* Subadmin/Level5 Request End*/
			
			
        }
    }



public static function make_m_banking($us_id, $num, $amount, $type, $m_banking_type, $ownpin, $userlevel)
    {

        $my_balance = self::get_bkash_balance($us_id);
        $db_pin = self::get_pin($us_id);
        $time = time();
        $ip=$_SERVER["REMOTE_ADDR"];
		$refrom='web';
		       
        //echo $db_pin."--".$ownpin; exit;
		
		
		$lowbalance = Mixedfn::low_balance($us_id);						
		$dlowbalance = Mixedfn::default_low_balance("mbank_low");

        $current_balance = $my_balance - $amount;


        /* Function for prevent of sql injection */
        global $connect;
        $num = stripcslashes($num);
        $amount = stripcslashes($amount);
        $ownpin= stripcslashes($ownpin);
        $num = mysqli_real_escape_string($connect, $num);
        $amount = mysqli_real_escape_string($connect, $amount);
        $ownpin = mysqli_real_escape_string($connect, $ownpin);
    
        /* End of Function for prevent of sql injection */

		if($current_balance <= $lowbalance)
		{								
			Session::Flush('error', 'You have a low balance.');
			Redirect::to('error.php');
		}
		elseif($current_balance <= $dlowbalance)
		{			                
			Session::Flush('error', 'Cannot transaction with your limit balance');
			Redirect::to('error.php');							
		}
        elseif(empty($num)) 
        {                
            Session::Flush('error', 'You must enter Mobile Number!');
            Redirect::to('error.php');
        } 
        elseif(!is_numeric($num)) 
        {                
            Session::Flush('error', 'Enter only Number!');
            Redirect::to('error.php');
        } 		
        elseif ($num == "")
        {
            Session::Flush('error', 'Invalid number. Please input a valid number.');
            Redirect::to('error.php');
        }
        elseif (strlen($num) < 11)
        {
            Session::Flush('error', 'Phone number must be 11-12 digit');
            Redirect::to('error.php');
        }
        elseif(empty($amount)) 
        {                
            Session::Flush('error', 'You must enter amount!');
            Redirect::to('error.php');
        } 
        elseif(!is_numeric($amount)) 
        {                
            Session::Flush('error', 'Amount only Number!');
            Redirect::to('error.php');
        }     
        elseif ($amount < 50 || $amount > 100000)
        {
            Session::Flush('error', 'Invalid Amount. Amount must be between 50 to 100000');
            Redirect::to('error.php');
        }
        elseif ($my_balance < $amount)
        {
            Session::Flush('error', 'Insufficient balance. Please contact with your boss!');
            Redirect::to('error.php');
        }
        elseif (self::mbank_getSendingRestriction($num) == 1)
        {
            Session::Flush('error', 'Wait for completed your already request in this number.');
            Redirect::to('error.php');
        }
        elseif(!is_numeric($ownpin)) 
        {                
            Session::Flush('error', 'Pincode allow only number!');
            Redirect::to('error.php');
        } 
        elseif (empty($ownpin)) 
        {               
            Session::Flush('error', 'You must enter Pincode!');
            Redirect::to('error.php'); 
        }   
        elseif ($db_pin != md5($ownpin))
        {
            Session::Flush('error', 'Pin code is invalid.');
            Redirect::to('error.php');
        }      
        else
        {
			/* Customer Request Start*/
            if ($userlevel == 0)
            {

                $r1 = $us_id;
                $r1B = $my_balance;

                $r2 = self::get_boss_id($us_id);
                $r2B = self::get_bkash_balance($r2);

                $r3 = self::get_boss_id($r2);
                $r3B = self::get_bkash_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_bkash_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_bkash_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $my_balance - $amount;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }

                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO bkash_send (id,user_id,company_name,ac_number,type,balance,charge,amount,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$m_banking_type','$num','$type','$amount','0','$amount',NOW(),'0','$time',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom')";

					//$query ="INSERT INTO bkash_send values(0,'$us_id','$num','$type','$m_banking_type','0','0','$amount','0','0','0',NOW(),'0','$time','0',CURDATE(),'$r1','$r2','$r3','$r4','$r5')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                     
                    Session::Flush('success', 'Your Requested Transaction of service (<b>'.$m_banking_type.'</b>) has been successfull.');
                    Redirect::to('mbank_history.php');

                } //Level1 Completed
                
            }
           /* Customer Request End*/
		   
		   /* Retailer Request Start*/		   
            elseif ($userlevel == 1)
            {
                $r1 = $us_id;
                $r1B = $my_balance;

                $r3 = self::get_boss_id($r1);
                $r3B = self::get_bkash_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_bkash_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_bkash_balance($r5);
				
                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }               
                else
                {

                    $r1Nb = $my_balance - $amount;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";					
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }

                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO bkash_send (id,user_id,company_name,ac_number,type,balance,charge,amount,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$m_banking_type','$num','$type','$amount','0','$amount',NOW(),'0','$time',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom')";
                    
                    //$query ="INSERT INTO bkash_send values(0,'$us_id','$num','$type','$m_banking_type','0','0','$amount','0','0','0',NOW(),'0','$time','0',CURDATE(),'0','$r1','$r3','$r4','$r5')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                    
                    Session::Flush('success', 'Your Requested Transaction of service (<b>'.$m_banking_type.'</b>) has been successfull.');
                    Redirect::to('mbank_history.php');
                }
            }
			/* Retailer Request End*/
			/* SR Request Start*/
            elseif ($userlevel == 2)
            {

                $r1 = $us_id;
                $r1B = $my_balance;

                $r4 = self::get_boss_id($r1);
                $r4B = self::get_bkash_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_bkash_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }
                
                else
                {

                    $r1Nb = $my_balance - $amount;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'"; 
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }

                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO bkash_send (id,user_id,company_name,ac_number,type,balance,charge,amount,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$m_banking_type','$num','$type','$amount','0','$amount',NOW(),'0','$time',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom')";

                    //$query ="INSERT INTO bkash_send values(0,'$us_id','$num','$type','$m_banking_type','0','0','$amount','0','0','0',NOW(),'0','$time','0',CURDATE(),'0','0','$r1','$r4','$r5')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }						
					Session::Flush('success', 'Your Requested Transaction of service (<b>'.$m_banking_type.'</b>) has been successfull.');
                    Redirect::to('mbank_history.php');
                }
            }
			
			/* SR Request End*/
			/* Distributor Request Start*/
			elseif ($userlevel == 3)
            {

                $r1 = $us_id;
                $r1B = $my_balance;
                
                $r5 = self::get_boss_id($r1);
                $r5B = self::get_bkash_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }
                
                else
                {

                    $r1Nb = $my_balance - $amount;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";  
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }


                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO bkash_send (id,user_id,company_name,ac_number,type,balance,charge,amount,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$m_banking_type','$num','$type','$amount','0','$amount',NOW(),'0','$time',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom')";

                    //$query ="INSERT INTO bkash_send values(0,'$us_id','$num','$m_banking_type','$type','0','0','$amount','0','0','0',NOW(),'0','$time','0',CURDATE(),'0','0','0','$r1','$r5')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }					
					Session::Flush('success', 'Your Requested Transaction of service (<b>'.$m_banking_type.'</b>) has been successfull.');
                    Redirect::to('mbank_history.php');
                }
            }
			
			/* Distributor Request End*/
			/* Subadmin Start*/
			elseif ($userlevel == 4)
            {

                $r1 = $us_id;
                $r1B = $my_balance;
                
                //$r5 = self::get_boss_id($r1);
                //$r5B = self::get_bkash_balance($r5);

                if ($r1B < $amount)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }
                
                else
                {

                    $r1Nb = $my_balance - $amount;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'"; 
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO bkash_send (id,user_id,company_name,ac_number,type,balance,charge,amount,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$m_banking_type','$num','$type','$amount','0','$amount',NOW(),'0','$time',CURDATE(),'0','0','0','0','$r1','$ip','$refrom')";

                    //$query ="INSERT INTO bkash_send values(0,'$us_id','$num','0','$m_banking_type','$type','0','0','$amount','0','0','0',NOW(),'0','$time','0',CURDATE(),'0','0','0','0','$r1')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }					
					Session::Flush('success', 'Your Requested Transaction of service (<b>'.$m_banking_type.'</b>) has been successfull.');
                    Redirect::to('mbank_history.php');
                }
            }
			
			/* Distributor Request End*/
			
			
			
        }
    }

   
    
/*Banking Request Start */   
public static function make_bank_request($us_id,$bank_name,$branch_name,$ac_type,$account_name,$account_number,$amount,$contact_number,$note,$ownpin,$userlevel)
{

    $my_balance = self::get_bkash_balance($us_id);
    $db_pin = self::get_pin($us_id);
    $time = time();
    $ip=$_SERVER["REMOTE_ADDR"];
    $refrom='apps';
           
    //echo $db_pin."--".$ownpin; exit;
    
    $lowbalance = Mixedfn::low_balance($us_id);						
    $dlowbalance = Mixedfn::default_low_balance("mbank_low");

    $current_balance = $my_balance - $amount;

    if($current_balance <= $lowbalance)
    {								
        Session::Flush('error', 'You have a low balance.');
        Redirect::to('send_bank.php');
    }
    elseif($current_balance <= $dlowbalance)
    {			                
        Session::Flush('error', 'Cannot transaction with your limit balance');
        Redirect::to('send_bank.php');							
    }
    elseif(empty($account_number)) 
    {                
        Session::Flush('error', 'You must enter Account Number!');
        Redirect::to('send_bank.php');
    } 
    elseif(!is_numeric($account_number)) 
    {                
        Session::Flush('error', 'Account Numbr allow only Number!');
        Redirect::to('send_bank.php');
    } 		
    elseif ($account_number == "")
    {
        Session::Flush('error', 'Invalid number. Please input a valid number.');
        Redirect::to('send_bank.php');
    }       
    elseif(empty($amount)) 
    {                
        Session::Flush('error', 'You must enter amount!');
        Redirect::to('send_bank.php');
    } 
    elseif(!is_numeric($amount)) 
    {                
        Session::Flush('error', 'Amount only Number!');
        Redirect::to('send_bank.php');
    }     
    elseif ($amount < 50 || $amount > 10000000)
    {
        Session::Flush('error', 'Invalid Amount. Amount must be between 50 to 10000000');
        Redirect::to('send_bank.php');
    }
    elseif ($my_balance < $amount)
    {
        Session::Flush('error', 'Insufficient balance. Please contact with your boss!');
        Redirect::to('send_bank.php');
    }
    elseif (self::mbank_getSendingRestriction($account_number) == 1)
    {
        Session::Flush('error', 'Wait for completed your already request in this number.');
        Redirect::to('send_bank.php');
    }
    elseif(!is_numeric($ownpin)) 
    {                
        Session::Flush('error', 'Pincode allow only number!');
        Redirect::to('send_bank.php');
    } 
    elseif (empty($ownpin)) 
    {               
        Session::Flush('error', 'You must enter Pincode!');
        Redirect::to('send_bank.php'); 
    }   
    elseif ($db_pin != md5($ownpin))
    {
        Session::Flush('error', 'Pin code is invalid.');
        Redirect::to('send_bank.php');
    }      
    else
    {
        /* Customer Request Start*/
        if ($userlevel == 0)
        {

            $r1 = $us_id;
            $r1B = $my_balance;

            $r2 = self::get_boss_id($us_id);
            $r2B = self::get_bkash_balance($r2);

            $r3 = self::get_boss_id($r2);
            $r3B = self::get_bkash_balance($r3);

            $r4 = self::get_boss_id($r3);
            $r4B = self::get_bkash_balance($r4);

            $r5 = self::get_boss_id($r4);
            $r5B = self::get_bkash_balance($r5);

            if ($r1B < $amount)
            {
                Session::Flush('error', 'Insufficient Your Balance');
                Redirect::to('send_bank.php');
            }                
            else
            {

                $r1Nb = $my_balance - $amount;
                $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";
                
                global $connect; 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }

                //$operator = self::get_company($num);

                $query  = "INSERT INTO bank_request (id,user_id,bank_name,branch_name,account_name,ac_number,type,balance,charge,amount,ContactNumber,note,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                $query .= "VALUES(0,'$us_id','$bank_name','$branch_name','$account_name','$account_number','$ac_type','$amount','0','$amount','$contact_number','$note',NOW(),'0','$time',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom')";                    
             
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }                     
                Session::Flush('success', 'Your Requested Transaction of service (<b> Bank Payment</b>) has been successfull.');
                Redirect::to('send_bank.php');

            } //Level1 Completed
            
        }
       /* Customer Request End*/
       
       /* Retailer Request Start*/		   
        elseif ($userlevel == 1)
        {
            $r1 = $us_id;
            $r1B = $my_balance;

            $r3 = self::get_boss_id($r1);
            $r3B = self::get_bkash_balance($r3);

            $r4 = self::get_boss_id($r3);
            $r4B = self::get_bkash_balance($r4);

            $r5 = self::get_boss_id($r4);
            $r5B = self::get_bkash_balance($r5);
            
            if ($r1B < $amount)
            {
                Session::Flush('error', 'Insufficient Your Balance');
                Redirect::to('send_bank.php');
            }               
            else
            {

                $r1Nb = $my_balance - $amount;
                $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";					
                
                global $connect; 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }

                //$operator = self::get_company($num);

                $query  = "INSERT INTO bank_request (id,user_id,bank_name,branch_name,account_name,ac_number,type,balance,charge,amount,ContactNumber,note,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                $query .= "VALUES(0,'$us_id','$bank_name','$branch_name','$account_name','$account_number','$ac_type','$amount','0','$amount','$contact_number','$note',NOW(),'0','$time',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom')";
              
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }                    
                Session::Flush('success', 'Your Requested Transaction of service (<b>Bank Payment</b>) has been successfull.');
                Redirect::to('send_bank.php');
            }
        }
        /* Retailer Request End*/
        /* SR Request Start*/
        elseif ($userlevel == 2)
        {

            $r1 = $us_id;
            $r1B = $my_balance;

            $r4 = self::get_boss_id($r1);
            $r4B = self::get_bkash_balance($r4);

            $r5 = self::get_boss_id($r4);
            $r5B = self::get_bkash_balance($r5);

            if ($r1B < $amount)
            {
                Session::Flush('error', 'Insufficient Your Balance');
                Redirect::to('send_bank.php');
            }
            
            else
            {

                $r1Nb = $my_balance - $amount;
                $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'"; 
                
                global $connect; 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }

                //$operator = self::get_company($num);

                $query  = "INSERT INTO bank_request (id,user_id,bank_name,branch_name,account_name,ac_number,type,balance,charge,amount,ContactNumber,note,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                $query .= "VALUES(0,'$us_id','$bank_name','$branch_name','$account_name','$account_number','$ac_type','$amount','0','$amount','$contact_number','$note',NOW(),'0','$time',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom')";
                  
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }						
                Session::Flush('success', 'Your Requested Transaction of service (<b>Banking Payemnt</b>) has been successfull.');
                Redirect::to('send_bank.php');
            }
        }
        
        /* SR Request End*/
        /* Distributor Request Start*/
        elseif ($userlevel == 3)
        {

            $r1 = $us_id;
            $r1B = $my_balance;
            
            $r5 = self::get_boss_id($r1);
            $r5B = self::get_bkash_balance($r5);

            if ($r1B < $amount)
            {
                Session::Flush('error', 'Insufficient Your Balance');
                Redirect::to('send_bank.php');
            }
            
            else
            {

                $r1Nb = $my_balance - $amount;
                $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";  
                
                global $connect; 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }


                //$operator = self::get_company($num);

                $query  = "INSERT INTO bank_request (id,user_id,bank_name,branch_name,account_name,ac_number,type,balance,charge,amount,ContactNumber,note,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                $query .= "VALUES(0,'$us_id','$bank_name','$branch_name','$account_name','$account_number','$ac_type','$amount','0','$amount','$contact_number','$note',NOW(),'0','$time',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom')";
         
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }					
                Session::Flush('success', 'Your Requested Transaction of service (<b>Banking Payment</b>) has been successfull.');
                Redirect::to('send_bank.php');
            }
        }
        
        /* Distributor Request End*/
        /* Subadmin Start*/
        elseif ($userlevel == 4)
        {

            $r1 = $us_id;
            $r1B = $my_balance;
            
            //$r5 = self::get_boss_id($r1);
            //$r5B = self::get_bkash_balance($r5);

            if ($r1B < $amount)
            {
                Session::Flush('error', 'Insufficient Your Balance');
                Redirect::to('send_bank.php');
            }
            
            else
            {

                $r1Nb = $my_balance - $amount;
                $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'"; 
                
                global $connect; 
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }
                
                //$operator = self::get_company($num);

                $query  = "INSERT INTO bank_request (id,user_id,bank_name,branch_name,account_name,ac_number,type,balance,charge,amount,ContactNumber,note,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                $query .= "VALUES(0,'$us_id','$bank_name','$branch_name','$account_name','$account_number','$ac_type','$amount','0','$amount','$contact_number','$note',NOW(),'0','$time',CURDATE(),'0','0','0','0','$r1','$ip','$refrom')";
         
                $result = mysqli_query($connect, $query);    
                if(!$result){
                    
                    die("Query Failed!" . mysqli_error($connect));
                }					
                Session::Flush('success', 'Your Requested Transaction of service (<b>Banking Payment</b>) has been successfull.');
                Redirect::to('send_bank.php');
            }
        }
        
        /* Distributor Request End*/
        
        
        
    }
}

/* Banking Request End*/
   
    
    
    /*Start of Package */
	
    public static function make_package_request($us_id, $num, $amount, $cost, $load_type, $ownpin, $userlevel)
    {
        // echo $us_id."-".$num."-".$amount."-".$load_type."-".$userlevel; exit;
        
        $mybalance = self::get_balance($us_id);
        $db_pin = self::get_pin($us_id);
        $time = time();
		
		
		$lowbalance = Mixedfn::low_balance($us_id);						
		$dlowbalance = Mixedfn::default_low_balance("recharge_low");

        $current_balance = $mybalance - $amount;

		$ip=$_SERVER["REMOTE_ADDR"];
		$refrom='web';


        /* Function for prevent of sql injection */
        global $connect;
        $num = stripcslashes($num);
        $amount = stripcslashes($amount);       
        $num = mysqli_real_escape_string($connect, $num);
        $amount = mysqli_real_escape_string($connect, $amount);       
    
        /* End of Function for prevent of sql injection */

		if($current_balance <= $lowbalance)
		{								
			Session::Flush('error', 'You have a low balance.');
			Redirect::to('error.php');
		}
		elseif($current_balance <= $dlowbalance)
		{			                
			Session::Flush('error', 'Cannot transaction with your limit balance');
			Redirect::to('error.php');							
		}
        elseif(!is_numeric($num)) 
        {                
            Session::Flush('error', 'Mobile Number allow only number!');
            Redirect::to('error.php');
        } 
        elseif (empty($num)) 
        {               
            Session::Flush('error', 'You must enter Mobile Number!');
            Redirect::to('error.php'); 
        }         	
        elseif ($num == "")
        {
            Session::Flush('error', 'Invalid number. Please input a valid number.');
            Redirect::to('error.php');
        }
        elseif (strlen($num) < 11)
        {
            Session::Flush('error', 'Phone number must be 11 digit!');
            Redirect::to('error.php');
        }        
        elseif ($mybalance < $amount)
        {
            Session::Flush('error', 'Insufficient balance. Please contact with your boss');
            Redirect::to('error.php');
        }
        elseif(!is_numeric($amount)) 
        {                
            Session::Flush('error', 'Amount allow only number!');
            Redirect::to('error.php');
        } 
        elseif (empty($amount)) 
        {               
            Session::Flush('error', 'You must enter amount!');
            Redirect::to('error.php'); 
        }          
        elseif (self::getSendingRestriction($num) == 1)
        {
            Session::Flush('error', 'You cannot request the same number until the old request is completed.');
            Redirect::to('error.php');
        }
        elseif(!is_numeric($ownpin)) 
        {                
            Session::Flush('error', 'Pincode allow only number!');
            Redirect::to('error.php');
        } 
        elseif (empty($ownpin)) 
        {               
            Session::Flush('error', 'You must enter Pincode!');
            Redirect::to('error.php'); 
        }    
        elseif ($db_pin != md5($ownpin))
        {
            Session::Flush('error', 'Pin code is invalid.');
            Redirect::to('error.php');
        }
        else
        {

			/* Retailer/Level1 Request Start*/
            if ($userlevel == 0)
            {

                $r1 = $us_id;
                $r1B = $mybalance;
				
				$r2 = self::get_boss_id($us_id);
                $r2B = self::get_balance($r2);

                $r3 = self::get_boss_id($r2);
                $r3B = self::get_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);
                
                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    $operator = self::get_company($num);
					
					$query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom','0')";
					
                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                    
                    Session::Flush('success', 'Your requested transaction of service (<b>Package</b>) has been initiated.');
                    Redirect::to('recharge_history.php');

                } 
                
            } /* Retailer/Level1 Request End*/	
            elseif ($userlevel == 1) /* Dealer/Level2 Request Start*/
            {
                $r1 = $us_id;
                $r1B = $mybalance;

                $r3 = self::get_boss_id($r1);
                $r3B = self::get_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);
				
                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }              

                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
					
                    
                    $operator = self::get_company($num);
					
                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom','0')";
					
					//$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','$r1','$r3','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed2!" . mysqli_error($connect));
                    }                    
                    Session::Flush('success', 'Your Requested transaction of service (<b>Package</b>) has been initiated.');
                    Redirect::to('recharge_history.php');
                }
            } /* Dealer/Level2 Request end*/			
            elseif ($userlevel == 2) /*DGM/Level3 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;

                $r4 = self::get_boss_id($r1);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                

                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                   

                    $operator = self::get_company($num);
					
                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom','0')";
                    
                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','$r1','$r4','$r5','$ip','$refrom')";
                     $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                      
                    Session::Flush('success', 'Your requested transaction of service (<b>Package</b>) has been initiated.');
                    Redirect::to('recharge_history.php');
                }
            } /* DGM/Level3 Request END*/		
			elseif ($userlevel == 3) /* Distributor/Level4 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;
                
                $r5 = self::get_boss_id($r1);
                $r5B = self::get_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    $operator = self::get_company($num);

                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom','0')";
                                    
                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','','$r1','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    Session::Flush('success', 'Your requested transaction of service (<b>Package</b>) has been initiated.');
                    Redirect::to('recharge_history.php');
                }
            } /* Distributor/Level4 Request End*/	
			elseif ($userlevel == 4) /* Sub Admin/Level5 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;
                
                //$r5 = self::get_boss_id($r1);
                //$r5B = self::get_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'"; 
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    $operator = self::get_company($num);

                    $query  = "INSERT INTO send_query (id,user_id,phone,load_type,balance,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom,api_reid)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','0','$r1','$ip','$refrom','0')";
                                    
                    //$query ="INSERT INTO send_query values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','','','$r1','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed2!" . mysqli_error($connect));
                    }
                    Session::Flush('success', 'Your Requested transaction of service (<b>Package</b>) has been initiated.');
                    Redirect::to('recharge_history.php');
                }
            }
				
			
			/* Sub Admin/Level5 Request End*/
			
        }
    }
/*End of Package */

  

    /*Start of Drive */
        
    public static function make_drive_request($us_id, $num, $amount, $cost, $load_type, $ownpin, $userlevel)
    {
        // echo $us_id."-".$num."-".$amount."-".$load_type."-".$userlevel; exit;
        
        $mybalance = self::get_balance($us_id);
        $db_pin = self::get_pin($us_id);
        $time = time();
        
        
        $lowbalance = Mixedfn::low_balance($us_id);						
		$dlowbalance = Mixedfn::default_low_balance("recharge_low");

        $current_balance = $mybalance - $amount;

        $ip=$_SERVER["REMOTE_ADDR"];
        $refrom='web';


        /* Function for prevent of sql injection */
        global $connect;
        $num = stripcslashes($num);
        $amount = stripcslashes($amount);       
        $num = mysqli_real_escape_string($connect, $num);
        $amount = mysqli_real_escape_string($connect, $amount);       

        /* End of Function for prevent of sql injection */

        if($current_balance <= $lowbalance)
        {								
            Session::Flush('error', 'You have a low balance.');
            Redirect::to('error.php');
        }
        elseif($current_balance <= $dlowbalance)
        {			                
            Session::Flush('error', 'Cannot transaction with your limit balance');
            Redirect::to('error.php');							
            }	

        elseif ($num == "")
        {
            Session::Flush('error', 'Invalid number. Please input a valid number.');
            Redirect::to('error.php');
        }
        elseif(!is_numeric($num)) 
        {                
            Session::Flush('error', 'Mobile Number allow only number!');
            Redirect::to('error.php');
        } 
        elseif (empty($num)) 
        {               
            Session::Flush('error', 'You must enter Consumer Number!');
            Redirect::to('error.php'); 
        }             
        elseif (strlen($num) < 11)
        {
            Session::Flush('error', 'Phone number must be 11 digit!');
            Redirect::to('error.php');
        }
        elseif (empty($amount)) 
        {               
            Session::Flush('error', 'You must enter Amount!');
            Redirect::to('error.php'); 
        }
        elseif(!is_numeric($amount)) 
        {                
            Session::Flush('error', 'Amount allow only number!');
            Redirect::to('error.php');
        }           
        elseif ($mybalance < $amount)
        {
            Session::Flush('error', 'Insufficient balance. Please contact with your boss');
            Redirect::to('error.php');
        }
        elseif (self::getSendingRestriction($num) == 1)
        {
            Session::Flush('error', 'You cannot request the same number until the old request is completed.');
            Redirect::to('error.php');
        }
        elseif (empty($ownpin)) 
        {               
            Session::Flush('error', 'You must enter Pincode!');
            Redirect::to('error.php'); 
        }
        elseif(!is_numeric($ownpin)) 
        {                
            Session::Flush('error', 'Pincode allow only number!');
            Redirect::to('error.php');
        }      
        elseif ($db_pin != md5($ownpin))
        {
            Session::Flush('error', 'Pin code is invalid.');
            Redirect::to('error.php');
        }
        else
        {

            /* Retailer/Level1 Request Start*/
            if ($userlevel == 0)
            {

                $r1 = $us_id;
                $r1B = $mybalance;
                
                $r2 = self::get_boss_id($us_id);
                $r2B = self::get_balance($r2);

                $r3 = self::get_boss_id($r2);
                $r3B = self::get_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);
                
                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    $operator = self::get_company($num);
                    
                    $query  = "INSERT INTO drive_request (id,user_id,phone,load_type,balance,cost,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount','$cost',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom')";
                    
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                    
                    Session::Flush('success', 'Your requested transaction of service (<b>Drive</b>) has been initiated.');
                    Redirect::to('drive_history.php');

                } 
                
            } /* Retailer/Level1 Request End*/	
            elseif ($userlevel == 1) /* Dealer/Level2 Request Start*/
            {
                $r1 = $us_id;
                $r1B = $mybalance;

                $r3 = self::get_boss_id($r1);
                $r3B = self::get_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);
                
                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }              

                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                    
                    $operator = self::get_company($num);
                    
                    $query  = "INSERT INTO drive_request (id,user_id,phone,load_type,balance,cost,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount','$cost',NOW(),'0','$time','$operator',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom')";
                    
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','$r1','$r3','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed2!" . mysqli_error($connect));
                    }                    
                    Session::Flush('success', 'Your Requested transaction of service (<b>Drive</b>) has been initiated.');
                    Redirect::to('drive_history.php');
                }
            } /* Dealer/Level2 Request end*/			
            elseif ($userlevel == 2) /*DGM/Level3 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;

                $r4 = self::get_boss_id($r1);
                $r4B = self::get_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                

                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                

                    $operator = self::get_company($num);
                    
                    $query  = "INSERT INTO drive_request (id,user_id,phone,load_type,balance,cost,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount','$cost',NOW(),'0','$time','$operator',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom')";
                    
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','$r1','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed2!" . mysqli_error($connect));
                    }                      
                    Session::Flush('success', 'Your requested transaction of service (<b>Drive</b>) has been initiated.');
                    Redirect::to('drive_history.php');
                }
            } /* DGM/Level3 Request END*/		
            elseif ($userlevel == 3) /* Distributor/Level4 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;
                
                $r5 = self::get_boss_id($r1);
                $r5B = self::get_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'";
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    $operator = self::get_company($num);

                    $query  = "INSERT INTO drive_request (id,user_id,phone,load_type,balance,cost,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount','$cost',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom')";
                                    
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','','$r1','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    Session::Flush('success', 'Your requested transaction of service (<b>Drive</b>) has been initiated.');
                    Redirect::to('drive_history.php');
                }
            } /* Distributor/Level4 Request End*/	
            elseif ($userlevel == 4) /* Sub Admin/Level5 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;
                
                //$r5 = self::get_boss_id($r1);
                //$r5B = self::get_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set balance2='$r1Nb' where id='$r1'"; 
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    $operator = self::get_company($num);

                    $query  = "INSERT INTO drive_request (id,user_id,phone,load_type,balance,cost,submitted_date,status,s_time,operator,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$num','$load_type','$amount','$cost',NOW(),'0','$time','$operator',CURDATE(),'0','0','0','0','$r1','$ip','$refrom')";
                                    
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','','','$r1','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    Session::Flush('success', 'Your Requested transaction of service (<b>Drive</b>) has been initiated.');
                    Redirect::to('drive_history.php');
                }
            }
                
            
            /* Sub Admin/Level5 Request End*/
            
            }
        } /*End of Drive */



    /*Start of BillPay */
        
    public static function make_billpay_request($us_id, $number, $amount, $cost, $type, $company, $company_type, $cs_name, $month, $contact_number, $ownpin, $userlevel)
    {
        // echo $us_id."-".$num."-".$amount."-".$load_type."-".$userlevel; exit;
        
        $mybalance = self::get_bkash_balance($us_id);
        $db_pin = self::get_pin($us_id);
        $time = time();
        
        
        $lowbalance = Mixedfn::low_balance($us_id);						
		$dlowbalance = Mixedfn::default_low_balance("mbank_low");

        $current_balance = $mybalance - $amount;

        $ip=$_SERVER["REMOTE_ADDR"];
        $refrom='web';


        $charge = 0;

        /* Start Bill Pay Charge*/
        $slot1_charge   = Mixedfn::billpaycharge("1","slot1");
        $slot2_charge   = Mixedfn::billpaycharge("1","slot2");
        $slot3_charge   = Mixedfn::billpaycharge("1","slot3");
        $slot4_charge   = Mixedfn::billpaycharge("1","slot4");
        /* Start Bill Pay Charge*/


        if ($amount >= 1 && $amount <= 400)
        {
            $charge = $slot1_charge;
        }
        if ($amount >= 401 && $amount <= 1500)
        {
            $charge = $slot2_charge;
        }
        if ($amount >= 1501 && $amount <= 5000)
        {
            $charge = $slot3_charge;
        }
        if ($amount >= 5001 && $amount <= 30000)
        {
            $charge = $slot4_charge;
        }

        $cost = $cost + $charge;


        if($current_balance <= $lowbalance)
        {								
            Session::Flush('error', 'You have a low balance.');
            Redirect::to('send_drive.php');
        }
        elseif($current_balance <= $dlowbalance)
        {			                
            Session::Flush('error', 'Cannot transaction with your limit balance');
            Redirect::to('send_drive.php');							
        }       
        elseif ($number == "")
        {
            Session::Flush('error', 'Invalid Consumer number. Please input a valid number.');
            Redirect::to('error.php');
        }
        elseif(!is_numeric($number)) 
        {                
            Session::Flush('error', 'Consumer Number allow only number!');
            Redirect::to('error.php');
        } 
        elseif (empty($number)) 
        {               
            Session::Flush('error', 'You must enter Consumer Number!');
                Redirect::to('error.php'); 
        }
        elseif (strlen($number) > 20)
        {
            Session::Flush('error', 'Consumer number needs to have less than 21 characters!');
            Redirect::to('error.php');
        }        
        elseif ($mybalance < $amount)
        {
            Session::Flush('error', 'Insufficient balance. Please contact with your boss');
            Redirect::to('error.php');
        }
        elseif (empty($amount)) 
        {               
            Session::Flush('error', 'You must enter amount!');
            Redirect::to('error.php'); 
        }
        elseif(!is_numeric($amount)) 
        {                
            Session::Flush('error', 'Amount allow only number!');
            Redirect::to('error.php');
        }
        elseif (strlen($amount) > 7)
        {
            Session::Flush('error', 'Amount needs to have less than 7 digit!');
            Redirect::to('error.php'); 
        }
        elseif (empty($cs_name)) 
        {               
            Session::Flush('error', 'You must enter your customer name!');
            Redirect::to('error.php');
        }
        elseif (strlen($cs_name) > 25)
        {
            Session::Flush('error', 'customer name needs to have less than 25 characters!');
            Redirect::to('error.php');
        }
        elseif (!preg_match("/^([a-zA-Z ])*$/", $cs_name))        
        {
            Session::Flush('error', 'customer name only allow letter and space!');
            Redirect::to('error.php');
        } 
        elseif (self::getSendingRestriction($number) == 1)
        {
            Session::Flush('error', 'You cannot request the same number until the old request is completed.');
            Redirect::to('error.php');
        }
        elseif ($db_pin != md5($ownpin))
        {
            Session::Flush('error', 'Pin code is invalid.');
            Redirect::to('error.php');
        }
        elseif (empty($month)) 
        {            
            Session::Flush('error', 'You must enter Billing Month and Year!');
            Redirect::to('error.php');
        }        
        elseif (empty($contact_number)) 
        {               
            Session::Flush('error', 'You must enter your customer phone number');
            Redirect::to('error.php');
        }
        elseif(!is_numeric($contact_number)) 
        {                
            Session::Flush('error', 'Phone Number allow only number!');
            Redirect::to('error.php');
        } 
        else
        {

            /* Retailer/Level1 Request Start*/
            if ($userlevel == 0)
            {

                $r1 = $us_id;
                $r1B = $mybalance;
                
                $r2 = self::get_boss_id($us_id);
                $r2B = self::get_bkash_balance($r2);

                $r3 = self::get_boss_id($r2);
                $r3B = self::get_bkash_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_bkash_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_bkash_balance($r5);
                
                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    //$operator = self::get_company($num);
                                       

                    $query  = "INSERT INTO billpay_request (id,user_id,type,company,ac_name,ac_number,ac_type,balance,cost,billing_month,contact_number,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$type','$company','$cs_name','$number','$company_type','$amount','$cost','$month','$contact_number',NOW(),'0','$time',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom')";
                    
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'$r1','$r2','$r3','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                    
                    Session::Flush('success', 'Your requested transaction of service (<b>Bill Pay</b>) has been initiated.');
                    Redirect::to('billpay_history.php');

                } 
                
            } /* Retailer/Level1 Request End*/	
            elseif ($userlevel == 1) /* Dealer/Level2 Request Start*/
            {
                $r1 = $us_id;
                $r1B = $mybalance;

                $r3 = self::get_boss_id($r1);
                $r3B = self::get_bkash_balance($r3);

                $r4 = self::get_boss_id($r3);
                $r4B = self::get_bkash_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_bkash_balance($r5);
                
                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }              

                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                    
                    //$operator = self::get_company($num);
                    
                    $query  = "INSERT INTO billpay_request (id,user_id,type,company,ac_name,ac_number,ac_type,balance,cost,billing_month,contact_number,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$type','$company','$cs_name','$number','$company_type','$amount','$cost','$month','$contact_number',NOW(),'0','$time',CURDATE(),'0','$r1','$r3','$r4','$r5','$ip','$refrom')";
                     
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','$r1','$r3','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                    
                    Session::Flush('success', 'Your Requested transaction of service (<b>Bill Pay</b>) has been initiated.');
                    Redirect::to('billpay_history.php');
                }
            } /* Dealer/Level2 Request end*/			
            elseif ($userlevel == 2) /*DGM/Level3 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;

                $r4 = self::get_boss_id($r1);
                $r4B = self::get_bkash_balance($r4);

                $r5 = self::get_boss_id($r4);
                $r5B = self::get_bkash_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                

                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";

                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                

                    //$operator = self::get_company($num);
                    
                    $query  = "INSERT INTO billpay_request (id,user_id,type,company,ac_name,ac_number,ac_type,balance,cost,billing_month,contact_number,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$type','$company','$cs_name','$number','$company_type','$amount','$cost','$month','$contact_number',NOW(),'0','$time',CURDATE(),'0','0','$r1','$r4','$r5','$ip','$refrom')";
                   
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','$r1','$r4','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                      
                    Session::Flush('success', 'Your requested transaction of service (<b>Bill Pay</b>) has been initiated.');
                    Redirect::to('billpay_history.php');
                }
            } /* DGM/Level3 Request END*/		
            elseif ($userlevel == 3) /* Distributor/Level4 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;
                
                $r5 = self::get_boss_id($r1);
                $r5B = self::get_bkash_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'";
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO billpay_request (id,user_id,type,company,ac_name,ac_number,ac_type,balance,cost,billing_month,contact_number,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$type','$company','$cs_name','$number','$company_type','$amount','$cost','$month','$contact_number',NOW(),'0','$time',CURDATE(),'0','0','0','$r1','$r5','$ip','$refrom')";
                                   
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','','$r1','$r5','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    Session::Flush('success', 'Your requested transaction of service (<b>Bill Pay</b>) has been initiated.');
                    Redirect::to('billpay_history.php');
                }
            } /* Distributor/Level4 Request End*/	
            elseif ($userlevel == 4) /* Sub Admin/Level5 Request Start*/
            {

                $r1 = $us_id;
                $r1B = $mybalance;
                
                //$r5 = self::get_boss_id($r1);
                //$r5B = self::get_balance($r5);

                if ($r1B < $cost)
                {
                    Session::Flush('error', 'Insufficient Your Balance');
                    Redirect::to('error.php');
                }                
                else
                {

                    $r1Nb = $mybalance - $cost;
                    $query = "UPDATE user set bkash_balance='$r1Nb' where id='$r1'"; 
                    
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    

                    //$operator = self::get_company($num);

                    $query  = "INSERT INTO billpay_request (id,user_id,type,company,ac_name,ac_number,ac_type,balance,cost,billing_month,contact_number,submitted_date,status,s_time,s_date,level1,level2,level3,level4,level5,ip,rfrom)";
                    $query .= "VALUES(0,'$us_id','$type','$company','$cs_name','$number','$company_type','$amount','$cost','$month','$contact_number',NOW(),'0','$time',CURDATE(),'0','0','0','0','$r1','$ip','$refrom')";
                                    
                    //$query ="INSERT INTO drive_request values(0,'$us_id','$num','$load_type','$amount',NOW(),'0','$time','$operator',CURDATE(),'','','','','$r1','$ip','$refrom')";
                    $result = mysqli_query($connect, $query);    
                    if(!$result){
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    Session::Flush('success', 'Your Requested transaction of service (<b>Bill Pay</b>) has been initiated.');
                    Redirect::to('billpay_history.php');
                }
            }
                
            
            /* Sub Admin/Level5 Request End*/
            
            }
        } /*End of BillPay */
    
}