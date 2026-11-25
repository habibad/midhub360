<?php
class Bulk
{
      public static function BulkListAdd()
      {
            $groupName = Xss::xssClean(Input::get('title'));
            $data      = [
                  'user_id'    => Session::get('userId'),
                  'group_name' => $groupName,
                  'status'     => '1',
                  'date'       => DateHelper::gtm('6', 'date'),
            ];
            $in = DB::getInstance()->insert('bulkflexi', $data);
            if ($in) {
                  Redirect::to('bulkflexi_view.php?gid=' . $in);
            }
      }

      public static function gName($gid)
      {
            $gName  = '';
            $userId = Session::get('userId');
            $sql    = "SELECT * FROM bulkflexi WHERE id = $gid AND user_id ='$userId'";
            $res    = DB::getInstance()->get($sql)->result();
            foreach ($res as $group):
                  $gName .= $group->group_name;
            endforeach;
            return $gName;
      }

      public static function addBulkNumber()
      {
            $badNumber   = [];
            $nullAmount  = [];
            $duplicate   = [];
            $gid         = Input::get('gid');
            $numbers     = Input::get('number');
            $amounts     = Input::get('amount');
            $types       = Input::get('type');
            $enable      = Input::get('enable');
            $totalNumber = count($numbers);
            $userId      = Session::get('userId');
            $parent      = Reseller::info(Session::get('userId'), 'parent');
            if ($totalNumber > 0) {
                  for ($i = 0; $i < $totalNumber; $i++) {
                        if (!empty($numbers[$i])) {
                              $prefix      = substr($numbers[$i], 0, 3);
                              $phoneLenght = strlen($numbers[$i]);
                              if (!$prefix = '017' ||
                                    !$prefix = '018' ||
                                    !$prefix = '016' ||
                                    !$prefix = '019' ||
                                    !$prefix = '015' ||
                                    !$prefix = '011' ||
                                    11 > $phoneLenght ||
                                    16 < $phoneLenght
                              ) {
                                    $badNumber[] = $numbers[$i];
                              } else {
                                    if ($amounts[$i] < 10 || $amounts[$i] > 1000) {
                                          $nullAmount[] = $numbers[$i];
                                    } else {
                                          if (self::numberCheck($numbers[$i], $gid, $userId) === true) {
                                                $duplicate[] = $numbers[$i];
                                          } else {
                                                $data = [
                                                      'p_id'       => $parent,
                                                      'user_id'    => $userId,
                                                      'admin_id'   => '',
                                                      'mobile'     => $numbers[$i],
                                                      'amount'     => $amounts[$i],
                                                      'type'       => $types[$i],
                                                      'gid'        => $gid,
                                                      'status'     => '1',
                                                      'group_name' => self::gName($gid),
                                                      'date'       => DateHelper::today(),
                                                ];
                                                $set = DB::getInstance()->insert('phonebook', $data);
                                          }
                                    }
                              }
                        }
                  }
            }
            $invalid = '';
            $x       = 1;
            foreach ($badNumber as $bad) {
                  $invalid .= $bad;
                  if ($x < count($badNumber)) {
                        $invalid .= ', ';
                  }
            }
            $noAmount = '';
            $n        = 1;
            foreach ($nullAmount as $no) {
                  $noAmount .= $no;
                  if ($n < count($nullAmount)) {
                        $noAmount .= ', ';
                  }
            }
            $duplicatenum = '';
            $d            = 1;
            foreach ($duplicate as $dc) {
                  $duplicatenum .= $dc;
                  if ($d < count($duplicate)) {
                        $duplicatenum = ', ';
                  }
            }
            if (count($badNumber) > 0) {
                  Session::Flush('error', $invalid . ' Numbers Are Invalid');
            }
            if (count($nullAmount) > 0) {
                  Session::Flush('error', $noAmount . ' Amount Not Set');
            }
            if (count($duplicate) > 0) {
                  Session::Flush('error', $duplicatenum . ' Already exists in this group');
            }
            Session::Flush('success', ' Phonebook Save Successfully');
            Redirect::to('bulk-phonebook.php?gid=' . $gid);
      }

