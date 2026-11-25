<?php
class Account
{

           
      public static function myRate()
      {

            $userId  = Session::get('userId');
            $prefix  = Input::get('prefix');
            $service = Input::get('service');
            if ($prefix != '' && $service != '') {
                  $sql = "SELECT * FROM rate_module WHERE service = '$service' AND prefix='$prefix' AND userid = '$userId' ORDER BY id DESC ";
            } elseif ($prefix != '') {
                  $sql = "SELECT * FROM rate_module WHERE prefix='$prefix' AND userid = '$userId' ORDER BY id DESC ";
            } elseif ($service != '') {
                  $sql = "SELECT * FROM rate_module WHERE service = '$service' AND userid = '$userId' ORDER BY id DESC ";
            } else {
                  $sql = "SELECT * FROM rate_module WHERE userid = '$userId' ORDER BY id DESC ";
            }
            return $sql;
      }

      public static function countMyRate()
      {
            return DB::getInstance()->get(self::myRate())->Count();
      }

      
      
      public static function validpin($pin)
      {
        $error = "";
        
            if (empty($pin)) 
            {               
                $error = 'You must enter your Pin Code!';
            }
            elseif(!is_numeric($pin)) 
            {                
                  $error = 'Pin Code allow only number!';                   
            }
            elseif ($pin == 0)
            {
                  $error = 'Only number value is allowed for Pin Code!';                  
            }
            elseif (strlen($pin) != 6)
            {
                $error = 'PIN must be in 6 digits!';                
            }
            elseif ($pin == "123456" or $pin == "654321")
            {
                $error = 'Your given PIN is not allowed. Try to stronger PIN code!'; 
                
            }           
            else
            {
            $error = "";
            }

        return $error;
    }
      
      
      
      /** change pin function */
      public static function changePin()
      {
            $userId = Session::get('us_id');
            $oldPin = Input::get('oldpin');
            $pin1   = Input::get('pin1');
            $pin2   = Input::get('pin2');            
           
            /* Function for prevent of sql injection */
         
            global $connect;
            $oldPin = stripcslashes($oldPin);
            $pin1 = stripcslashes($pin1);
            $pin2 = stripcslashes($pin2);            
           
            $oldPin = mysqli_real_escape_string($connect, $oldPin);
            $pin1 = mysqli_real_escape_string($connect, $pin1);
            $pin2 = mysqli_real_escape_string($connect, $pin2);
                              
    
        /* End of Function for prevent of sql injection */

           $query= "SELECT pin_code from user where id='$userId'";
           global $connect;
           $result = mysqli_query($connect, $query);

           if(!$result){
           die("Query Failed!" . mysqli_error($connect));      
           } 

           $reseller_data = mysqli_fetch_object($result);
           $get_pin  = $reseller_data->pin_code;
           $oldPin = md5($oldPin);

           //$count = mysqli_num_rows($result);

           $is_valid_pin     = self::validPin($pin1);
                      
           if ($get_pin == $oldPin)           
            {
                                               
                  if ($is_valid_pin != "")
                 {
                       Session::Flush('error', $is_valid_pin);
                       Redirect::to('change_pin.php');
                 }                 
                 elseif ($pin1 != $pin2) 
                 {
                 
                       Session::Flush('error', 'Your confirm and new pin code does Not match');
                       Redirect::to('change_pin.php');
                 }                                        
                 else 
                 {
                        $main   = md5($pin2);
                       
                       $query = "UPDATE user SET pin_code = '$main' WHERE id = $userId";
                       global $connect; 
                       $result = mysqli_query($connect, $query);    
                       if(!$result)
                       {                      
                       die("Query Failed!" . mysqli_error($connect));
                       }

                       Session::Flush('success', 'Dear Client, Your Pin Code has been Changed Successfully!');
                       Redirect::to('change_pin.php');
                 }
                
           } 
           else 
           {
                 Session::Flush('error', 'Your old Pin Code does Not match');
                 Redirect::to('change_pin.php');
           }                


      }

      
      
