<?php

class Transaction
{
      public static function accountBalance()
      {
            return Reseller::info(Session::get('userId'), 'balance');
      }

      public static function parentBalance()
      {
            $parent = Reseller::info(Session::get('userId'), 'parent');
            return Reseller::info($parent, 'balance');
      }

      public static function daily()
      {
            $todayTotal = '';
            $today      = DateHelper::gtm('6', 'date');
            $userId     = Session::get('userId');
            $sql        = "SELECT SUM(balance) as today FROM reseller WHERE idate = '$today' WHERE userid = $userId";
            if (DB::getInstance()->get($sql)->Count() > 0) {
                  foreach (DB::getInstance()->get($sql)->result() as $total):
                        $todayTotal .= $total->today();
                  endforeach;
            } else {
                  $todayTotal .= 0;
            }
            return $todayTotal;
      }

      /**
       *      for reseller label 4
       */

      public static function trans($serviceid, $currentUser, $amount, $type, $preffix)
      {
            $parent       = Reseller::info($currentUser, 'parent');
            $CustomerType = Reseller::type($currentUser);
            if($CustomerType == 'reseller5'){
                  $subadmin = Reseller::info($currentUser, 'parent'); // sub admin
            }elseif ($CustomerType == 'reseller4') {
                  $parent45 = Reseller::info($currentUser, 'parent'); //label 5
            } elseif ($CustomerType == 'reseller3') {
                  $parent34 = Reseller::info($currentUser, 'parent'); // label 4
                  $parent35 = Reseller::info($parent34, 'parent'); //label 5
            } elseif ($CustomerType == 'reseller2') {
                  $parent23 = Reseller::info($currentUser, 'parent'); // label 3
                  $parent24 = Reseller::info($parent23, 'parent'); // label 4
                  $parent25 = Reseller::info($parent24, 'parent'); // label 5
            } elseif ($CustomerType == 'reseller1') {
                  $parent12 = Reseller::info($currentUser, 'parent'); // label 2
                  $parent13 = Reseller::info($parent12, 'parent'); //label 3
                  $parent14 = Reseller::info($parent13, 'parent'); // label 4
                  $parent15 = Reseller::info($parent14, 'parent'); // label 5
            }
            if ($parent != '1') {
                  if($CustomerType == 'reseller5'){
                        self::rateTrans($subadmin, $serviceid, $amount, $type, $preffix);
                  }elseif ($CustomerType == 'reseller4') {
                        self::rateTrans($parent45, $serviceid, $amount, $type, $preffix);
                  } elseif ($CustomerType == 'reseller3') {
                        self::rateTrans($parent34, $serviceid, $amount, $type, $preffix);
                        self::rateTrans($parent35, $serviceid, $amount, $type, $preffix);
                  } elseif ($CustomerType == 'reseller2') {
                        self::rateTrans($parent23, $serviceid, $amount, $type, $preffix);
                        self::rateTrans($parent24, $serviceid, $amount, $type, $preffix);
                        self::rateTrans($parent25, $serviceid, $amount, $type, $preffix);
                  } elseif ($CustomerType == 'reseller1') {
                        self::rateTrans($parent12, $serviceid, $amount, $type, $preffix);
                        self::rateTrans($parent13, $serviceid, $amount, $type, $preffix);
                        self::rateTrans($parent14, $serviceid, $amount, $type, $preffix);
                        self::rateTrans($parent15, $serviceid, $amount, $type, $preffix);
                  }
            }
      }

