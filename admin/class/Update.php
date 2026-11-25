<?php

class Update 
{

    /* Update Customer Profile Start */

    public static function UpdateCustomer()
    {
        $fullname = Input::get('full_name');
        $address = Input::get('address');;        
        $phone = Input::get('mobile');
        $email = Input::get('email');
        $user = Input::get('username');
        $pass = Input::get('password');
        $cslevel = Input::get('level');
        $customer_id = Input::get('userid');
        $self_pin = Input::get('selfpin');
        $api_allow = Input::get('api_allow');


        $pin_code = Input::get('pin');
        $country = "";
        $reseller_active = Input::get('active');		
		$reseller_ip_block_check = Input::get('ip_block_check');
		$devices_permitted = Input::get('deviceno');
        $limit_balance = Input::get('bllimit');	
		
		
        $userlevel = Session::get('level');
        $us_id = Session::get('us_id');	



         /* Function for prevent of sql injection */
         
            global $connect;
            $fullname = stripcslashes($fullname);
            $address  = stripcslashes($address);
            $phone    = stripcslashes($phone);
            $email    = stripcslashes($email);
            $user     = stripcslashes($user);
            $pass     = stripcslashes($pass);
            $pin_code = stripcslashes($pin_code);
            
            $fullname = mysqli_real_escape_string($connect, $fullname);
            $address  = mysqli_real_escape_string($connect, $address);
            $phone    = mysqli_real_escape_string($connect, $phone);
            $email    = mysqli_real_escape_string($connect, $email);
            $user     = mysqli_real_escape_string($connect, $user);
            $pass     = mysqli_real_escape_string($connect, $pass);
            $pin_code = mysqli_real_escape_string($connect, $pin_code);            
    
        /* End of Function for prevent of sql injection */

        $query = "SELECT * FROM user where id='$us_id'";        
        $result = mysqli_query($connect, $query);

        if(!$result){
            die("Query Failed!" . mysqli_error($connect));      
        }        
        
        $reseller_data    = mysqli_fetch_object($result);
        
        $search_pin       = $reseller_data->pin_code;
       
        $selfpincode  = md5($self_pin);


        if($selfpincode != $search_pin) 
        {
            Session::Flush('error', 'Your self pin is invalid!');
            Redirect::to('cs-edit.php?reseller_id='.$customer_id); 
        }
        else
        {
      
                $npassword = md5($pass);
                $npincode  = md5($pin_code);

                if (empty($pass) && empty($pin_code)) 
                {            
                    $data = "fullname='$fullname',email='$email',phone='$phone',address='$address',limit_balance='$limit_balance',active='$reseller_active',api_status='$api_allow'";
                    $notifica_msg = "Your profile updated by Admin.";
                }
                elseif (!empty($pass) && empty($pin_code)) 
                {            
                    $data = "fullname='$fullname',pass='$npassword',email='$email',phone='$phone',address='$address',limit_balance='$limit_balance',active='$reseller_active',api_status='$api_allow'";
                    $notifica_msg = "Your password changed by Admin.";
                }
                elseif (empty($pass) && !empty($pin_code)) 
                {            
                    $data = "fullname='$fullname',pin_code='$npincode',email='$email',phone='$phone',address='$address',limit_balance='$limit_balance',active='$reseller_active',api_status='$api_allow'";
                    $notifica_msg = "Your pincode changed by Admin.";
                }
                elseif (!empty($pass) && !empty($pin_code)) 
                {            
                    $data = "fullname='$fullname',pass='$npassword',pin_code='$npincode',email='$email',phone='$phone',address='$address',limit_balance='$limit_balance',active='$reseller_active',api_status='$api_allow'";
                    $notifica_msg = "Your password and pincode changed by Admin.";
                }         
                else
                {
                    Session::Flush('error', 'Invalid!');
                    Redirect::to('cs-edit.php?reseller_id='.$customer_id); 
                }

                $query = "UPDATE user SET $data WHERE id = $customer_id";
                global $connect; 
                $result = mysqli_query($connect, $query);    
                if(!$result)
                {                      
                    die("Query Failed!" . mysqli_error($connect));
                }


                $notifica_from  = "Account Update";
                Notice::InsertNotification($customer_id,$notifica_msg,$notifica_from);

                Session::Flush('success', 'Successfully Customer Profile Updated!');
                Redirect::to('cs-edit.php?reseller_id='.$customer_id); 

                

        }
       
    }

    /* Update Customer Profile End */

    
    /* Update My Settings Start */

