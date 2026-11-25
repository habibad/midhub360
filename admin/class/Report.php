<?php

class Report
{
      public static function balance()
      {
            $p_id = Session::get('userId');
            $sql  = "SELECT * FROM reseller WHERE p_id = '$p_id' AND status = '1'";
            return DB::getInstance()->get($sql)->result();
      }

      /* get total  balance where sender by main admin */
      public static function totalBalance()
      {
            $p_id = Session::get('userId');
            $sql  = "SELECT SUM(balance) as total FROM reseller WHERE p_id='$p_id' AND status = '1'";
            $res  = DB::getInstance()->get($sql)->result();
            foreach ($res as $amount):
                  return $amount->total;
            endforeach;
      }
      /* end this function */

      /**
       *      get total receive payment
       */
      public static function receivePay()
      {
            $receiveAmount = '';
            $userId        = Session::get('userId');
            $sql           = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid= '$userId' AND type='Transfer' ORDER BY id DESC";
            $res           = DB::getInstance()->get($sql)->result();
            foreach ($res as $receive):
                  $receiveAmount = $receive->credit;
            endforeach;
            return $receiveAmount;
      }

      /* function total added by parent reseller */
      public static function totalAdded()
      {
            $addedAmount = '';
            $userId      = Session::get('userId');
            $sql         = "SELECT SUM(credit) as credit FROM pay_receive WHERE sender= '$userId' AND type='Transfer' ORDER BY id DESC";
            $res         = DB::getInstance()->get($sql)->result();
            foreach ($res as $added):
                  $addedAmount = $added->credit;
            endforeach;
            return $addedAmount;
      }
      /* end this function */

      /**
       *     function name sendReport()
       *      @param  $module | Which Module you want to see
       *      @return data array object
       */
      public static function topReport($moduleId = '')
      {
            $userId = Session::get('userId');
            $sql    = "SELECT * FROM sendflexi WHERE userid = '$userId' AND service = '$moduleId' ORDER BY id DESC LIMIT 10";
            return DB::getInstance()->get($sql)->result();
      }

      /**
       *      show all reseller flexi pending history
       */

      public static function pendingHistory()
      {
            $userId = Session::get('userId');
            # get all input filter data
            $number = Input::get('number');
            $resel  = Input::get('resel');
            $op     = Input::get('op');
            $status = Input::get('status');
            $from1  = Input::get('from1');
            $to1    = Input::get('to1');

            if ($number != '' && $resel != '' && $op != '' && $status != '' && $from1 != '' && $to1 != '') {
                  $sql = "SELECT * FROM sendflexi WHERE phone = '$number' AND userid = '$resel' AND service = '$op' AND status = '$status' AND idate between '$from1' AND '$to1' ORDER BY id DESC ";
            } elseif ($number != '' && $resel != '' && $op != '') {
                  $sql = "SELECT * FROM sendflexi WHERE phone = '$number' AND userid = '$resel' AND service = '$op' ORDER BY id DESC ";
            } elseif ($op != '' && $from1 != '' && $to1 != '' && $resel != '') {
                  $sql = "SELECT * FROM sendflexi WHERE service = '$op' AND idate between '$from1' AND '$to1' AND userid = '$resel' AND status ='0' ORDER BY id DESC ";
            } elseif ($op != '' && $from1 != '' && $to1 != '') {
                  $sql = "SELECT * FROM sendflexi WHERE service = '$op' AND idate between '$from1' AND '$to1' AND userid = '$userId' ORDER BY id DESC ";
            } elseif ($resel != '' && $number != '') {
                  $sql = "SELECT * FROM sendflexi WHERE  userid = '$resel' AND phone = '$number' AND status ='0' ORDER BY id DESC ";
            } elseif ($from1 != '' && $to1 != '') {
                  $sql = "SELECT * FROM sendflexi WHERE idate between '$from1' AND '$to1' AND userid='$userId' AND status ='0' ORDER BY id DESC ";
            } elseif ($from1 != '' && $to1 != '' && $resel != '') {
                  $sql = "SELECT * FROM sendflexi WHERE idate between '$from1' AND '$to1' AND userid='$resel' AND status ='0' ORDER BY id DESC ";
            } elseif ($number != '') {
                  $sql = "SELECT * FROM sendflexi WHERE phone = '$number' AND status ='0' ORDER BY id DESC ";
            } elseif ($resel != '') {
                  $sql = "SELECT * FROM sendflexi WHERE  userid = '$resel' AND status ='0' ORDER BY id DESC ";
            } else {
                  $sql = "SELECT * FROM sendflexi WHERE userid = '$userId' AND status ='0' ORDER BY id DESC ";
            }

            return $sql;
      }