      public static function validPassword($password)
      {
          $error = "";
          if (strlen($password) < 6)
          {
              $error = 'Password need to have at least 6 characters!';
          }
          elseif (strlen($password) > 30)
          {
              $error = 'Password needs to have less than 30 characters!';
          }
          elseif (!preg_match("#[0-9]+#", $password))
          {
              $error = 'Password must include at least one number!';
          }
          elseif (!preg_match("#[a-z]+#", $password))
          {
              $error = 'Password must include at least one Lowercase letter!';
          }
          elseif (!preg_match("#[A-Z]+#", $password))
          {
              $error = 'Password must include at least one uppercase letter!';
          }
          elseif (!preg_match("/^([A-Za-z0-9@#])+$/", $password)) 
          //(!preg_match ("/^[a-zA-z]*$/", $name)
          {
  
              $error = 'Password only allow letter and number. Also allow @ and # symbol.  Space not allowed.';
          }
          elseif (empty($password)) 
              {               
                  $error = 'You must enter your customer Password!';
              }
          else
          {
              $error = "";
          }
  
          return $error;
      }
      
           
      public static function changePassword()
      {
            $userId    = Session::get('us_id');
            $xpassword = Input::get('xpassword');
            $npassword = Input::get('npassword');
            $cpassword = Input::get('cpassword');
            //$new       = md5($cpassword);
            
             /* Function for prevent of sql injection */
         
             global $connect;
             $xpassword = stripcslashes($xpassword);
             $npassword = stripcslashes($npassword);
             $cpassword = stripcslashes($cpassword);            
            
             $xpassword = mysqli_real_escape_string($connect, $xpassword);
             $npassword = mysqli_real_escape_string($connect, $npassword);
             $cpassword = mysqli_real_escape_string($connect, $cpassword);
                               
     
         /* End of Function for prevent of sql injection */

            $query= "SELECT pass from user where id='$userId'";
            global $connect;
            $result = mysqli_query($connect, $query);

            if(!$result){
            die("Query Failed!" . mysqli_error($connect));      
            } 

            $reseller_data = mysqli_fetch_object($result);
            $get_password  = $reseller_data->pass;
            $xpassword = md5($xpassword);

            //$count = mysqli_num_rows($result);

            $is_valid_pass     = self::validPassword($npassword);
                       
            if ($get_password == $xpassword)           
             {
                  
                  if ($is_valid_pass != "")
                  {
                        Session::Flush('error', $is_valid_pass);
                        Redirect::to('change_password.php');
                  }                 
                  elseif ($npassword != $cpassword) 
                  {
                  
                        Session::Flush('error', 'Your confirm and new password does Not match');
                        Redirect::to('change_password.php');
                  }                                        
                  else 
                  {
                        $new = md5($cpassword);
                        
                        // $update = "UPDATE user SET pass = '$new' WHERE id = $userId";
			      $query = "UPDATE user SET pass = '$new' WHERE id = $userId";
                        global $connect; 
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {                      
                        die("Query Failed!" . mysqli_error($connect));
                        }

                        Session::Flush('success', 'Dear Client, Your password has been Changed Successfully!');
                        Redirect::to('change_password.php');
                  }
             
            } 
            else 
            {
                  Session::Flush('error', 'Your old Password does Not match');
                  Redirect::to('change_password.php');
            }                 
       
      }


 /*Start Change Phone NUmber */

