<?php
class Rate
{
      /**
       *        add rate module
       *      user Rate::add()
       */
      public static function add()
      {
            # bind array with input field
            $service = Input::get('services');
            $code    = Input::get('code');
            $type    = Input::get('type');
            $prefix  = Input::get('prefix');
            $rate    = [
                  'opname' => $service,
                  'rate'   => Input::get('rate'),
                  'comm'   => Input::get('commision'),
                  'charge' => Input::get('charge'),
                  'type'   => $type,
                  'pcode'  => $code,
                  'prefix' => $prefix,
                  'date'   => date('Y-m-d H:i:s'),
            ];

            $select   = "SELECT * FROM price WHERE opname='$service' AND pcode = '$code' AND type = '$type' AND prefix='$prefix'";
            $countRow = DB::getInstance()->get($sql)->Count();
            if ($countRow < 1) {
                  # insert into price table
                  $setRate = DB::getInstance()->insert('price', $rate);
                  if ($setRate) {
                        Session::Flush('success', ' Rate Module Added Successfully');
                        Redirect::to('rate-module.php');
                  } else {
                        Session::Flush('error', 'Please Try Again');
                        Redirect::to('addRate.php');
                  }
            } else {
                  Session::Flush('error', 'This Rate Already Exists');
                  Redirect::to('addRate.php');
            }
      }

      /**
       *      function show all rate module
       *      show in rate - module page
       */
      public static function show()
      {
            $sql    = "SELECT * FROM price ORDER By id DESC";
            $result = DB::getInstance()->get($sql)->result();
            return $result;
      }
      // end function Rate::show

      /**
       *      function name :delRate
       *      delete rate form price table
       */
      public static function delRate()
      {
            $id  = Input::get('rateId');
            $del = DB::getInstance()->get("DELETE FROM price WHERE id = $id");
            if ($del) {
                  Session::Flush('success', 'Rate Module Delete Successfully');
                  Redirect::to('rate-module.php');
            } else {
                  Session::Flush('error', 'Please Try Again');
                  Redirect::to('rate-module.php');
            }
      }
      // end  this function
      /**
       *      add rate module for reseller service
       *      @param $userId | null,
       *      @return boolean
       */
      public static function addRateModule($userId = '')
      {
            $rate = DB::getInstance()->getTable('price')->result();
            foreach ($rate as $rateItem) {
                  # bind array for insert rate_module
                  $addRate = [
                        'userid'    => $userId,
                        'create_by' => Session::get('userId'),
                        'service'   => $rateItem->opname,
                        'prefix'    => $rateItem->prefix,
                        'pcode'     => $rateItem->pcode,
                        'type'      => $rateItem->type,
                        'rate'      => $rateItem->rate,
                        'comm'      => $rateItem->comm,
                        'charge'    => $rateItem->charge,
                        'price_id'  => $rateItem->id,
                        'status'    => '1',
                        'date'      => date('Y-m-d'),
                  ];
                  # insert into rate module table
                  $add = DB::getInstance()->insert('rate_module', $addRate);
            }
            return $add ? true : false;
      }
      //

      /**
       *     function name : getRateModule();
       *     @param $userId | null | must be string
       *     @return array
       */
      public static function getRateModule($userId = null)
      {
            $rateSql = DB::getInstance()->get("SELECT * FROM rate_module WHERE userid = '$userId'")->result();
            return $rateSql;

      }

      /**
       *  update reseller rate module
       */
      public static function updateRateModule()
      {
            //$type       = Input::get('type');
            $resellerId = Input::get('resellerid');
            $checkbox   = Input::get('chkbox');
            $rate       = Input::get('rate');
            $comm       = Input::get('com');
            $charge     = Input::get('charge');

            for ($i = 0; $i < count($checkbox); $i++) {
                  $sql    = "UPDATE rate_module SET rate = '$rate[$i]', comm=$comm[$i], charge=$charge[$i] WHERE id =$checkbox[$i]";
                  $update = DB::getInstance()->get($sql);
            }
            if ($update) {
                  Redirect::to('reseller_list.php');
            }
      }