      /* end filter */

      public static function countPending()
      {
            return DB::getInstance()->get(self::pendingHistory())->Count();
      }

      /* get single request */

      public static function singleRequest($requestId)
      {
            $sql = "SELECT * FROM sendflexi WHERE  id ='$requestId'";
            return DB::getInstance()->get($sql)->result();
      }

      public static function history()
      {
            // $servi  = Input::get('service');
            $userId = Session::get('us_id');
            # get all input filter data
            // $number = Input::get('number');
            // $resel  = Input::get('resel');
            // $op     = Input::get('op');
            // $status = Input::get('status');
            // $from1  = Input::get('from1');
            // $to1    = Input::get('to1');
			
			$sql = "SELECT * FROM send_query WHERE user_id='$userId' ORDER BY id DESC";
			
            // if ($number != '' && $resel != '' && $op != '' && $status != '' && $from1 != '' && $to1 != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE phone = '$number' AND userid = '$resel' AND service = '$op' AND status = '$status' AND idate between '$from1' AND '$to1' ORDER BY id DESC ";
            // } elseif ($op != '' && $from1 != '' && $to1 != '' && $resel != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE service = '$op' AND idate between '$from1' AND '$to1' AND userid = '$resel' ORDER BY id DESC ";
            // } elseif ($number != '' && $resel != '' && $op != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE phone = '$number' AND userid = '$resel' AND service = '$op' ORDER BY id DESC ";
            // } elseif ($op != '' && $from1 != '' && $to1 != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE service = '$op' AND idate between '$from1' AND '$to1' ORDER BY id DESC ";
            // } elseif ($resel != '' && $number != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE  userid = '$resel' AND phone = '$number' ORDER BY id DESC ";
            // } elseif ($from1 != '' && $to1 != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE idate between '$from1' AND '$to1' AND userid='$userId' ORDER BY id DESC ";
            // } elseif ($from1 != '' && $to1 != '' && $resel != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE idate between '$from1' AND '$to1' AND userid='$resel' ORDER BY id DESC ";
            // } elseif ($number != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE phone = '$number'  ORDER BY id DESC ";
            // } elseif ($resel != '') {
                  // $sql = "SELECT * FROM sendflexi WHERE  userid = '$resel'  ORDER BY id DESC ";
            // } else {
                  // if ($servi == 'all') {
                        // $sql = "SELECT * FROM sendflexi WHERE userid = '$userId' ORDER BY id DESC ";
                  // } else {
                        // $sql = "SELECT * FROM sendflexi WHERE userid = '$userId' AND service='$servi' ORDER BY id DESC ";
                  // }
            // }

            return $sql;
      }

      public static function allcustomer_list($userId)
      {
          //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
          $sql = "SELECT * FROM user ORDER BY id DESC ";
  
          return $sql;
      }

	  
	  public static function history_of_service($servi, $userId)
      {
            $sql = "SELECT * FROM sendflexi WHERE userid = '$userId' AND service='$servi' ORDER BY id DESC ";
            return $sql;
      }
	  
      public static function recharge_sim_msg($userId)
      {
          //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
          $sql = "SELECT * FROM reportserver ORDER BY id DESC ";
  
          return $sql;
      }

      public static function mb_sim_msg($userId)
      {
          //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
          $sql = "SELECT * FROM bkash_report ORDER BY id DESC ";
  
          return $sql;
      }