 public static function changePhone()
 {
       $userId    = Session::get('us_id');
       $newphone = Input::get('newphone');
       
       //$new       = md5($cpassword);
       
        /* Function for prevent of sql injection */
    
        global $connect;
        $newphone  = stripcslashes($newphone);              
       
        $newphone = mysqli_real_escape_string($connect, $newphone);
       
    /* End of Function for prevent of sql injection */

       $query= "SELECT phone from user where id='$userId'";
       global $connect;
       $result = mysqli_query($connect, $query);

       if(!$result){
       die("Query Failed!" . mysqli_error($connect));      
       } 
           

       $count = mysqli_num_rows($result);

                         
       if ($count == 1)           
        {
             
            if (empty($newphone)) 
            {               
                Session::Flush('error', 'You must enter your new phone number');
                Redirect::to('change_phone.php');
            }
            elseif(!is_numeric($newphone)) 
            {                
                Session::Flush('error', 'Phone Number allow only number!');
                Redirect::to('change_phone.php');
            }                                    
             else 
             {
                                     
                   // $update = "UPDATE user SET pass = '$new' WHERE id = $userId";
                   $query = "UPDATE user SET phone = '$newphone' WHERE id = $userId";
                   global $connect; 
                   $result = mysqli_query($connect, $query);    
                   if(!$result)
                   {                      
                   die("Query Failed!" . mysqli_error($connect));
                   }

                   Session::Flush('success', 'Dear Client, Your Phone Number has been Changed Successfully!');
                   Redirect::to('change_phone.php');
             }
        
       } 
       else 
       {
             Session::Flush('error', 'Invalid!');
             Redirect::to('change_phone.php');
       }                 
  
 }

 /** Change Phone NUmber End */


/*Start Change Email Address */

public static function changeEmail()
{
      $userId    = Session::get('us_id');
      $newemail = Input::get('newemail');
      
          
       /* Function for prevent of sql injection */
   
       global $connect;
       $newemail  = stripcslashes($newemail);              
      
       $newemail = mysqli_real_escape_string($connect, $newemail);
      
   /* End of Function for prevent of sql injection */

      $query= "SELECT email from user where id='$userId'";
      global $connect;
      $result = mysqli_query($connect, $query);

      if(!$result){
      die("Query Failed!" . mysqli_error($connect));      
      } 
          

      $count = mysqli_num_rows($result);

                        
      if ($count == 1)           
       {
            
           if (empty($newemail)) 
           {               
               Session::Flush('error', 'You must enter your new email address!');
               Redirect::to('change_email.php');
           }
           elseif (!filter_var($newemail, FILTER_VALIDATE_EMAIL)) {            
                  Session::Flush('error', 'Invalid email format');
                  Redirect::to('change_email.php');
          }                                 
            else 
            {
                                    
                  // $update = "UPDATE user SET pass = '$new' WHERE id = $userId";
                  $query = "UPDATE user SET email = '$newemail' WHERE id = $userId";
                  global $connect; 
                  $result = mysqli_query($connect, $query);    
                  if(!$result)
                  {                      
                  die("Query Failed!" . mysqli_error($connect));
                  }

                  Session::Flush('success', 'Dear Client, Your Email Address has been Changed Successfully!');
                  Redirect::to('change_email.php');
            }
       
      } 
      else 
      {
            Session::Flush('error', 'Invalid!');
            Redirect::to('change_phone.php');
      }                 
 
}

/** Change Phone NUmber End */



 /** Change Pin Start */

public static function ChangePinCode()
      {
            $userId    = Session::get('us_id');
            $xpin = Input::get('xpin');
            $npin = Input::get('npin');
            $cpin = Input::get('cpin');
            $new       = md5($cpin);
            $verify    = Verify::isExist('user.pin_code', md5($xpin));
            if ($verify != true) {
                  if ($npin == $cpin) {

                        // $update = "UPDATE user SET pass = '$new' WHERE id = $userId";
						$update = "UPDATE user SET pin_code = '$new' WHERE id = $userId";
                        $change = DB::getInstance()->get($update);
                        if ($change) {
                              Session::Flush('success', 'Dear Client, You Pin Code has been Changed Successfully.');
                              Redirect::to('change_pin.php');
                        } else {
                              Session::Flush('error', 'Your Confirm Pin Does Not Match');
                              Redirect::to('change_pin.php');
                        }
                  } else {
                        Session::Flush('error', 'Your Confirm Pin Does Not Match');
                        Redirect::to('change_pin.php');
                  }
            } else {
                  Session::Flush('error', 'Your Old Pin Does Not Match');
                  Redirect::to('change_pin.php');
            }
      }


