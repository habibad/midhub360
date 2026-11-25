<?php
class Module
{
      /**
       *      Add Service Module Function
       *      @return Redirect Other Page
       */
      public static function add()
      {
            # get module information data
            $title     = Xss::xssClean(Input::get('stitle'));
            $minAmount = Xss::xssClean(Input::get('min_amount'));
            $maxAmount = Xss::xssClean(Input::get('max_amount'));
            $minLength = Xss::xssClean(Input::get('min_length'));
            $maxLength = Xss::xssClean(Input::get('max_length'));
            $rqPin     = Input::get('requr_pin');
            $active    = Input::get('status');
            $sort      = Input::get('order');
            # bind array for insert in module table
            $module = [
                  'title'      => $title,
                  'min_amount' => $minAmount,
                  'max_amount' => $maxAmount,
                  'min_length' => $minLength,
                  'max_length' => $maxLength,
                  'requr_pin'  => $rqPin,
                  'sorder'     => $sort,
                  'status'     => $active,
                  'date'       => date('Y-m-d'),
            ];
            $insert = DB::getInstance()->insert('module', $module);
            if ($insert) {
                  Session::Flush('success', ' Service Module Added Successfully');
                  Redirect::to('service-module.php');
            } else {
                  Session::Flush('error', '');
                  Redirect::to('add-module.php');
            }
      }

      // end Module::add function

      /**
       *         function name: show
       *      module list
       *      @return object data
       */
      public static function show()
      {
            $sql    = "SELECT * FROM module ORDER By sorder ASC";
            $result = DB::getInstance()->get($sql)->result();
            return $result;
      }
      // end this function
      public static function showall()
      {
            $sql    = "SELECT * FROM module ORDER By sorder ASC";
            $result = DB::getInstance()->get($sql)->result();
            return $result;
      }
      /**
       *   delete module
       *   delete from module table
       */
      public static function delModule()
      {
            $id  = Input::get('moduleId');
            $del = DB::getInstance()->get("DELETE FROM module WHERE id = $id");
            if ($del) {
                  Session::Flush('success', ' Service Module Delete Successfully');
                  Redirect::to('service-module.php');
            } else {
                  Session::Flush('error', 'Please Try Again');
                  Redirect::to('service-module.php');
            }
      }

      public static function inActive()
      {
            $moduleId = Input::get('chkbox');
            foreach ($moduleId as $id) {
                  DB::getInstance()->get("UPDATE module SET status = '0' WHERE id =$id");
            }
      }

      public static function active()
      {
            $moduleId = Input::get('chkbox');
            foreach ($moduleId as $id) {
                  DB::getInstance()->get("UPDATE module SET status = '1' WHERE id =$id");
            }
      }
      public static function setableModule()
      {
            $todo = Input::get('todo');
            if ($todo == 'Enable') {
                  self::active();
                  Session::Flush('success', 'Active Successfully');
                  Redirect::to('service-module.php');
            } elseif ($todo == 'Disable') {
                  self::inActive();
                  Session::Flush('success', 'inActive Successfully');
                  Redirect::to('service-module.php');
            } else {
                  return false;
            }
      }
      /**
       *     function name: module name
       *     @param $id = null | int
       *     @return string
       */
      public static function moduleName($id = null)
      {
            $res = DB::getInstance()->get("SELECT title FROM module WHERE id= $id");
            foreach ($res->result() as $title):
                  return $title->title;
            endforeach;
      }

      public static function sort()
      {
            $sortorder = [];
            $sort      = DB::getInstance()->get("SELECT * FROM module ORDER BY id DESC");
            foreach ($sort->result() as $order):
                  $sortorder[] = $order->id;
            endforeach;
            return $sortorder;
      }

      /**
       *     function name: get
       *     get module from module for edit
       */
      public static function get()
      {
            $service = [];
            $id      = Input::get('moduleId');
            $get     = DB::getInstance()->get("SELECT * FROM module WHERE id =$id")->result();
            foreach ($get as $item):
                  $service['title']  = $item->title;
                  $service['minAm']  = $item->min_amount;
                  $service['maxAm']  = $item->max_amount;
                  $service['minLen'] = $item->min_length;
                  $service['maxLen'] = $item->max_length;
                  $service['pin']    = $item->requr_pin;
                  $service['sort']   = $item->sorder;
                  $service['status'] = $item->status;
            endforeach;
            return $service;
      }

      /** update module */
      public static function updateModule()
      {
            $moduleId = Input::get('moduleid');
            # get module information data
            $title     = Xss::xssClean(Input::get('stitle'));
            $minAmount = Xss::xssClean(Input::get('min_amount'));
            $maxAmount = Xss::xssClean(Input::get('max_amount'));
            $minLength = Xss::xssClean(Input::get('min_length'));
            $maxLength = Xss::xssClean(Input::get('max_length'));
            $rqPin     = Input::get('requr_pin');
            $active    = Input::get('status');
            $sort      = Input::get('order');
            # bind array for insert in module table
            $module = [
                  'title'      => $title,
                  'min_amount' => $minAmount,
                  'max_amount' => $maxAmount,
                  'min_length' => $minLength,
                  'max_length' => $maxLength,
                  'requr_pin'  => $rqPin,
                  'sorder'     => $sort,
                  'status'     => $active
            ];
            $update = DB::getInstance()->update('module',$moduleId, $module);
            if ($update) {
                  Session::Flush('success', ' Service Module Added Successfully');
                  Redirect::to('service-module.php');
            } else {
                  Session::Flush('error', '');
                  //Redirect::to('add-module.php?moduleId='.$moduleId);
            }
      }

      /* end this function */

      /**
      *     function name:info
      *     @param moduleId
      */
      public static function info($moduleId = '', $index = '')
      {
            $service = [];
            $get     = DB::getInstance()->get("SELECT * FROM module WHERE id =$moduleId")->result();
            foreach ($get as $item):
                  $service['title']  = $item->title;
                  $service['minAm']  = $item->min_amount;
                  $service['maxAm']  = $item->max_amount;
                  $service['minLen'] = $item->min_length;
                  $service['maxLen'] = $item->max_length;
                  $service['pin']    = $item->requr_pin;
                  $service['sort']   = $item->sorder;
                  $service['status'] = $item->status;
            endforeach;
            return $service[$index];
      }

      /** deactive module status */
      public static function isDeactive($moduleid)
      {
            $aql = "SELECT * FROM module WHERE id =$moduleid AND status='0'";
            return DB::getInstance()->get($aql)->Count();
      }
}