      // set rate module from reseller page
      public static function setRateModule()
      {
            $resellerId = Input::get('resellerid');
            $checkbox   = Input::get('chkbox');
            $rate       = Input::get('rate');
            $comm       = Input::get('com');
            $charge     = Input::get('charge');
            $status     = Input::get('status'); 
            if (Verify::pin(Input::get('pincode')) == true) {
                  for ($i = 0; $i < count($checkbox); $i++) {
                        $sql    = "UPDATE rate_module SET rate = '$rate[$i]', comm='$comm[$i]', charge='$charge[$i]', status='$status[$i]' WHERE id =$checkbox[$i]";
                        $update = DB::getInstance()->get($sql);
                  }
                  if ($update) {
                        Redirect::to('reseller.php');
                  }
            } else {
                  Session::Flush('error', 'Invalid Pin');
                  Redirect::to('rates.php?reseller=' . base64_encode($resellerId));
            }
      }

      // filter rate module
      public static function filterRate()
      {
            $prefix  = Input::get('prefix');
            $service = Input::get('service');
            if ($prefix != '' && $service != '') {
                  $sql = "SELECT * FROM price WHERE opname ='$service' AND prefix ='$prefix' ORDER BY id ASC ";
            } elseif ($prefix != '') {
                  $sql = "SELECT * FROM price WHERE prefix ='$prefix' ORDER BY id ASC ";
            } elseif ($service != '') {
                  $sql = "SELECT * FROM price WHERE opname ='$service' ORDER BY id ASC ";
            } else {
                  $sql = "SELECT * FROM price ORDER BY id ASC ";
            }
            return $sql;
      }

      // count total  filter data
      public static function total()
      {
            return DB::getInstance()->get(self::filterRate())->Count();
      }

      /** get rate module for update page */

      public static function edit($index = '')
      {
            $item   = [];
            $rateId = Input::get('rateId');
            $select = "SELECT * FROM price WHERE id = $rateId";
            $data   = DB::getInstance()->get($select)->result();
            foreach ($data as $el):
                  $item['op']     = $el->opname;
                  $item['rate']   = $el->rate;
                  $item['comm']   = $el->comm;
                  $item['charge'] = $el->charge;
                  $item['type']   = $el->type;
                  $item['pcode']  = $el->pcode;
                  $item['prefix'] = $el->prefix;
            endforeach;
            return $item[$index];
      }

      /** function for update price table rate module */
      public static function updateRate()
      {
            $rateId  = Input::get('rateId');
            $service = Input::get('services');
            $code    = Input::get('code');
            $type    = Input::get('type');
            $prefix  = Input::get('prefix');
            $rate    = [
                  'opname' => $service,
                  'rate'   => Input::get('rate'),
                  'comm'   => Input::get('commision'),
                  'charge' => Input::get('charge'),
                  'type'   => $type,
                  'pcode'  => $code,
                  'prefix' => $prefix,
            ];
            # update price table
            $update = DB::getInstance()->update('price', $rateId, $rate);
            if($update){
                  Session::Flush('success', 'Rate Module Delete Successfully');
                  Redirect::to('rate-module.php');
            }else{
                  Session::Flush('error', 'Please Try Again');
                  Redirect::to('edit-rate.php');
            }
      }

      public static function synchronized()
      {
            $rate   = [];
            $type = Input::get('type');
            $userId = Input::get('user');
            $sql1    = "DELETE FROM rate_module WHERE userid = '$userId'";
            DB::getInstance()->get($sql1);
            $sql = "SELECT * FROM price";
            $newRate = DB::getInstance()->get($sql)->result();
            foreach ($newRate as $new) {
                  $data = [
                        'userid'    => $userId,
                        'create_by' => Session::get('userId'),
                        'service'   => $new->opname,
                        'prefix'    => $new->prefix,
                        'pcode'     => $new->pcode,
                        'type'      => $new->type,
                        'rate'      => $new->rate,
                        'comm'      => $new->comm,
                        'charge'    => $new->charge,
                        'price_id'  => $new->id,
                        'status'    => '1',
                        'date'      => DateHelper::gtm(6, 'date'),
                  ];
                  $insert = DB::getInstance()->insert('rate_module', $data);
            }
            if($insert){
                  Session::Flush('success', 'Rate Module Synchronized Successfully');
                  Redirect::to('rates.php?reseller='.base64_encode($userId).'&type='.$type);
            }
      }
}