 /** Change Pin End */

      /** update profile */
      public static function updateProfile()
      {
            $userId    = Session::get('us_id');
            $full_name = Input::get('fullname');
            $address = Input::get('address');
            
                
             /* Function for prevent of sql injection */         
             global $connect;
             $full_name  = stripcslashes($full_name);
             $address    = stripcslashes($address);              
            
             $full_name = mysqli_real_escape_string($connect, $full_name);
             $address = mysqli_real_escape_string($connect, $address);
            
         /* End of Function for prevent of sql injection */
               
                
                 if (empty($full_name)) 
                  {               
                        Session::Flush('error', 'You must enter your fullname!');
                        Redirect::to('update_profile.php');
                  }
                  elseif (strlen($full_name) > 25)
                  {
                        Session::Flush('error', 'Fullname needs to have less than 25 characters!');
                        Redirect::to('update_profile.php');
                  }
                  elseif (!preg_match("/^([a-zA-Z ])*$/", $full_name))                        
                  {
                        Session::Flush('error', 'Fullname only allow letter and space!');
                        Redirect::to('update_profile.php');
                  }
                  elseif (empty($address)) 
                  {               
                     Session::Flush('error', 'You must enter your address!');
                     Redirect::to('update_profile.php');
                  }
                  elseif (strlen($address) > 100)
                  {
                        Session::Flush('error', 'Address needs to have less than 100 characters!');
                        Redirect::to('update_profile.php');
                  }
                  elseif (!preg_match("/^([A-Za-z0-9,-: ])*$/", $address))                  
                  {

                        Session::Flush('error', 'Address only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!');
                        Redirect::to('update_profile.php');                        
                  }                                               
                  else 
                  {
                                          
                        $query = "UPDATE user SET fullname = '$full_name', address = '$address' WHERE id = $userId";
                        global $connect; 
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {                      
                        die("Query Failed!" . mysqli_error($connect));
                        }
      
                        Session::Flush('success', 'Dear Client, Your profile has been update Successfully!');
                        Redirect::to('update_profile.php');
                  }
             
      } 
        
      

      
/*Start Forgot Password */

public static function ForgotPassword()
{
      $userId    = Session::get('us_id');            
      $npassword = Input::get('npassword');
      $cpassword = Input::get('cpassword');
      $token = Input::get('token');

      $curDate = date("Y-m-d H:i:s");

      //$new       = md5($cpassword);
      
       /* Function for prevent of sql injection */
   
       global $connect;             
       $npassword = stripcslashes($npassword);
       $cpassword = stripcslashes($cpassword);            
       $token = stripcslashes($token);            
      
       $npassword = mysqli_real_escape_string($connect, $npassword);
       $cpassword = mysqli_real_escape_string($connect, $cpassword);
       $token = mysqli_real_escape_string($connect, $token);
                         

   /* End of Function for prevent of sql injection */

      $query  = "SELECT * FROM pass_reset where token='$token'";    

      $result = mysqli_query($connect, $query); 

      $reseller_data= mysqli_fetch_object($result);

      $count = mysqli_num_rows($result);

    
      if($count == 1) 
      {
     
            $email = $reseller_data->email;
            $username = $reseller_data->username;                
            $expDate = $reseller_data->expDate;  
            $token_id  = $reseller_data->id;  
            $token_status  = $reseller_data->status;                    
            
            if ($expDate <= $curDate)
            {
                  Session::Flush('error', 'রিসেটের লিঙ্কটির মেয়াদ শেষ হয়ে গিয়েছে। ইমেইলে নতুন করে লিংক পাঠানোর জন্য চেস্টা করুন। (The link is expired)');
                  Redirect::to('password_reset.php?'.'token='.$token);
            }
            elseif ($token_status == 1)
            {
                  Session::Flush('error', '(লিঙ্কটি ব্যবহার করা হয়ে গিয়েছে। ইমেইলে নতুন করে লিংক পাঠানোর জন্য চেস্টা করুন।) The link is already used');
                  Redirect::to('password_reset.php?'.'token='.$token);
            }
            else
            {                                  
                  $query= "SELECT * FROM user where user='$username'";
                  global $connect;
                  $result = mysqli_query($connect, $query);

                  if(!$result){
                  die("Query Failed!" . mysqli_error($connect));      
                  } 

                  $reseller_data = mysqli_fetch_object($result);
                  $get_password  = $reseller_data->pass;
                  $get_user      = $reseller_data->user;
                  $get_id        = $reseller_data->id;
                  $get_email     = $reseller_data->email;
                  

                  //$count = mysqli_num_rows($result);

                        $is_valid_pass     = self::validPassword($npassword);
                        
                                    
                        if ($is_valid_pass != "")
                        {
                              Session::Flush('error', $is_valid_pass);
                              Redirect::to('password_reset.php?'.'token='.$token);
                        }                 
                        elseif ($npassword != $cpassword) 
                        {
                        
                              Session::Flush('error', 'নতুন ও কনফার্ম পাসওয়ার্ড দুইটি একই হতে হবে। (Your confirm and new password does Not match)');
                              Redirect::to('password_reset.php?'.'token='.$token);
                        }                                        
                        else 
                        {
                              $new = md5($cpassword);
                              
                              // $update = "UPDATE user SET pass = '$new' WHERE id = $userId";
                              $query = "UPDATE user SET pass = '$new' WHERE id = $get_id";
                              global $connect; 
                              $result = mysqli_query($connect, $query);    
                              if(!$result)
                              {                      
                              die("Query Failed!" . mysqli_error($connect));
                              }

                              $query2 = "UPDATE pass_reset SET status = '1' WHERE id = $token_id";
                              global $connect; 
                              $result2 = mysqli_query($connect, $query2);    
                              if(!$result2)
                              {                      
                              die("Query Failed!" . mysqli_error($connect));
                              }

                              Session::Flush('success', '(প্রিয় গ্রাহক আপনার পাসওয়ার্ড পরিবর্তন হয়েছে।) Dear Client, Your password has been Changed Successfully! <br><br><a href="index.php"><b>Click here to login</b></a>');
                              Redirect::to('password_reset.php?'.'token='.$token);
                        }
            }
      }
      else
      {
            Session::Flush('error', 'রিসেট লিঙ্কটি সঠিক নয়। (Invalid reset link!)');
            Redirect::to('password_reset.php?'.'token='.$token);
            
      }                
 
}


/*End forgot Password */