      public static function numberCheck($number, $gid, $userId)
      {
            $sql   = "SELECT * FROM phonebook WHERE mobile = '$number' AND  gid = '$gid' AND user_id = '$userId'";
            $count = DB::getInstance()->get($sql)->Count();
            if ($count > 0) {
                  return true;
            } else {
                  return false;
            }
      }

      public static function listManage()
      {
            $user_id = Session::get('userId');
            $sql     = "SELECT COUNT(mobile) as totalNumber, gid, SUM(amount) as taka FROM phonebook WHERE user_id = '$user_id' GROUP BY gid";
            return DB::getInstance()->get($sql)->result();
      }

      public static function getBook()
      {
            $gid    = Input::get('gid');
            $userId = Session::get('userId');
            $sql    = "SELECT * FROM phonebook WHERE user_id = '$userId' AND gid = '$gid'";
            return DB::getInstance()->get($sql)->result();
      }

      public static function delPhonebook()
      {
            $id  = Input::get('id');
            $sql = "DELETE FROM phonebook WHERE id = $id";
            $del = DB::getInstance()->get($sql);
      }

      public static function updateBulkNumber()
      {
            $gid     = Input::get('gid');
            $numbers = Input::get('number');
            $amounts = Input::get('amount');
            $types   = Input::get('type');
            $enable  = Input::get('enable');
            $id      = Input::get('id');
            $countId = count($id);
            for ($i = 0; $i < $countId; $i++) {
                  $sql    = "UPDATE phonebook SET mobile = '$numbers[$i]', amount = '$amounts[$i]', type='$types[$i]', status = '$enable[$i]' WHERE id=$id[$i]";
                  $update = DB::getInstance()->get($sql);
            }
            if ($update) {
                  Session::Flush('success', ' Phonebook Update Successfully');
                  Redirect::to('list-manage.php?gid=' . $gid);
            }
      }

      public static function csvPhonebook()
      {
            $badNumber  = [];
            $nullAmount = [];
            $duplicate  = [];
            $numbers    = [];
            $amounts    = [];
            $types      = [];
            $csvFile    = $_FILES['file']['tmp_name'];
            $title      = Input::get('title');
            $fileName   = $_FILES['file']['name'];
            $ext        = end((explode('.', $fileName)));
            if ($ext == 'csv') {
                  if ($_FILES['file']['size'] > 0) {
                        $data = [
                              'user_id'    => Session::get('userId'),
                              'group_name' => $title,
                              'status'     => '1',
                              'date'       => DateHelper::gtm('6', 'date'),
                        ];
                        $gid  = DB::getInstance()->insert('bulkflexi', $data);
                        $file = fopen($csvFile, "r");
                        while (($getData = fgetcsv($file, 10000, ",")) !== false) {
                              $numbers[] = $getData[0];
                              $amounts[] = $getData[1];
                              $types[]   = $getData[2];
                        }
                  }
                  $totalNumber = count($numbers);
                  $userId      = Session::get('userId');
                  $parent      = Reseller::info(Session::get('userId'), 'parent');
                  for ($i = 0; $i < $totalNumber; $i++) {
                        if (!empty($numbers[$i])) {
                              $prefix      = substr($numbers[$i], 0, 3);
                              $phoneLenght = strlen($numbers[$i]);
                              if (!$prefix = '017' ||
                                    !$prefix = '018' ||
                                    !$prefix = '016' ||
                                    !$prefix = '019' ||
                                    !$prefix = '015' ||
                                    !$prefix = '011' ||
                                    11 > $phoneLenght ||
                                    16 < $phoneLenght
                              ) {
                                    $badNumber[] = $numbers[$i];
                              } else {
                                    if ($amounts[$i] < 10 || $amounts[$i] > 1000) {
                                          $nullAmount[] = $numbers[$i];
                                    } else {
                                          if (self::numberCheck($numbers[$i], $gid, $userId) === true) {
                                                $duplicate[] = $numbers[$i];
                                          } else {
                                                $data = [
                                                      'p_id'       => $parent,
                                                      'user_id'    => $userId,
                                                      'admin_id'   => '',
                                                      'mobile'     => $numbers[$i],
                                                      'amount'     => $amounts[$i],
                                                      'type'       => $types[$i],
                                                      'gid'        => $gid,
                                                      'status'     => '1',
                                                      'group_name' => self::gName($gid),
                                                      'date'       => DateHelper::today(),
                                                ];
                                                $set = DB::getInstance()->insert('phonebook', $data);
                                          }
                                    }
                              }
                        }
                  }
                  $invalid = '';
                  $x       = 1;
                  foreach ($badNumber as $bad) {
                        $invalid .= $bad;
                        if ($x < count($badNumber)) {
                              $invalid .= ', ';
                        }
                  }
                  $noAmount = '';
                  $n        = 1;
                  foreach ($nullAmount as $no) {
                        $noAmount .= $no;
                        if ($n < count($nullAmount)) {
                              $noAmount .= ', ';
                        }
                  }
                  $duplicatenum = '';
                  $d            = 1;
                  foreach ($duplicate as $dc) {
                        $duplicatenum .= $dc;
                        if ($d < count($duplicate)) {
                              $duplicatenum = ', ';
                        }
                  }
                  if (count($badNumber) > 0) {
                        Session::Flush('error', $invalid . ' Numbers Are Invalid');
                  }
                  if (count($nullAmount) > 0) {
                        Session::Flush('error', $noAmount . ' Amount Not Set');
                  }
                  if (count($duplicate) > 0) {
                        Session::Flush('error', $duplicatenum . ' Already exists in this group');
                  }
                  Session::Flush('success', ' Phonebook Save Successfully');
                  Redirect::to('bulk-phonebook.php?gid=' . $gid);

            } else {
                  Session::Flush('error', 'please upload only csv file');
                  Redirect::to('csv-phonebook.php');
            }
      }