    public static function UpdateMySettings()
    {
        $newpassword = Input::get('newpass');
        $newpin      = Input::get('newpin');;        
        $oldpassword = Input::get('oldpass');
        $oldpin      = Input::get('oldpin');
                
        $us_id       = Session::get('us_id');	



         /* Function for prevent of sql injection */
         
            global $connect;
            $newpassword  = stripcslashes($newpassword);
            $newpin       = stripcslashes($newpin);
            $oldpassword  = stripcslashes($oldpassword);
            $oldpin       = stripcslashes($oldpin);
            
            
            $newpassword    = mysqli_real_escape_string($connect, $newpassword);
            $newpin         = mysqli_real_escape_string($connect, $newpin);
            $oldpassword    = mysqli_real_escape_string($connect, $oldpassword);
            $oldpin         = mysqli_real_escape_string($connect, $oldpin);
                 
    
        /* End of Function for prevent of sql injection */

        $query = "SELECT * FROM user where id='$us_id'";        
        $result = mysqli_query($connect, $query);

        if(!$result){
            die("Query Failed!" . mysqli_error($connect));      
        }        
        
        $reseller_data    = mysqli_fetch_object($result);
        $search_password  = $reseller_data->pass;
        $search_pin       = $reseller_data->pin_code;


        $opassword = md5($oldpassword);
        $opincode  = md5($oldpin);

        
        if($opassword != $search_password) 
        {
            Session::Flush('error', 'Old Password does not match!');
            Redirect::to('update_mysettings.php'); 
        }
        elseif($opincode != $search_pin) 
        {
            Session::Flush('error', 'Old Pincode does not match!');
            Redirect::to('update_mysettings.php'); 
        }
        else
        {

                    $npassword = md5($newpassword);
                    $npincode  = md5($newpin);

                    if (empty($newpassword) && empty($newpin)) 
                    {            
                        Session::Flush('error', 'New Password or New Pin is field blank!');
                        Redirect::to('update_mysettings.php'); 
                    }
                    elseif (!empty($newpassword) && empty($newpin)) 
                    {            
                        $data = "pass='$npassword'";
                    }
                    elseif (empty($newpassword) && !empty($newpin)) 
                    {            
                        $data = "pin_code='$npincode'";
                    }
                    elseif (!empty($newpassword) && !empty($newpin)) 
                    {            
                        $data = "pass='$npassword',pin_code='$npincode'";
                    }         
                    else
                    {
                        Session::Flush('error', 'Invalid!');
                        Redirect::to('update_mysettings.php');
                    }

                    $query = "UPDATE user SET $data WHERE id = $us_id";
                    global $connect; 
                    $result = mysqli_query($connect, $query);    
                    if(!$result)
                    {                      
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    Session::Flush('success', 'Successfully settings updated!');
                    Redirect::to('update_mysettings.php');

             
        }


       
    }

    /* Update My Settings End */



    /* Update My Profile Start */

    public static function UpdateMyProfile()
    {
        $fullname = Input::get('fullname');            
        $phone = Input::get('phonenumber');
        $email = Input::get('email');    
        $self_pin = Input::get('selfpin');
        $us_id = Session::get('us_id');	



         /* Function for prevent of sql injection */
         
            global $connect;
            $fullname = stripcslashes($fullname);           
            $phone    = stripcslashes($phone);
            $email    = stripcslashes($email);           
            $self_pin = stripcslashes($self_pin);
            
            $fullname = mysqli_real_escape_string($connect, $fullname);           
            $phone    = mysqli_real_escape_string($connect, $phone);
            $email    = mysqli_real_escape_string($connect, $email);           
            $self_pin = mysqli_real_escape_string($connect, $self_pin);            
    
        /* End of Function for prevent of sql injection */

        $query = "SELECT * FROM user where id='$us_id'";        
        $result = mysqli_query($connect, $query);

        if(!$result){
            die("Query Failed!" . mysqli_error($connect));      
        }        
        
        $reseller_data    = mysqli_fetch_object($result);
        
        $search_pin       = $reseller_data->pin_code;
       
        $selfpincode  = md5($self_pin);


        if($selfpincode != $search_pin) 
        {
            Session::Flush('error', 'Your self pin is invalid!');
            Redirect::to('update_myprofile.php');
        }
        else
        {     
                             
                $query = "UPDATE user SET fullname='$fullname',email='$email',phone='$phone' WHERE id = $us_id";
                global $connect; 
                $result = mysqli_query($connect, $query);    
                if(!$result)
                {                      
                    die("Query Failed!" . mysqli_error($connect));
                }
                Session::Flush('success', 'Successfully Profile Updated!');
                Redirect::to('update_myprofile.php'); 

                

        }
       
    }

    /* Update My Profile End */


     /* Update Settings Start */

     public static function UpdateSettings()
     {
         $recharge_limit = Input::get('relimit');            
         $mbank_limit = Input::get('mlimit');
         $pin_expire = Input::get('pinexpire');    
         $pass_expire = Input::get('passexpire');
         $recharge_min = Input::get('remin');            
         $recharge_max = Input::get('remax');
         $mbank_min = Input::get('mmin');    
         $mbank_max = Input::get('mmax');
         $country_block = Input::get('active');

         $us_id = Session::get('us_id');	
 
 
 
          /* Function for prevent of sql injection */
          
             global $connect;
             $recharge_limit = stripcslashes($recharge_limit);           
             $mbank_limit    = stripcslashes($mbank_limit);
             $pin_expire    = stripcslashes($pin_expire);           
             $pass_expire = stripcslashes($pass_expire);
             $recharge_min = stripcslashes($recharge_min);           
             $recharge_max    = stripcslashes($recharge_max);
             $mbank_min    = stripcslashes($mbank_min);           
             $mbank_max = stripcslashes($mbank_max);

             
             $recharge_limit = mysqli_real_escape_string($connect, $recharge_limit);           
             $mbank_limit    = mysqli_real_escape_string($connect, $mbank_limit);
             $pin_expire    = mysqli_real_escape_string($connect, $pin_expire);           
             $pass_expire = mysqli_real_escape_string($connect, $pass_expire); 
             $recharge_min = mysqli_real_escape_string($connect, $recharge_min);           
             $recharge_max    = mysqli_real_escape_string($connect, $recharge_max);
             $mbank_min    = mysqli_real_escape_string($connect, $mbank_min);           
             $mbank_max = mysqli_real_escape_string($connect, $mbank_max); 
             
     
         /* End of Function for prevent of sql injection */
 
                                      
                 $query = "UPDATE settings SET recharge_low='$recharge_limit',mbank_low='$mbank_limit',pin_expire='$pin_expire',pass_expire='$pass_expire',recharge_min='$recharge_min',recharge_max='$recharge_max',mbank_min='$mbank_min',mbank_max='$mbank_max' WHERE id ='1'";
                 global $connect; 
                 $result = mysqli_query($connect, $query);    
                 if(!$result)
                 {                      
                     die("Query Failed!" . mysqli_error($connect));
                 }
                 Session::Flush('success', 'Successfully Settings Updated!');
                 Redirect::to('settings.php'); 
          
        
     }
 
     /* Update Settings End */



     /* Update Web Settings Start */

     public static function UpdateWebSettings()
     {
         $phone_number = Input::get('phnumber');            
         $whatsapp_number = Input::get('whnumber');
         $email_address = Input::get('email');    
         $apps_link = Input::get('applink');
         $fb_link = Input::get('fblink');            
         $youtube_link = Input::get('ytlink');
         $twitter_link = Input::get('twtlink');    
         $about = Input::get('about');
        
         $us_id = Session::get('us_id');	
 
 
 
          /* Function for prevent of sql injection */
          
             global $connect;
             $phone_number    = stripcslashes($phone_number);           
             $whatsapp_number = stripcslashes($whatsapp_number);
             $email_address   = stripcslashes($email_address);           
             $apps_link       = stripcslashes($apps_link);
             $fb_link         = stripcslashes($fb_link);           
             $youtube_link    = stripcslashes($youtube_link);
             $twitter_link    = stripcslashes($twitter_link);           
             $about           = stripcslashes($about);

             
             $phone_number = mysqli_real_escape_string($connect, $phone_number);           
             $whatsapp_number    = mysqli_real_escape_string($connect, $whatsapp_number);
             $email_address    = mysqli_real_escape_string($connect, $email_address);           
             $apps_link = mysqli_real_escape_string($connect, $apps_link); 
             $fb_link = mysqli_real_escape_string($connect, $fb_link);           
             $youtube_link    = mysqli_real_escape_string($connect, $youtube_link);
             $twitter_link    = mysqli_real_escape_string($connect, $twitter_link);           
             $about = mysqli_real_escape_string($connect, $about); 
             
     
         /* End of Function for prevent of sql injection */
 
                                      
                 $query = "UPDATE web_setting SET contact_number='$phone_number',whats_app='$whatsapp_number',email='$email_address',app_link='$apps_link',about='$about',fb_link='$fb_link',youtube_link='$youtube_link',twitter_link='$twitter_link' WHERE id ='1'";
                 global $connect; 
                 $result = mysqli_query($connect, $query);    
                 if(!$result)
                 {                      
                     die("Query Failed!" . mysqli_error($connect));
                 }
                 Session::Flush('success', 'Successfully Settings Updated!');
                 Redirect::to('web_settings.php'); 
          
        
     }
 
     /* Update Web Settings End */



      /* Server Status On/Off Start */

      public static function UpdateServerStatus()
      {
          $server_status = Input::get('active');            
          $opening_date= Input::get('date');
          $opening_time = Input::get('time');    
          $message = Input::get('msg');          
 
          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;
              $opening_date  = stripcslashes($opening_date);           
              $opening_time  = stripcslashes($opening_time);
              $message       = stripcslashes($message);           
             
              
              $opening_date = mysqli_real_escape_string($connect, $opening_date);           
              $opening_time   = mysqli_real_escape_string($connect, $opening_time);
              $message    = mysqli_real_escape_string($connect, $message);           
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE server_status SET status='$server_status',msg='$message',opening_date='$opening_date',opening_time='$opening_time' WHERE id ='1'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Server Status Updated!');
                  Redirect::to('server_status.php'); 
           
         
      }
  
      /* Server Status On/Off End */



      
      /* Operator Status On/Off Start */

      public static function OperatorOnOff()
      {
          $server_status = Input::get('active');            
          $opening_date= Input::get('date');
          $opening_time = Input::get('time');    
          $message = Input::get('msg'); 
          $op_extension = Input::get('operator');         
 
          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;
              $opening_date  = stripcslashes($opening_date);           
              $opening_time  = stripcslashes($opening_time);
              $message       = stripcslashes($message);           
             
              
              $opening_date = mysqli_real_escape_string($connect, $opening_date);           
              $opening_time   = mysqli_real_escape_string($connect, $opening_time);
              $message    = mysqli_real_escape_string($connect, $message);           
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE operator_status SET status='$server_status',msg='$message',opening_date='$opening_date',opening_time='$opening_time' WHERE extension ='$op_extension'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Server Status Updated!');
                  Redirect::to('operator_status.php'); 
           
         
      }
  
      /* Operator Status On/Off End */


      /* Add Balance Status update Start */

      public static function AddBalanceStatus()
      {
          $server_status = Input::get('status');            
          
    
          $query = "UPDATE addfund_settings SET status='$server_status'WHERE id ='1'";
          global $connect; 
          $result = mysqli_query($connect, $query);    
          if(!$result)
          {                      
              die("Query Failed!" . mysqli_error($connect));
          }
          Session::Flush('success', 'Successfully Server Status Updated!');
          Redirect::to('add_balance_status.php');   
           
      }
  
      /* Add Balance Status update End */


      /* Add Balance Status update Start */

      public static function balanceveriup()
      {
          $verified = Input::get('verified');            
          $id = Input::get('id');
    
          $query = "UPDATE auto_addbalance SET verified='$verified'WHERE id ='$id'";
          global $connect; 
          $result = mysqli_query($connect, $query);    
          if(!$result)
          {                      
              die("Query Failed!" . mysqli_error($connect));
          }
          Session::Flush('success', 'Successfully Server Status Updated!');
          Redirect::to('add_bl_edit.php?id='.$id);   
           
      }
  
      /* Add Balance Status update End */



      /* Update Brand Start */

      public static function UpdateBrand()
      {
          $appname = Input::get('appname');            
                     
 
          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;
              $appname  = stripcslashes($appname);          
             
              $appname= mysqli_real_escape_string($connect, $appname);           
                    
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE branding SET apps_name='$appname' WHERE id ='1'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('branding.php'); 
           
         
      }
  
      /* Update Brand End */



      /* Update Company Info Start */

      public static function UpdateCompanyInfo()
      {
          $company_name = Input::get('company_name');
          $company_address = Input::get('company_address');  
          $phonenumber = Input::get('phonenumber');  
          $about = Input::get('about');              
                     
 
          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;
              $company_name     = stripcslashes($company_name); 
              $company_address  = stripcslashes($company_address); 
              $phonenumber      = stripcslashes($phonenumber); 
              $about            = stripcslashes($about);          
             
              $company_name     = mysqli_real_escape_string($connect, $company_name);
              $company_address  = mysqli_real_escape_string($connect, $company_address);
              $phonenumber      = mysqli_real_escape_string($connect, $phonenumber);
              $about            = mysqli_real_escape_string($connect, $about);           
                    
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE company SET name='$company_name',phone='$phonenumber',address='$company_address',about='$about' WHERE id ='1'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('company_info.php'); 
           
         
      }
  
      /* Update Company Info End */



      /* Update Payment Info Start */

      public static function UpdatePaymentInfo()
      {
          $bkash_agent      = Input::get('bkagent');
          $rocket_agent     = Input::get('rcagent');  
          $nagad_agent      = Input::get('ngagent');  
          $bkash_personal   = Input::get('bkpersonal');              
          $rocket_personal  = Input::get('rcpersonal');              
          $nagad_personal   = Input::get('ngpersonal');              
          $bank_ac          = Input::get('bankac');              
           
          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;
              $bkash_agent      = stripcslashes($bkash_agent); 
              $rocket_agent     = stripcslashes($rocket_agent); 
              $nagad_agent      = stripcslashes($nagad_agent); 
              $bkash_personal   = stripcslashes($bkash_personal);          
              $rocket_personal  = stripcslashes($rocket_personal);          
              $nagad_personal   = stripcslashes($nagad_personal);          
              $bank_ac          = stripcslashes($bank_ac);          
             
              $bkash_agent      = mysqli_real_escape_string($connect, $bkash_agent);
              $rocket_agent     = mysqli_real_escape_string($connect, $rocket_agent);
              $nagad_agent      = mysqli_real_escape_string($connect, $nagad_agent);
              $bkash_personal   = mysqli_real_escape_string($connect, $bkash_personal);           
              $rocket_personal  = mysqli_real_escape_string($connect, $rocket_personal);           
              $nagad_personal   = mysqli_real_escape_string($connect, $nagad_personal);           
              $bank_ac          = mysqli_real_escape_string($connect, $bank_ac);           
                    
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE payment_account SET bkash_agent='$bkash_agent',bkash_personal='$bkash_personal',rocket_agent='$rocket_agent',rocket_personal='$rocket_personal',nagad_agent='$nagad_agent',nagad_personal='$nagad_personal',bank_ac='$bank_ac' WHERE id ='1'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('add_payment_info.php'); 
           
         
      }
  
      /* Update Payment Info End */





      /* API Update Start */

      public static function UpdateAPI()
      {
          $api_username = Input::get('apiusername');
          $api_key      = Input::get('apikey');  
          $request_link = Input::get('requestlink');
          $status_link  = Input::get('statuslink'); 
          $api_id       = Input::get('apiid');
          $company_name = Input::get('company');
          $service_name = Input::get('service');
          $balance_link = Input::get('balancelink');
          $api_status   = Input::get('active');
          $pera_user    = Input::get('perauser');
          $pera_key     = Input::get('perakey');
          $pera_number  = Input::get('peranumber');
          $pera_amount  = Input::get('peraamount');
          $pera_type    = Input::get('peratype');
          $pera_customone   = Input::get('peracustomone');
          $peracustomtwo   = Input::get('peracustomtwo');
          $country_name   = Input::get('country');
         

          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;
              $api_username     = stripcslashes($api_username); 
              $api_key          = stripcslashes($api_key); 
              $request_link     = stripcslashes($request_link); 
              $status_link      = stripcslashes($status_link);
              $api_id           = stripcslashes($api_id);
              $company_name     = stripcslashes($company_name);            
              $service_name     = stripcslashes($service_name);            
              $balance_link     = stripcslashes($balance_link);            
             
              $api_username     = mysqli_real_escape_string($connect, $api_username);
              $api_key          = mysqli_real_escape_string($connect, $api_key);
              $request_link     = mysqli_real_escape_string($connect, $request_link);
              $status_link      = mysqli_real_escape_string($connect, $status_link);           
              $api_id           = mysqli_real_escape_string($connect, $api_id);           
              $company_name     = mysqli_real_escape_string($connect, $company_name);           
              $service_name     = mysqli_real_escape_string($connect, $service_name);           
              $balance_link     = mysqli_real_escape_string($connect, $balance_link);           
                    
             
          /* End of Function for prevent of sql injection */
  
                                       
          if ($company_name == 'eFlexiload') 
          {            
            $data = "username='$api_username',ap_key='$api_key',request_url='$request_link',status_url='$status_link',balance_url='$balance_link',api_status='$api_status'";
          }          
          elseif ($company_name == 'Others')
          {            
            $data = "username='$api_username',ap_key='$api_key',request_url='$request_link',status_url='$status_link',balance_url='$balance_link',api_status='$api_status',pera_user='$pera_user',pera_key='$pera_key',pera_number='$pera_number',pera_amount='$pera_amount',pera_type='$pera_type',pera_customone='$pera_customone',pera_customtwo='$peracustomtwo'";
          }
          else
          {            
            $data = "country='$country_name',company='$company_name',service='$service_name',username='$api_username',ap_key='$api_key',request_url='$request_link',status_url='$status_link',balance_url='$balance_link',api_status='$api_status',pera_user='$pera_user',pera_key='$pera_key',pera_number='$pera_number',pera_amount='$pera_amount',pera_type='$pera_type',pera_customone='$pera_customone',pera_customtwo='$peracustomtwo'";
          } 

          $query = "UPDATE api SET $data WHERE id = $api_id";
          global $connect; 
          $result = mysqli_query($connect, $query);    
          if(!$result)
          {                      
              die("Query Failed!" . mysqli_error($connect));
          }

          if ($company_name == 'eFlexiload') 
          { 

          Session::Flush('success', 'Successfully API Updated!');
          Redirect::to('api_setup_eflexiload.php'); 
          }
          elseif ($company_name == 'Others') 
          { 

          Session::Flush('success', 'Successfully API Updated!');
          Redirect::to('api_setup_others.php'); 
          }
          else
          {
            Session::Flush('success', 'Successfully API Updated!');
            Redirect::to('api_list.php');
          }

           
         
      }
  
      /* API Update End */
 
 
 
      /* Add New API End*/

      public static function AddAPI()
      {
          $api_username = Input::get('apiusername');
          $api_key      = Input::get('apikey');  
          $request_link = Input::get('requestlink');
          $status_link  = Input::get('statuslink'); 
          $api_id       = Input::get('apiid');
          $company_name = Input::get('company');
          $service_name = Input::get('service');
          $balance_link = Input::get('balancelink');
          $api_status   = Input::get('active');
          $pera_user    = Input::get('perauser');
          $pera_key     = Input::get('perakey');
          $pera_number  = Input::get('peranumber');
          $pera_amount  = Input::get('peraamount');
          $pera_type    = Input::get('peratype');
          $pera_customone  = Input::get('peracustomone');
          $peracustomtwo   = Input::get('peracustomtwo');
          $country         = Input::get('country');

          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;
              $api_username     = stripcslashes($api_username); 
              $api_key          = stripcslashes($api_key); 
              $request_link     = stripcslashes($request_link); 
              $status_link      = stripcslashes($status_link);
              $api_id           = stripcslashes($api_id);
              $company_name     = stripcslashes($company_name);            
              $service_name     = stripcslashes($service_name);            
              $balance_link     = stripcslashes($balance_link);            
             
              $api_username     = mysqli_real_escape_string($connect, $api_username);
              $api_key          = mysqli_real_escape_string($connect, $api_key);
              $request_link     = mysqli_real_escape_string($connect, $request_link);
              $status_link      = mysqli_real_escape_string($connect, $status_link);           
              $api_id           = mysqli_real_escape_string($connect, $api_id);           
              $company_name     = mysqli_real_escape_string($connect, $company_name);           
              $service_name     = mysqli_real_escape_string($connect, $service_name);           
              $balance_link     = mysqli_real_escape_string($connect, $balance_link);           
                    
             
          /* End of Function for prevent of sql injection */
  
          $query  = "INSERT INTO api (id,country,company,service,username,ap_key,request_url,status_url,balance_url,api_status,pera_user,pera_key,pera_number,pera_amount,pera_type,pera_customone,pera_customtwo)";
          $query .= "VALUES(0,'$country','$company_name','$service_name','$api_username','$api_key','$request_link','$status_link','$balance_link','$api_status','$pera_user','$pera_key','$pera_number','$pera_amount','$pera_type','$pera_customone','$peracustomtwo')"; //Used concatenation (.) add two string
         
          $result = mysqli_query($connect, $query);    
          if(!$result)
          {
              
              die("Query Failed!" . mysqli_error($connect));
          }
          
          
            Session::Flush('success', 'Successfully New API Added!');
            Redirect::to('api_list.php');
          

           
         
      }
  
      /* Add New API End*/




      
      /* Edit Drive Start */

      public static function EditDrive()
      {
            $operator   = Input::get('operator'); 
            $drivename  = Input::get('drivename'); 
            $amount     = Input::get('amount');      
            $commission = Input::get('commission');         
            $status     = Input::get('active');            
            $drive_id   = Input::get('driveid');
            $balance    = $amount - $commission;
                
                        
            $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;            
              $drivename     = stripcslashes($drivename);
              $amount        = stripcslashes($amount);
              $commission    = stripcslashes($commission);           
           
              $drivename    = mysqli_real_escape_string($connect, $drivename);
              $amount       = mysqli_real_escape_string($connect, $amount);
              $commission   = mysqli_real_escape_string($connect, $commission);           
                    
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE drive SET operator_id='$operator',drive_name='$drivename',amount='$amount',commission='$commission',balance='$balance',status='$status' WHERE id ='$drive_id'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('drive_list.php'); 
           
         
      }
  
      /* Edit Drive End */


      /* Edit Package Start */

      public static function EditPkg()
      {
            $operator   = Input::get('operator'); 
            $pkg_type   = Input::get('pkg_type'); 
            $pkgname    = Input::get('pkgname'); 
            $amount     = Input::get('amount');      
            $commission = Input::get('commission');         
            $status     = Input::get('active');            
            $pkgid      = Input::get('pkgid');
            $balance    = $amount - $commission; 

                        
            $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;            
              $pkgname     = stripcslashes($pkgname);
              $amount        = stripcslashes($amount);
              $commission    = stripcslashes($commission);           
           
              $pkgname    = mysqli_real_escape_string($connect, $pkgname);
              $amount       = mysqli_real_escape_string($connect, $amount);
              $commission   = mysqli_real_escape_string($connect, $commission);           
                    
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE package SET operator_id='$operator',package_name='$pkgname',amount='$amount',commission='$commission',balance='$balance',status='$status',pkg_type='$pkg_type' WHERE id ='$pkgid'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('package_list.php'); 
           
         
      }
  
      /* Edit Package End */

      
      /* Edit Commission Start */

      public static function CommissionUp()
      {
            $id   = Input::get('comid'); 
            $subadmin_com = Input::get('l5com'); 
            $level4_com   = Input::get('l4com'); 
            $level3_com   = Input::get('l3com'); 
            $level2_com   = Input::get('l2com'); 
            $level1_com   = Input::get('l1com');            
                     
            $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;            
              $id    = stripcslashes($id);
              $subadmin_com  = stripcslashes($subadmin_com);
              $level4_com    = stripcslashes($level4_com);           
              $level3_com    = stripcslashes($level3_com);           
              $level2_com    = stripcslashes($level2_com);           
              $level1_com    = stripcslashes($level1_com);           
           
              $id    = mysqli_real_escape_string($connect, $id);
              $subadmin_com = mysqli_real_escape_string($connect, $subadmin_com);
              $level4_com   = mysqli_real_escape_string($connect, $level4_com);           
              $level3_com   = mysqli_real_escape_string($connect, $level3_com);           
              $level2_com   = mysqli_real_escape_string($connect, $level2_com);           
              $level1_com   = mysqli_real_escape_string($connect, $level1_com);           
                    
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE commission SET subadmin='$subadmin_com',level4='$level4_com',level3='$level3_com',level2='$level2_com',level1='$level1_com' WHERE id ='$id'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('commission_set.php'); 
           
         
      }
  
      /* Edit Commission End */


       /* Edit Add Balance Charge Start */

       public static function AddBlanceCharge()
       {
             $bkpersonal_charge = Input::get('bkashp'); 
             $bkpayment_charge  = Input::get('bkashpm'); 
             $rkpersonal_charge = Input::get('rocketp'); 
             $rkpayment_charge  = Input::get('rocketpm'); 
             $ngpersonal_charge = Input::get('nagadp'); 
             $ngpayment_charge  = Input::get('nagadpm');           
                      
          
            /* Function for prevent of sql injection */
            
               global $connect;          
               $bkpersonal_charge  = stripcslashes($bkpersonal_charge);
               $bkpayment_charge   = stripcslashes($bkpayment_charge);           
               $rkpersonal_charge  = stripcslashes($rkpersonal_charge);           
               $rkpayment_charge   = stripcslashes($rkpayment_charge);           
               $ngpersonal_charge  = stripcslashes($ngpersonal_charge);
               $ngpayment_charge   = stripcslashes($ngpayment_charge);           
            
               $bkpersonal_charge = mysqli_real_escape_string($connect, $bkpersonal_charge);
               $bkpayment_charge  = mysqli_real_escape_string($connect, $bkpayment_charge);
               $rkpersonal_charge = mysqli_real_escape_string($connect, $rkpersonal_charge);           
               $rkpayment_charge  = mysqli_real_escape_string($connect, $rkpayment_charge);           
               $ngpersonal_charge = mysqli_real_escape_string($connect, $ngpersonal_charge);           
               $ngpayment_charge  = mysqli_real_escape_string($connect, $ngpayment_charge);           
                     
              
           /* End of Function for prevent of sql injection */
   
                                        
                   $query = "UPDATE addfund_charge SET bkash_personal='$bkpersonal_charge',bkash_payment='$bkpayment_charge',rocket_personal='$rkpersonal_charge',rocket_payment='$rkpayment_charge',nagad_personal='$ngpersonal_charge',nagad_payment='$ngpayment_charge' WHERE id ='1'";
                   global $connect; 
                   $result = mysqli_query($connect, $query);    
                   if(!$result)
                   {                      
                       die("Query Failed!" . mysqli_error($connect));
                   }
                   Session::Flush('success', 'Successfully Updated!');
                   Redirect::to('balance_charge_set.php'); 
            
          
       }
   
       /* Edit Add Balance Charge End */
 



      /* Edit Account Fee Start */

      public static function acfeeup()
      {
            $id           = Input::get('comid'); 
            $subadmin_com = Input::get('l5com'); 
            $level4_com   = Input::get('l4com'); 
            $level3_com   = Input::get('l3com'); 
            $level2_com   = Input::get('l2com'); 
            $level1_com   = Input::get('l1com');            
                     
            $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;            
              $id    = stripcslashes($id);
              $subadmin_com  = stripcslashes($subadmin_com);
              $level4_com    = stripcslashes($level4_com);           
              $level3_com    = stripcslashes($level3_com);           
              $level2_com    = stripcslashes($level2_com);           
              $level1_com    = stripcslashes($level1_com);           
           
              $id    = mysqli_real_escape_string($connect, $id);
              $subadmin_com = mysqli_real_escape_string($connect, $subadmin_com);
              $level4_com   = mysqli_real_escape_string($connect, $level4_com);           
              $level3_com   = mysqli_real_escape_string($connect, $level3_com);           
              $level2_com   = mysqli_real_escape_string($connect, $level2_com);           
              $level1_com   = mysqli_real_escape_string($connect, $level1_com);           
                    
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE account_fee SET subadmin='$subadmin_com',level4='$level4_com',level3='$level3_com',level2='$level2_com',level1='$level1_com' WHERE id ='$id'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('regi_fee.php'); 
           
         
      }
  
      /* Edit Account Fee End */

      /* Edit Bill Pay Charge Start */

      public static function billpaycharge()
      {
            $id           = Input::get('comid');             
            $slot1_charge   = Input::get('slot1'); 
            $slot2_charge   = Input::get('slot2'); 
            $slot3_charge   = Input::get('slot3'); 
            $slot4_charge   = Input::get('slot4'); 
                       
                     
            $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;            
              $id            = stripcslashes($id);              
              $slot1_charge  = stripcslashes($slot1_charge);           
              $slot2_charge  = stripcslashes($slot2_charge);           
              $slot3_charge  = stripcslashes($slot3_charge);           
              $slot4_charge  = stripcslashes($slot4_charge);           
                  
           
              $id    = mysqli_real_escape_string($connect, $id);              
              $slot1_charge   = mysqli_real_escape_string($connect, $slot1_charge);           
              $slot2_charge   = mysqli_real_escape_string($connect, $slot2_charge);           
              $slot3_charge   = mysqli_real_escape_string($connect, $slot3_charge);           
              $slot4_charge   = mysqli_real_escape_string($connect, $slot4_charge);           
             
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE billpay_charge SET slot1='$slot1_charge',slot2='$slot2_charge',slot3='$slot3_charge',slot4='$slot4_charge' WHERE id ='$id'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('billpay_charge.php'); 
           
         
      }
  
      /* Edit Bill Pay Charge End */


       /* Level name change Start */

      public static function levelchange()
      {
            $id             = Input::get('id');             
            $admin_name     = Input::get('admin'); 
            $subadmin_name  = Input::get('subadmin');
            $level4_name    = Input::get('level4');
            $level3_name    = Input::get('level3');
            $level2_name    = Input::get('level2');
            $level1_name    = Input::get('level1');
            
                     
            $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;            
              $id            = stripcslashes($id);              
              $admin_name    = stripcslashes($admin_name);           
              $subadmin_name = stripcslashes($subadmin_name);           
              $level4_name   = stripcslashes($level4_name);
              $level3_name   = stripcslashes($level3_name); 
              $level2_name   = stripcslashes($level2_name); 
              $level1_name   = stripcslashes($level1_name);            
                
                  
           
              $id    = mysqli_real_escape_string($connect, $id);              
              $admin_name     = mysqli_real_escape_string($connect, $admin_name);           
              $subadmin_name  = mysqli_real_escape_string($connect, $subadmin_name);           
              $level4_name    = mysqli_real_escape_string($connect, $level4_name); 
              $level3_name    = mysqli_real_escape_string($connect, $level3_name);
              $level2_name    = mysqli_real_escape_string($connect, $level2_name);
              $level1_name    = mysqli_real_escape_string($connect, $level1_name);         
                      
             
             
          /* End of Function for prevent of sql injection */
  
                                       
                  $query = "UPDATE level_name SET admin='$admin_name',subadmin='$subadmin_name',level4='$level4_name',level3='$level3_name',level2='$level2_name',level1='$level1_name' WHERE id ='$id'";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('level_name.php'); 
           
         
      }
  
      /* Level name change End */


      
      /* Edit Package Start */

      public static function EditSim()
      {
            $user = Input::get('username'); 
            $userid = Input::get('userid');          
            $phone = Input::get('mobile');      
            $pin_code = Input::get('pincode');         
            $reseller_active = Input::get('active');
            $cs_type = Input::get('level');
            $sim_id = Input::get('simid');
            

                        
            $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;            
              $user     = stripcslashes($user);
              $userid   = stripcslashes($userid);
              $phone    = stripcslashes($phone);           
              $pin_code = stripcslashes($pin_code);           
              $cs_type  = stripcslashes($cs_type);           
           
              $user     = mysqli_real_escape_string($connect, $user);
              $userid   = mysqli_real_escape_string($connect, $userid);
              $phone    = mysqli_real_escape_string($connect, $phone);           
              $pin_code = mysqli_real_escape_string($connect, $pin_code);           
              $cs_type  = mysqli_real_escape_string($connect, $cs_type);           
                    
             
          /* End of Function for prevent of sql injection */


                /*Check Duplicate*/

                $query = "SELECT user_name FROM veri_number where user_name='$user' and id!='$sim_id'";   //username     
                global $connect; 
                $result = mysqli_query($connect, $query);
                
                if(!$result)
                {
                    die("Query Failed!" . mysqli_error($connect));      
                }        
                
                $count = mysqli_num_rows($result); 

                $query1 = "SELECT user_id FROM veri_number where user_id='$userid' and id!='$sim_id'";   //userid     
                $result1 = mysqli_query($connect, $query1);
                
                if(!$result1)
                {
                    die("Query Failed!" . mysqli_error($connect));      
                }        
                
                $count1 = mysqli_num_rows($result1); 

                $query2 = "SELECT user_mobile_no FROM veri_number where user_mobile_no='$phone' and id!='$sim_id'";   //phonenumber     
                $result2 = mysqli_query($connect, $query2);
                
                if(!$result2){
                    die("Query Failed!" . mysqli_error($connect));      
                }        
                
                $count2 = mysqli_num_rows($result2); 

                 /*Check Duplicate end*/


                if($count > 0)
                {
                    Session::Flush('error', 'Username already registered! Please check sim list');
                    Redirect::to('sim_edit.php?sim_id='.$sim_id);   
                }
                elseif($count1 > 0)
                {
                    Session::Flush('error', 'User ID already registered! Please check sim list');
                    Redirect::to('sim_edit.php?sim_id='.$sim_id);   
                }
                elseif($count2 > 0)
                {
                    Session::Flush('error', 'Phone Number already registered! Please check sim list');
                    Redirect::to('sim_edit.php?sim_id='.$sim_id);   
                }
                else
                {
                                                       
                  $query3 = "UPDATE veri_number SET user_name='$user',user_id='$userid',user_mobile_no='$phone',pin='$pin_code',active='$reseller_active',level='$cs_type' WHERE id ='$sim_id'";                  
                  $result3 = mysqli_query($connect, $query3);    
                  if(!$result3)
                  {                      
                      die("Query Failed!" . mysqli_error($connect));
                  }
                  Session::Flush('success', 'Successfully Updated!');
                  Redirect::to('sim_edit.php?sim_id='.$sim_id);
                  
                }
           
         
      }
  
      /* Edit Package End */



}


?>