	/* admin forget password */
	public static function forget()
	{
		$userName   = Xss::xssClean(Input::get('username'));
		$yourMail   = Xss::xssClean(Input::get('youremail'));
		$yourPin    = Xss::xssClean(Input::get('yourpin'));
		$hashPin    = Hash::make($yourPin);
		//$potalMail = Company::info()['help_email'];
		$newPassword = self::randomCode();
		//$sendSms    = $_POST['sms'];
		//$sendMail   = $_POST['email'];
		$sql = "SELECT * FROM reseller WHERE username = '$userName' AND pincode = '$hashPin' AND email = '$yourMail' LIMIT 1";
		$count = DB::getInstance()->get($sql)->Count();
		$res = DB::getInstance()->get($sql)->result();
		if($count > 0){
			foreach ($res as $reset){
				$name = $reset->name;
			}

			$from     = 'security@iniload.com';
			$subject  = "Reset Your Password";
			$body     = "Mr. $name Your  Password is: $newPassword";
			$headers  = "MIME-Version: 1.0rn";
			$headers .= "Content-type: text/html; charset=iso-8859-1rn";
			$headers .= "From: Login site <$from>rn";
			$body = nl2br($body);
			$mailSuccess = @mail($yourMail, $subject, $body, $headers);
			if($mailSuccess){
				$sql1 = "UPDATE reseller SET password ='".Hash::make($newPassword)."' WHERE username = '$userName' AND pincode = '$hashPin' AND email = '$yourMail' ";
				$set = DB::getInstance()->get($sql1);
				if($set){
					Session::flush('success', 'Please Check Your Mail');
					Redirect::to('home.php');
				}
			}else{
				Session::Flush('error', 'Your Not Valid');
				Redirect::to('forget.php');
			}
		}else{
			Session::flush('error', 'Dear Client . Enter Your Valid Information');
			Redirect::to('forget.php');
		}
	}