      public static function rateTrans($userId, $serviceid, $amount, $type, $preffix)
      {
            $mainRate       = '';
            $mainComm       = '';
            $mainCharge     = '';
            $accountBalance = '';
            $rate           = '';
            $comm           = '';
            $charge         = '';
            $sql            = "SELECT * FROM rate_module WHERE userid = '$userId' AND service ='$serviceid' AND type ='$type' AND prefix ='$preffix'";
            $Res            = DB::getInstance()->get($sql)->result();
            foreach ($Res as $rate1) {
                  $pcode  = $rate1->pcode;
                  $rate   = $rate1->rate;
                  $comm   = $rate1->comm;
                  $charge = $rate1->charge;
            }
            $mainRate   = $rate * $amount;
            $mainComm   = $amount * $comm / 100;
            $mainCharge = $amount * $charge / 100;

            $totalSend      = ($mainRate + $mainCharge) - $mainComm;
            $preBalance     = Reseller::info($userId, 'balance');
            $accountBalance = $preBalance - $totalSend;

            $data = [
                  'userid'         => $userId,
                  'descr'          => '',
                  'oldbal'         => $preBalance,
                  'debit'          => $totalSend,
                  'credit'         => '',
                  'service'        => $serviceid,
                  'accountbalance' => $accountBalance,
                  'type'           => 'Minus',
                  'date'           => DateHelper::gtm('6', 'date'),
                  'time'           => DateHelper::timeToday(),
            ];
            $insert = DB::getInstance()->insert('trans', $data);
            Reseller::balanceUpdate($userId, $totalSend);
      }

      public static function checkInterval($mumber = '')
      {

            $selectMax = "SELECT MAX(id) as id, count(*) as cnt FROM sendflexi WHERE phone = '$mumber'";
            foreach (DB::getInstance()->get($selectMax)->result() as $id){
                  $maxId = $id->id;
                  $con   = $id->cnt;
            }
            if ($con > 0) {
                  $selectTime = "SELECT * FROM sendflexi WHERE id = $maxId";
                  foreach (DB::getInstance()->get($selectTime)->result() as $time1) {
                        $timedate = $time1->submitted_date;
                  }
                  $request_date    = DateHelper::gtm(6, 'time');

                  $a    = strtotime($request_date);
                  $b    = strtotime($timedate);
                  $time = $a - $b;

                  $dateDiff    = $a - $b;
                  $fullDays    = floor($dateDiff / (60 * 60 * 24));
                  $fullHours   = floor(($dateDiff - ($fullDays * 60 * 60 * 24)) / (60 * 60));
                  $fullMinutes = floor(($dateDiff - ($fullDays * 60 * 60 * 24) - ($fullHours * 60 * 60)) / 60);
                  $fullSec     = $time;
                  return $fullSec;
            } else {
                  return 0;
            }
      }

      public static function sendFlexi($data = [])
      {
            $insert = DB::getInstance()->insert('sendflexi', $data);
            return $insert ? true : false;
      }
   public static function bill($data = [])
      {
            $insert = DB::getInstance()->insert('bill_req', $data);
            return $insert ? true : false;
      }
	  
	  
      // bank trans rate
      public static function bankTransRate($moduleId, $userId, $bankId, $amount)
      {
            $mainRate       = '';
            $mainComm       = '';
            $mainCharge     = '';
            $accountBalance = '';
            $rate           = '';
            $comm           = '';
            $charge         = '';
            $sql            = "SELECT * FROM rate_module WHERE userid = '$userId' AND service ='$moduleId' AND pcode ='$bankId'";
            $Res            = DB::getInstance()->get($sql)->result();
            foreach ($Res as $rate1) {
                  $pcode  = $rate1->pcode;
                  $rate   = $rate1->rate;
                  $comm   = $rate1->comm;
                  $charge = $rate1->charge;
            }
            $mainRate   = $rate * $amount;
            $mainComm   = $amount * $comm / 100;
            $mainCharge = $amount * $charge / 100;

            $totalSend      = ($mainRate + $mainCharge) - $mainComm;
            $preBalance     = Reseller::info($userId, 'balance');
            $accountBalance = $preBalance - $totalSend;

            $data = [
                  'userid'         => $userId,
                  'descr'          => '',
                  'oldbal'         => $preBalance,
                  'debit'          => $totalSend,
                  'credit'         => '',
                  'service'        => $moduleId,
                  'accountbalance' => $accountBalance,
                  'type'           => 'Minus',
                  'date'           => DateHelper::gtm('6', 'date'),
                  'time'           => DateHelper::timeToday(),
            ];
            $insert = DB::getInstance()->insert('trans', $data);
            Reseller::balanceUpdate($userId, $totalSend);
      }

