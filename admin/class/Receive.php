<?php

class Receive
{
      public static function receivePayment()
      {
            $userId = Session::get('userId');
            $type   = Input::get('type');
            $from1  = Input::get('from1');
            $to1    = Input::get('to1');

            if($type !='' && $from1 !='' && $to1 != ''){
            	$sql ="SELECT * FROM pay_receive WHERE userid = '$userId' AND type ='$type' AND idate between '$from1' AND '$to1' ORDER BY id DESC ";
            }elseif($from1 !='' && $to1 != ''){
            	$sql ="SELECT * FROM pay_receive WHERE userid = '$userId'  AND idate between '$from1' AND '$to1' ORDER BY id DESC ";
            }elseif($type !=''){
            	$sql ="SELECT * FROM pay_receive WHERE userid = '$userId' AND type ='$type' ORDER BY id DESC ";
            }else{
            	$sql ="SELECT * FROM pay_receive WHERE userid = '$userId' ORDER BY id DESC ";
            }

            return $sql;
      }
	  
	  public static function receivePayment_user($userId)
      {
            $sql ="SELECT * FROM pay_receive WHERE userid = '$userId' ORDER BY id DESC ";
            return $sql;
      }
      // end this function 

      public static function receiveTotal()
      {
      	return DB::getInstance()->get(self::receivePayment())->Count();
      }
	  
	  public static function receiveTotal_user($userId)
      {
      	return DB::getInstance()->get(self::receivePayment_user($userId))->Count();
      }

      public static function totalReceive()
      {
      	$credit = '';
      	$userId = Session::get('userId');
      	$sql  = "SELECT SUM(credit) as credit FROM pay_receive WHERE userid = '$userId' AND type = 'Transfer'";
      	$rs = DB::getInstance()->get($sql)->result();
      	foreach ($rs as $total){
      		$credit = $total->credit;
      	}
      	return $credit;
      }

      public static function totalReturn()
      {
      	$debit = '';
      	$userId = Session::get('userId');
      	$sql  = "SELECT SUM(debit) as debit FROM pay_receive WHERE userid = '$userId' AND type = 'Return'";
      	$rs = DB::getInstance()->get($sql)->result();
      	foreach ($rs as $total){
      		$debit = $total->debit;
      	}
      	return $debit;
      }
}
