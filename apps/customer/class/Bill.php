<?php
/*
 * Bill Pay property
 */
class Bill
{
      /* show all bill */
      public static function showBill()
      {
            return DB::getInstance()->get("SELECT * FROM billing_set WHERE status ='1' ORDER BY id DESC ")->result();
      }
      // get one bill info
      public static function get($index = '')
      {
            $data = [];
            # get veriable from url
            $billId = Input::get('billId');
            $sql    = "SELECT * FROM billing_set WHERE id = $billId";
            $res    = DB::getInstance()->get($sql)->result();
            foreach ($res as $result) {
                  $data['name'] = $result->name;
                  $data['code'] = $result->bill_code;
            }
            return $data[$index];
      }
      // get bill op name
      public static function name($code='')
      {
      	$name ='';
      	$sql = "SELECT * FROM billing_set WHERE bill_code='$code'";
      	$res = DB::getInstance()->get($sql)->result();
      	foreach ($res as $as){
      		$name .= $as->name; 
      	}
      	return $name;
      }

      // bill pay
      public static function pay()
      {
            $userId     = Session::get('userId');
            $pin        = Input::get('pincode'); 
            $moduleId   = '8';
            $ac_title   = Input::get('title');
            $pcode      = Input::get('pcode');
            $branch     = Input::get('branch');
            $number     = Input::get('number');
            $amount     = Input::get('amount');
            $note       = Input::get('note');
            $cost       = Input::get('cost');
			
			$bill_no   = Input::get('bill_no');
			$phone     = Input::get('phone');
			$month     = Input::get('month');
			$year      = Input::get('year');
			
            $accDigit   = strlen($number); 
            $minimun   = Module::info($moduleId, 'minAm');
            $maximum   = Module::info($moduleId, 'maxAm');
            $minLenght = Module::info($moduleId, 'minLen');
            $maxLenght = Module::info($moduleId, 'maxLen');
            // reseller daily limit
            $dailyLimit    = Verify::scure('11');
            $totalAmount   = Bank::totalAmount($moduleId, $userId, $pcode, $amount);
            $parent        = Reseller::info($userId, 'parent');
            $parentBalance = Transaction::parentBalance();
            $myBalance     = Transaction::accountBalance();

            if ($myBalance < $totalAmount) {
                  Session::Flush('error', 'You Have no Sufficient Balance.Available Only ' . $myBalance . '. Please Contact With Your reseller');
                  Redirect::to('newBill.php');
            } elseif ($minimun > $amount || $maximum < $amount) {
                  Session::Flush('error', 'Minimum Balance ' . $minimun . ' Maximum Balance ' . $maximum);
                   Redirect::to('newBill.php');
            } elseif ($minLenght > $accDigit || $maxLenght < $accDigit) {
                  Session::Flush('error', 'Minimum number ' . $minLenght . ' Maximum number ' . $maxLenght);
                  Redirect::to('newBill.php');
            } elseif ($dailyLimit < Transaction::daily()) {
                  echo 'Your Today Limit Over';
            } elseif ($parent != '1' && $parentBalance < $totalAmount) {
                  Session::Flush('error', 'Dear Client Contact With Parent reseller');
                  Redirect::to('newBill.php');
            } elseif (Module::isDeactive($moduleId) == 1) {
                  Session::Flush('error', Module::moduleName($moduleId) . ' Already Deactive');
                  Redirect::to('newBill.php');
            }else{
                  if (Verify::pin($pin) === true) {
                        $data = [
                              'userid'         => $userId,
                              'p_id'            => $parent,
                              'op'              => $pcode,
                              'ac'              => $number,
                              'ac_name'         => $ac_title,
                              'area'            => $branch,
                              'amount'          => $amount,
                              'cost'            => $totalAmount,
                              'trxid'           => '',
                              'msg'             => $note,
                              'description'     => '',
                              'last_bal'        => $myBalance,
                              'status'          => '0',
                              'idate'           => DateHelper::gtm(6, 'date'),
                              'lastupdate'      => DateHelper::gtm(6, 'format'),
							  'bill_no'         => $bill_no,
							  'phone'         	=> $phone,
							  'month'           => $month,
							  'year'            => $year,
                        ];
                        $transfer = DB::getInstance()->insert('bill_req', $data);
                        if ($transfer) {
                              Reseller::balanceUpdate(Session::get('userId'), $totalAmount);
                              if ($parent != '1') {
                                    Transaction::transBank($moduleId, $userId, $pcode, $amount);
                              }
                              # access log
                              $log = [
                                    'log_date'   => DateHelper::gtm('6', 'format'),
                                    'user'       => Session::get('userId'),
                                    'ip'         => $_SERVER['REMOTE_ADDR'],
                                    'access'     => 'Bill Pay',
                                    'createdres' => '',
                                    'payres'     => '',
                                    'amount'     => '',
                                    'service'    => $moduleId,
                                    'type'       => '',
                                    'pcode'      => $pcode,
                                    'amountreq'  => $amount,
                                    'page'       => basename($_SERVER["SCRIPT_FILENAME"], '.php'),
                                    'formre'     => Session::get('userId'),
                                    'updatedres' => '',
                                    'newres'     => '',
                                    'idate'      => DateHelper::gtm('6', 'date'),
                                    'number'     => $number,
                              ];
                              Log::requestLog($log);
                              Session::Flush('success', 'Bill Pay Successfully Complete');
                              Redirect::to('newBill.php');
                        } else {
                              Session::Flush('error', 'Dear Client Please Try Again');
                              Redirect::to('newBill.php');
                        }
                  } else {
                        Session::Flush('error', 'Dear Client Your Pin Incorrect');
                        Redirect::to('newBill.php');
                  }
            }
      }

      // bill pay get all history
      public static function billPayHistory()
      {
            // get filter variable
            $reseller = Input::get('resel');
            $from1    = Input::get('from1');
            $userId   = Session::get('userId'); 
            $to1      = Input::get('to1');
            if(!empty($reseller) && !empty($from1) && !empty($to1)){
                  $sql = "SELECT * FROM bill_req WHERE userid = '$reseller' AND idate BETWEEN  '$from1' AND '$to1' ORDER BY id DESC ";
            }elseif(!empty($from1) && !empty($to1)){
                  $sql = "SELECT * FROM bill_req WHERE userid = '$userId' AND idate BETWEEN  '$from1' AND '$to1' ORDER BY id DESC ";
            }elseif(!empty($reseller)){
                  $sql = "SELECT * FROM bill_req WHERE userid = '$reseller'  ORDER BY id DESC ";
            }else{
                  $sql = "SELECT * FROM bill_req WHERE userid = '$userId'  ORDER BY id DESC ";
            }
            return $sql;
      }

      // total bill 
      public static function totalBill()
      {
            return DB::getInstance()->get(self::billPayHistory())->Count();
      }
}