	public static function randomCode()
	{
		$string = '6789*()poiuy*()trew6789qasdHyfghj1234MR5kl<mn12345bvcxz';
		$codeId    = '';
		$lenght    = 8;
		$charArray = str_split($string);
		for ($i = 0; $i < $lenght; $i++) {
			$random = array_rand($charArray);
			$codeId .= $charArray[$random];
		}
		return $codeId;
	}


      /* get all child balance */
      public static function childBalance()
      {
            $labelFive= [];
            $labelfour = [];
            $labelTree = [];
            $labelTwo  = [];
            $labelOne  = [];
            $fiveBalance =[];
            $fourBalance = [];
            $threeBalance = [];
            $twoBalance = [];
            $oneBalance = [];
            $userId    = Session::get('userId');
            if(Reseller::type($userId) == 'subadmin'){
                  $sql       = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller5'";
                  $resfive   = DB::getInstance()->get($sql)->result();
                  foreach ($resfive as $key5 => $value5) {
                        $fiveBalance [$value5->id]    = $value5->balance;
                        $labelFive[$value5->id]       = $value5->username;
                        $fiveId                       = $value5->id;

                        $sql3    = "SELECT * FROM reseller WHERE p_id = '$fiveId'  AND custype = 'reseller4'";
                        $resFour = DB::getInstance()->get($sql3)->result();
                        foreach ($resFour as $key4 => $value4) {
                              $fourBalance[$value4->id] = $value4->balance;
                              $labelfour[$value4->id] = $value4->username;
                              $FourId                = $value4->id;
                              $sql2   = "SELECT * FROM reseller WHERE p_id = '$FourId'  AND custype = 'reseller3'";
                              $resThree = DB::getInstance()->get($sql2)->result();
                              foreach ($resThree as $value3) {
                                    $threeBalance[$value3->id] = $value3->balance;
                                    $labelTree[$value3->id] = $value3->username;
                                    $threeId                 = $value3->id;
                                    $sql1   = "SELECT * FROM reseller WHERE p_id = '$threeId' AND custype = 'reseller2'";
                                    $restwo = DB::getInstance()->get($sql1)->result();
                                    foreach ($restwo as $value1) {
                                          $twoBalance[$value1->id] = $value1->balance;
                                          $labelTwo[$value1->id] = $value1->username;
                                          $sql   = "SELECT * FROM reseller WHERE p_id = '$threeId' AND custype = 'reseller1'";
                                          $resOne = DB::getInstance()->get($sql)->result();
                                          foreach($resOne as $value){
                                                $oneBalance[$value->id] = $value->balance;
                                                $labelOne[$value->id] = $value->username;
                                          }
                                    }
                              }
                        }
                  }
            }elseif(Reseller::type($userId) == 'reseller5'){
                  $sql       = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller4'";
                  $resfour   = DB::getInstance()->get($sql)->result();
                  foreach ($resfour as $key4 => $value4) {
                        $fourBalance [$value4->id]    = $value4->balance;
                        $labelfour[$value4->id]       = $value4->username;
                        $fourId                       = $value4->id;

                        $sql3    = "SELECT * FROM reseller WHERE p_id = '$fourId'  AND custype = 'reseller3'";
                        $resTree = DB::getInstance()->get($sql3)->result();
                        foreach ($resTree as $key3 => $value3) {
                              $threeBalance[$value3->id] = $value3->balance;
                              $labelTree[$value3->id] = $value3->username;
                              $threeId                = $value3->id;
                              $sql2   = "SELECT * FROM reseller WHERE p_id = '$threeId'  AND custype = 'reseller2'";
                              $resTwo = DB::getInstance()->get($sql2)->result();
                              foreach ($resTwo as $value2) {
                                    $twoBalance[$value2->id] = $value2->balance;
                                    $labelTwo[$value2->id] = $value2->username;
                                    $twoId                 = $value2->id;
                                    $sql1   = "SELECT * FROM reseller WHERE p_id = '$twoId' AND custype = 'reseller1'";
                                    $resOne = DB::getInstance()->get($sql1)->result();
                                    foreach ($resOne as $value1) {
                                          $oneBalance[$value1->id] = $value1->balance;
                                          $labelOne[$value1->id] = $value1->username;
                                    }
                              }
                        }
                  }
            }elseif(Reseller::type($userId) == 'reseller4'){
                  $sql       = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller3'";
                  $resfour   = DB::getInstance()->get($sql)->result();
                  foreach ($resfour as $key4 => $value4) {
                        $threeBalance [$value4->id]    = $value4->balance;
                        $labelTree[$value4->id] = $value4->username;
                        $fourId                 = $value4->id;

                        $sql3    = "SELECT * FROM reseller WHERE p_id = '$fourId'  AND custype = 'reseller2'";
                        $resTree = DB::getInstance()->get($sql3)->result();
                        foreach ($resTree as $key3 => $value3) {
                              $twoBalance [$value4->id]    = $value3->balance;
                              $labelTwo[$value3->id] = $value3->username;
                              $threeId                = $value3->id;
                              
                              $sql2   = "SELECT * FROM reseller WHERE p_id = '$threeId'  AND custype = 'reseller1'";
                              $resTwo = DB::getInstance()->get($sql2)->result();
                              foreach ($resTwo as $value2) {
                                    $oneBalance[$value2->id]=$value2->balance;
                                    $labelOne[$value2->id] = $value2->username;
                              }
                        }
                  } 
            }elseif(Reseller::type($userId) == 'reseller3'){
                  $sql       = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller2'";
                  $resfour   = DB::getInstance()->get($sql)->result();
                  foreach ($resfour as $key4 => $value4) {
                        $twoBalance [$value4->id]    = $value4->balance;
                        $labelTwo[$value4->id] = $value4->username;
                        $fourId                 = $value4->id;

                        $sql3    = "SELECT * FROM reseller WHERE p_id = '$fourId'  AND custype = 'reseller1'";
                        $resTree = DB::getInstance()->get($sql3)->result();
                        foreach ($resTree as $key3 => $value3) {
                              $oneBalance[$value3->id]=$value3->balance;
                              $labelOne[$value3->id] = $value3->username;
                        }
                  }   
            }elseif(Reseller::type($userId) == 'reseller2'){
                  $sql       = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller1'";
                  $resfour   = DB::getInstance()->get($sql)->result();
                  foreach ($resfour as $key4 => $value4) {
                        $oneBalance[$value4->id]=$value4->balance;
                        $labelOne[$value4->id] = $value4->username;
                  }   
            }
            return array_sum($fiveBalance) +array_sum($fourBalance) + array_sum($threeBalance) + array_sum($twoBalance) + array_sum($oneBalance);
      }
}