      public static function history_drive_userid($userId)
      {
          //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
          $sql = "SELECT * FROM drive_request ORDER BY id DESC ";
  
          return $sql;
      }

      public static function history_BillPay_userid($userId)
      {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM billpay_request ORDER BY id DESC ";

        return $sql;
      }

	public static function history_flexi_userid($userId)
      {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM send_query ORDER BY id DESC ";

        return $sql;
      }


      public static function pending_flexi_userid($userId)
      {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM send_query WHERE status='0' ORDER BY id DESC ";

        return $sql;
      }

      public static function waiting_flexi_userid($userId)
      {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM send_query WHERE status='4' ORDER BY id DESC ";

        return $sql;
      }

      public static function pending_drive_userid($userId)
      {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM drive_request WHERE status='0' ORDER BY id DESC ";

        return $sql;
      }

      public static function pending_mbank_userid($userId)
      {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM bkash_send WHERE status='0' ORDER BY id DESC ";

        return $sql;
      }

      public static function pending_billpay_userid($userId)
      {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM billpay_request WHERE status='0' ORDER BY id DESC ";

        return $sql;
      }
   
      public static function pending_balancereq_userid($userId)
      {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM add_fund WHERE status='0' ORDER BY id DESC ";

        return $sql;
      }
	
	public static function history_m_userid($userId)
    {
        //$sql = "SELECT * FROM send_query WHERE level4 ='$userId' ORDER BY id DESC LIMIT $limit OFFSET $offset;";
        $sql = "SELECT * FROM bkash_send ORDER BY id DESC ";

        return $sql;
    }
	
	  
	  public static function history_all($userId)
      {
          $sql = "SELECT * FROM send_query WHERE user_id ='$userId' ORDER BY id DESC ";
		
          return $sql;
      }

	public static function add_payment_history($userId)
      {
          $sql = "SELECT * FROM add_fund ORDER BY id DESC ";
		
          return $sql;
      }

	public static function payment_history($userId)
      {
          $sql = "SELECT * FROM reseller_balance WHERE load_by='$userId' ORDER BY id DESC ";
		
          return $sql;
      }

      public static function sim_list($userId)
      {
          $sql = "SELECT * FROM veri_number ORDER BY id DESC ";
		
          return $sql;
      }

      public static function package_list($userId)
      {
          $sql = "SELECT * FROM package ORDER BY id DESC ";
		
          return $sql;
      }

      public static function drive_list($userId)
      {
          $sql = "SELECT * FROM drive ORDER BY id DESC ";
		
          return $sql;
      }

      public static function sms_reply($userId)
      {
          $sql = "SELECT * FROM sms_reply ORDER BY id DESC ";
		
          return $sql;
      }

      public static function payment_history_mb($userId)
      {
          $sql = "SELECT * FROM bkash_balance WHERE load_by='$userId' ORDER BY id DESC ";
		
          return $sql;
      }

      
      public static function user_paymenthistory($userId)
      {
          $sql = "SELECT * FROM reseller_balance WHERE user_id='$userId' ORDER BY id DESC ";
		
          return $sql;
      }


      public static function user_paymenthistory_mb($userId)
      {
          $sql = "SELECT * FROM bkash_balance WHERE user_id='$userId' ORDER BY id DESC ";
		
          return $sql;
      }


      public static function received_history($userId)
      {
          $sql = "SELECT * FROM reseller_balance WHERE user_id='$userId' ORDER BY id DESC ";
		
          return $sql;
      }


      public static function received_history_mb($userId)
      {
          $sql = "SELECT * FROM bkash_balance WHERE user_id='$userId' ORDER BY id DESC ";
		
          return $sql;
      }


      /* count total history */
      public static function totalHistory()
      {
            return DB::getInstance()->get(self::history())->Count();
      }

      public static function add_totalPaymentHistory($userId)
      {
            return DB::getInstance()->get(self::add_payment_history($userId))->Count();
      }

	public static function totalPaymentHistory($userId)
      {
            return DB::getInstance()->get(self::payment_history($userId))->Count();
      }