      public static function deleteList()
      {
            echo 'rotun';
            $gid  = Input::get('id');
            $sql1 = "DELETE FROM bulkflexi WHERE id = $gid";
            $del  = DB::getInstance()->get($sql1);
            if ($del) {
                  $sql = "DELETE FROM phonebook WHERE gid = '$gid'";
                  DB::getInstance()->get($sql);
            }
      }

      /* bulk flexi action */

      public static function bulkSubmit()
      {
            //$numbers = [];
            //$amounts = '';
            //$types   = [];
            $service   = Input::get('service');
            $pin       = Input::get('pin');
            $minimun   = Module::info($service, 'minAm');
            $maximum   = Module::info($service, 'maxAm');
            $minLenght = Module::info($service, 'minLen');
            $maxLenght = Module::info($service, 'maxLen');
            $newAmount = Input::get('amount');
            $gid       = Input::get('list');
            $amounts   = Input::get('totalAmount');
            $sql       = "SELECT * FROM phonebook WHERE gid = '$gid' AND status = '1'";
            $res       = DB::getInstance()->get($sql)->result();
            foreach ($res as $bulk) {
                  $amount = $bulk->amount;
                  $number = $bulk->mobile;
                  $type   = $bulk->type;

                  /* transaction info */
                  $intervalTime  = Verify::scure('5') * 60;
                  $checkInterval = Transaction::checkInterval($number);
                  $timeRemain    = $intervalTime - $checkInterval;
                  $dailyLimit    = Verify::scure('11');
                  $phoneLenght   = strlen($number);
                  $operatorType  = substr($number, 0, 3);
                  /* number info */
                  if ($operatorType == '017') {
                        $oprerator = 'gp';
                  } elseif ($operatorType == '018') {
                        $oprerator = 'rb';
                  } elseif ($operatorType == '019') {
                        $oprerator = 'bl';
                  } elseif ($operatorType == '016') {
                        $oprerator = 'at';
                  } elseif ($operatorType == '015') {
                        $oprerator = 'tt';
                  } elseif ($operatorType == '011') {
                        $oprerator = 'cc';
                  } else {
                        return false;
                  }
                  /*new Amount if not empty*/
                  if (!empty($newAmount)) {
                        $amount = $newAmount;
                  }
                  /* rate information */
                  $rates         = Request::rate($service, $operatorType, $type);
                  $totalAmount   = (Request::rateAmount($amount) + Request::charge($amount)) - Request::commAmount($amount);
                  $parent        = Reseller::info(Session::get('userId'), 'parent');
                  $parentBalance = Transaction::parentBalance();
                  $myBalance     = Transaction::accountBalance();

                  /* condition verify */
                  if ($myBalance < $amounts) {
                        Session::Flush('error', 'You Have no Sufficient Balance.Available Only ' . $myBalance . '. Please Contact With Your reseller');
                        Redirect::to('request.php?service=' . $service);
                  } elseif ($minimun > $amount || $maximum < $amount) {
                        Session::Flush('error', 'Minimum Balance ' . $minimun . ' Maximum Balance ' . $maximum);
                        Redirect::to('request.php?service=' . $service);
                  } elseif ($minLenght > $phoneLenght || $maxLenght < $phoneLenght) {
                        Session::Flush('error', 'Minimum number ' . $minLenght . ' Maximum number ' . $maxLenght);
                        Redirect::to('request.php?service=' . $service);
                  } elseif ($dailyLimit < Transaction::daily()) {
                        echo 'Your Today Limit Over';
                  } elseif ($parent != '1' && $parentBalance < $amounts) {
                        Session::Flush('error', 'Dear Client Contact With Parent reseller');
                        Redirect::to('request.php?service=' . $service);
                  } elseif (Module::isDeactive($service) == 1) {
                        Session::Flush('error', Module::moduleName($service) . ' Already Deactive');
                        Redirect::to('request.php?service=' . $service);
                  } else {
                        if ($checkInterval > $intervalTime || $checkInterval == 0) {
                              if (Verify::pin($pin) === true) {
                                    $data = [
                                          'userid'          => Session::get('userId'),
                                          'admin_id'        => '',
                                          'ip'              => $_SERVER["REMOTE_ADDR"],
                                          'p_id'            => $parent,
                                          'phone'           => $number,
                                          'type'            => $type,
                                          'balance'         => $amount,
                                          'cost'            => $totalAmount,
                                          'submitted_date'  => DateHelper::gtm('6', 'time'),
                                          'idate'           => DateHelper::gtm('6', 'date'),
                                          'service'         => $service,
                                          'send_time_stamp' => DateHelper::timeToday(),
                                          'status'          => '0',
                                          'operator'        => $oprerator,
                                          'pcode'           => Request::pcode(),
                                          'prebalance'      => $myBalance,
                                    ];
                                    $succes = Transaction::sendFlexi($data);
                                    if ($succes === true) {
                                          Reseller::balanceUpdate(Session::get('userId'), $totalAmount);
                                          if ($parent != '1') {
                                                Transaction::trans($service, Session::get('userId'), $amount, $type, $operatorType);
                                          }
                                          $log = [
                                                'log_date'   => DateHelper::gtm('6', 'format'),
                                                'user'       => Session::get('userId'),
                                                'ip'         => $_SERVER['REMOTE_ADDR'],
                                                'access'     => 'Request',
                                                'createdres' => '',
                                                'payres'     => '',
                                                'amount'     => '',
                                                'service'    => $service,
                                                'type'       => $type,
                                                'pcode'      => Request::pcode(),
                                                'amountreq'  => $amount,
                                                'page'       => basename($_SERVER["SCRIPT_FILENAME"], '.php'),
                                                'formre'     => Session::get('userId'),
                                                'updatedres' => '',
                                                'newres'     => '',
                                                'idate'      => DateHelper::gtm('6', 'date'),
                                                'number'     => $number,
                                          ];
                                          Log::requestLog($log);
                                    } else {
                                          Session::Flush('error', 'Dear Client Please Try Again');
                                          Redirect::to('request.php?service=' . $service);
                                    }

                              } else {
                                    Session::Flush('error', 'Dear Client Your Pin Incorrect');
                                    Redirect::to('request.php?service=' . $service);
                              }
                        } else {
                              Session::Flush('error', 'Please request this Mobile ' . $number . ' after ' . $timeRemain . ' Seconds');
                              Redirect::to('request.php?service=' . $service);
                        }
                  }
            }
            Session::Flush('success', 'Dear Client Success Your Transaction service ' . Module::moduleName($service));
            Redirect::to('request.php?service=' . $service);
      }
}