      // bank trans rate data
      public static function transBank($moduleId, $userId, $bankId, $amount)
      {
            $parent       = Reseller::info($userId, 'parent');
            $CustomerType = Reseller::type($userId);
            if($CustomerType == 'reseller5'){
                  $subadmin = Reseller::info($userId, 'parent'); // sub admin
            }elseif ($CustomerType == 'reseller4') {
                  $parent45 = Reseller::info($userId, 'parent'); //label 5
            } elseif ($CustomerType == 'reseller3') {
                  $parent34 = Reseller::info($userId, 'parent'); // label 4
                  $parent35 = Reseller::info($parent34, 'parent'); //label 5
            } elseif ($CustomerType == 'reseller2') {
                  $parent23 = Reseller::info($userId, 'parent'); // label 3
                  $parent24 = Reseller::info($parent23, 'parent'); // label 4
                  $parent25 = Reseller::info($parent24, 'parent'); // label 5
            } elseif ($CustomerType == 'reseller1') {
                  $parent12 = Reseller::info($userId, 'parent'); // label 2
                  $parent13 = Reseller::info($parent12, 'parent'); //label 3
                  $parent14 = Reseller::info($parent13, 'parent'); // label 4
                  $parent15 = Reseller::info($parent14, 'parent'); // label 5
            }
            if ($parent != '1') {
                  if($CustomerType == 'reseller5'){
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                  }elseif ($CustomerType == 'reseller4') {
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                  } elseif ($CustomerType == 'reseller3') {
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                  } elseif ($CustomerType == 'reseller2') {
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                  } elseif ($CustomerType == 'reseller1') {
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                        self::bankTransRate($moduleId, $userId, $bankId, $amount);
                  }
            }
      }

      public static function topLebel($userId, $amount)
      {
            $parent       = Reseller::info($userId, 'parent');
            $CustomerType = Reseller::type($userId);
            if($CustomerType == 'reseller5'){
                  $subadmin = Reseller::info($userId, 'parent'); // sub admin
                  $balanceSubAdmin = Reseller::info($subadmin, 'balance');
                  if($amount > $balanceSubAdmin){ return false;}else{return true;}
            }elseif ($CustomerType == 'reseller4') {
                  $parent45 = Reseller::info($userId, 'parent'); //label 5
                  $balanceparent45 = Reseller::info($parent45, 'balance');
                  if($amount > $balanceparent45){ return false;}else{return true;}
            } elseif ($CustomerType == 'reseller3') {
                  $parent34 = Reseller::info($userId, 'parent'); // label 4

                  $balanceparent34 = Reseller::info($parent34, 'balance');
                  //if( $balanceparent34 < $amount){ return false;}else{return true;}
                  $parent35 = Reseller::info($parent34, 'parent'); //label 5
                  $balanceparent35 = Reseller::info($parent35, 'balance');
                  if( $amount > $balanceparent35 ||  $amount > $balanceparent34 ){ return false;}else{return true;}
            } elseif ($CustomerType == 'reseller2') {
                  $parent23 = Reseller::info($userId, 'parent'); // label 3

                  $balanceparent23 = Reseller::info($parent23, 'balance');
                  

                  $parent24 = Reseller::info($parent23, 'parent'); // label 4

                  $balanceparent24 = Reseller::info($parent24, 'balance');
               

                  $parent25 = Reseller::info($parent24, 'parent'); // label 5

                  $balanceparent25 = Reseller::info($parent25, 'balance');
                  if( $amount > $balanceparent25 || $amount > $balanceparent24 || $amount > $balanceparent23){ return false;}else{return true;}
            } elseif ($CustomerType == 'reseller1') {
                  $parent12 = Reseller::info($userId, 'parent'); // label 2
                  $balanceparent12 = Reseller::info($parent12, 'balance');
                  $parent13 = Reseller::info($parent12, 'parent'); //label 3

                  $balanceparent13 = Reseller::info($parent13, 'balance');
                  

                  $parent14 = Reseller::info($parent13, 'parent'); // label 4

                  $balanceparent14 = Reseller::info($parent14, 'balance');

                  $parent15 = Reseller::info($parent14, 'parent'); // label 5
                  $balanceparent15 = Reseller::info($parent15, 'balance');
                  if( $amount > $balanceparent15 || $amount > $balanceparent14 || $amount > $balanceparent13 || $amount > $balanceparent12){ return false;}else{return true;}
            }
      }
}