      public static function totalSimlist($userId)
      {
            return DB::getInstance()->get(self::sim_list($userId))->Count();
      }

      public static function totalDrivelist($userId)
      {
            return DB::getInstance()->get(self::drive_list($userId))->Count();
      }

      public static function totalPackagelist($userId)
      {
            return DB::getInstance()->get(self::package_list($userId))->Count();
      }

      public static function totalSMSReply($userId)
      {
            return DB::getInstance()->get(self::sms_reply($userId))->Count();
      }
      
      public static function totalPaymentHistory_mb($userId)
      {
            return DB::getInstance()->get(self::payment_history_mb($userId))->Count();
      }

      public static function totalReceivedHistory($userId)
      {
            return DB::getInstance()->get(self::received_history($userId))->Count();
      }

      public static function totalUserPaymentHistory($userId)
      {
            return DB::getInstance()->get(self::user_paymenthistory($userId))->Count();
      }

      public static function totalUserPaymentHistory_mb($userId)
      {
            return DB::getInstance()->get(self::user_paymenthistory_mb($userId))->Count();
      }

      public static function totalReceivedHistory_mb($userId)
      {
            return DB::getInstance()->get(self::received_history_mb($userId))->Count();
      }


      public static function totalcustomerlist($userId)
      {
          return DB::getInstance()->get(self::allcustomer_list($userId))->Count();
      }

      public static function totalRechargeSimMsg($userId)
      {
          return DB::getInstance()->get(self::recharge_sim_msg($userId))->Count();
      }
      
      public static function totalMBSimMsg($userId)
      {
          return DB::getInstance()->get(self::mb_sim_msg($userId))->Count();
      }	


      public static function totalDriveHistoryById($userId)
      {
          return DB::getInstance()->get(self::history_drive_userid($userId))->Count();
      }	


      public static function totalBillPayHistoryById($userId)
    {
        return DB::getInstance()->get(self::history_BillPay_userid($userId))->Count();
    }	
    
    public static function totalFlexiHistoryById($userId)
    {
        return DB::getInstance()->get(self::history_flexi_userid($userId))->Count();
    }	
    
    public static function totalPendingFHistoryById($userId)
    {
        return DB::getInstance()->get(self::pending_flexi_userid($userId))->Count();
    }	
    public static function totalWaitingFHistoryById($userId)
    {
        return DB::getInstance()->get(self::waiting_flexi_userid($userId))->Count();
    }	

    public static function totalPendingDriveHistoryById($userId)
    {
        return DB::getInstance()->get(self::pending_drive_userid($userId))->Count();
    }	


    public static function totalPendingMbankHistoryById($userId)
    {
        return DB::getInstance()->get(self::pending_mbank_userid($userId))->Count();
    }	

    public static function totalPendingBillpayHistoryById($userId)
    {
        return DB::getInstance()->get(self::pending_billpay_userid($userId))->Count();
    }	

    public static function totalPendingAddBalreqHistoryById($userId)
    {
        return DB::getInstance()->get(self::pending_balancereq_userid($userId))->Count();
    }	

	public static function totalMbankiHistoryById($userId)
    {
        return DB::getInstance()->get(self::history_m_userid($userId))->Count();
    }

      /* today usage amount report */
      public static function todayUse()
      {
            $userId = Session::get('userId');
            $idate  = DateHelper::today();
            $sql    = "select sum(balance) as usageq, service from sendflexi where userid = '$userId' and idate ='$idate' group by service";
            $res    = DB::getInstance()->get($sql)->result();
            return $res;
      }

      /* total report */
      public static function totalReport()
      {
            $userId = Session::get('userId');
            $resel  = Input::get('resel');
            $from   = Input::get('from1');
            $to     = Input::get('to1');
            if ($resel != '' && $from != '' && $to != '') {
                  $sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$resel' AND idate between $from AND $to GROUP BY service";
            } elseif ($from != '' && $to != '') {
                  $sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$userId' AND idate between $from AND $to GROUP BY service";
            } elseif ($resel != '') {
                  $sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$resel' GROUP BY service";
            } else {
                  $sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$userId' GROUP BY service";
            }
            return DB::getInstance()->get($sql)->result();
      }
	  
	  
      /* total report by user */
      public static function totalReport_type($userId, $type)
      {
            if($type == "Daily")
			{
				$sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$userId' and submitted_date = CURDATE() GROUP BY service";
			}
			else if($type == "Weekly")
			{
				$sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$userId' and submitted_date >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY service";
			}
			else if($type == "Monthly")
			{
				$sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$userId' and submitted_date >= DATE(NOW()) - INTERVAL 1 MONTH GROUP BY service";
			}
			else if($type == "Yearly")
			{
				$sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$userId' and submitted_date >= DATE(NOW()) - INTERVAL 1 YEAR GROUP BY service";
			}
			else{
				$sql = "SELECT userid, service, SUM(balance) as bal, SUM(cost) as cost FROM sendflexi WHERE userid ='$userId' GROUP BY service";
			}
            
            return DB::getInstance()->get($sql)->result();
      }

      /* end total report */

      /* reseller total credit */
      public static function resellerCredit()
      {
            $userId = Session::get('userId');
            $resel  = Input::get('resel');
            $from   = Input::get('from1');
            $to     = Input::get('to1');
            if($resel != '' && $from != '' && $to != ''){
                  $sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$resel' AND  idate between '$from' AND '$to' ";
            }elseif($from != '' && $to != ''){
                  $sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$userId' AND  idate between '$from' AND '$to' ";
            }elseif($resel !=''){
                  $sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$resel' ";
            }else{
                   $sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$userId' ";
            }

            $data = DB::getInstance()->get($sql)->result();
            foreach ($data as $cd){
                  return $cd->credit;
            }
      }
	  
	  public static function resellerCredit_user($userId, $type)
      {
            
			if($type == "Daily")
			{
				$sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$userId' and idate = CURDATE()";
			}
			else if($type == "Weekly")
			{
				$sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$userId' and idate >= DATE(NOW()) - INTERVAL 7 DAY";
			}
			else if($type == "Monthly")
			{
				$sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$userId' and idate >= DATE(NOW()) - INTERVAL 1 MONTH";
			}
			else if($type == "Yearly")
			{
				$sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$userId' and idate >= DATE(NOW()) - INTERVAL 1 YEAR";
			}
			else{
				$sql = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$userId'";
			}

            $data = DB::getInstance()->get($sql)->result();
            foreach ($data as $cd){
                  return $cd->credit;
            }
      }

      /* reseller total credit */
      public static function resellerDebit()
      {
            $userId = Session::get('userId');
            $resel  = Input::get('resel');
            $from   = Input::get('from1');
            $to     = Input::get('to1');
            if($resel != '' && $from != '' && $to != ''){
                  $sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$resel' AND  idate between '$from' AND '$to' ";
            }elseif($from != '' && $to != ''){
                  $sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$userId' AND  idate between '$from' AND '$to' ";
            }elseif($resel !=''){
                  $sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$resel' ";
            }else{
                   $sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$userId' ";
            }

            $data = DB::getInstance()->get($sql)->result();
            foreach ($data as $cd){
                  return $cd->debit;
            }
      }
	  
	  
	  public static function flexiExpenseReport($us_id, $operator, $type = "")
    {
        if ($type == "Today")
        {
            $sql = "SELECT *, IFNULL(SUM(balance), 0) as total_balance FROM send_query WHERE s_date = CURDATE() and user_id='$us_id' AND status='1' and operator ='$operator'";
        }
        else if ($type == "Weekly")
        {
            $sql = "SELECT *, IFNULL(SUM(balance), 0) as total_balance FROM send_query WHERE user_id='$us_id' AND status='1' and operator ='$operator' and s_date >= DATE(NOW()) - INTERVAL 7 DAY";
        }
        else if ($type == "Monthly")
        {
            $sql = "SELECT *, IFNULL(SUM(balance), 0) as total_balance FROM send_query WHERE user_id='$us_id' AND status='1' and operator ='$operator' and s_date >= DATE(NOW()) - INTERVAL 1 MONTH";
        }
        else if ($type == "Yearly")
        {
            $sql = "SELECT *, IFNULL(SUM(balance), 0) as total_balance FROM send_query WHERE user_id='$us_id' AND status='1' and operator ='$operator' and s_date >= DATE(NOW()) - INTERVAL 1 YEAR";
        }
        else if ($type == "LifeTime")
        {
            $sql = "SELECT *, IFNULL(SUM(balance), 0) as total_balance FROM send_query WHERE user_id='$us_id' AND status='1' and operator ='$operator'";
        }
		else
        {
            $sql = "SELECT *, IFNULL(SUM(balance), 0) as total_balance FROM send_query WHERE s_date = CURDATE() and user_id='$us_id' AND status='1' and operator ='$operator'";
        }
        
        return $sql;
        // $data = DB::getInstance()->get($sql)->result();
        // foreach ($data as $cd)
        // {
        //     return $cd->balance;
        // }
    }
   
	  
	public static function mobileBankingExpenseReport($us_id, $bank_name, $type = "")
    {
        if ($type == "Today")
        {
            $sql = "SELECT *, IFNULL(SUM(amount), 0) as total_balance FROM bkash_send WHERE s_date = CURDATE() and user_id='$us_id' AND status='1' and bank_name ='$bank_name'";
        }
        else if ($type == "Weekly")
        {
            $sql = "SELECT *, IFNULL(SUM(amount), 0) as total_balance FROM bkash_send WHERE user_id='$us_id' AND status='1' and bank_name ='$bank_name' and s_date >= DATE(NOW()) - INTERVAL 7 DAY";
        }
        else if ($type == "Monthly")
        {
            $sql = "SELECT *, IFNULL(SUM(amount), 0) as total_balance FROM bkash_send WHERE user_id='$us_id' AND status='1' and bank_name ='$bank_name' and s_date >= DATE(NOW()) - INTERVAL 1 MONTH";
        }
        else if ($type == "Yearly")
        {
            $sql = "SELECT *, IFNULL(SUM(amount), 0) as total_balance FROM bkash_send WHERE user_id='$us_id' AND status='1' and bank_name ='$bank_name' and s_date >= DATE(NOW()) - INTERVAL 1 YEAR";
        }
        else if ($type == "LifeTime")
        {
            $sql = "SELECT *, IFNULL(SUM(amount), 0) as total_balance FROM bkash_send WHERE user_id='$us_id' AND status='1' and bank_name ='$bank_name'";
        }
		else
        {
            $sql = "SELECT *, IFNULL(SUM(amount), 0) as total_balance FROM bkash_send WHERE s_date = CURDATE() and user_id='$us_id' AND status='1' and bank_name ='$bank_name'";
        }
        
        // echo $sql;
        return $sql;
        // $data = DB::getInstance()->get($sql)->result();
        // foreach ($data as $cd)
        // {
        //     return $cd->balance;
        // }
    }
      
	   public static function getAllMobileBanking()
    {
        $sql = "SELECT DISTINCT(bank_name) FROM bkash_send";
        
        return $sql;
    } 
	  
	  /* reseller total credit by user */
      public static function resellerDebit_user($userId, $type)
      {
            if($type == "Daily")
			{
				$sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$userId' and idate = CURDATE()";
			}
			else if($type == "Weekly")
			{
				$sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$userId' and idate >= DATE(NOW()) - INTERVAL 7 DAY";
			}
			else if($type == "Monthly")
			{
				$sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$userId' and idate >= DATE(NOW()) - INTERVAL 1 MONTH";
			}
			else if($type == "Yearly")
			{
				$sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$userId' and idate >= DATE(NOW()) - INTERVAL 1 YEAR";
			}
			else{
				$sql = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$userId'";
			}

            $data = DB::getInstance()->get($sql)->result();
            foreach ($data as $cd){
                  return $cd->debit;
            }
      }

}
