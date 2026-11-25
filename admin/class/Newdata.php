<?php

class Newdata 
{

        /* Sim Registration Start*/

    public static function SimRegistration() 
    {
          $user = Input::get('username'); 
          $userid = Input::get('userid');          
          $phone = Input::get('mobile');      
          $pin_code = Input::get('pincode');         
          $reseller_active = Input::get('active');
          $cs_type = Input::get('level');
     
          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;
              
              $phone    = stripcslashes($phone);
              $userid   = stripcslashes($userid);
              $user     = stripcslashes($user);            
              $pin_code = stripcslashes($pin_code);              
             
              $phone    = mysqli_real_escape_string($connect, $phone);
              $userid   = mysqli_real_escape_string($connect, $userid);
              $user     = mysqli_real_escape_string($connect, $user);              
              $pin_code = mysqli_real_escape_string($connect, $pin_code);            
      
          /* End of Function for prevent of sql injection */



          if(empty($phone)) 
          {               
                  Session::Flush('error', 'You must enter your customer phone number!');
                  Redirect::to('sim_registration.php');
          }
          elseif (strlen($phone) < 14)
          {
            Session::Flush('error', 'Invalid Phone Number. Please start with +88');
            Redirect::to('sim_registration.php');
          }
          elseif(empty($user)) 
          {               
                  Session::Flush('error', 'You must enter your customer username!');
                  Redirect::to('sim_registration.php');
          }
          elseif(empty($userid)) 
          {               
                  Session::Flush('error', 'You must enter your customer ID no!');
                  Redirect::to('sim_registration.php');
          }
          elseif(!is_numeric($userid)) 
          {                
                Session::Flush('error', 'User ID allow only number!');
                Redirect::to('sim_registration.php');
          }
          elseif(empty($pin_code)) 
          {               
                  Session::Flush('error', 'You must enter your customer ID no!');
                  Redirect::to('sim_registration.php');
          }
          elseif(!is_numeric($pin_code)) 
          {                
                Session::Flush('error', 'Pin Code allow only number!');
                Redirect::to('sim_registration.php');
          }
          elseif (strlen($pin_code) != 4)
          {
            Session::Flush('error', 'PIN must be in 4 digits');
            Redirect::to('sim_registration.php');
          }
          else
          {
                    $query  = "INSERT INTO veri_number (id,user_name,user_id,user_mobile_no,pin,active,level)";
                    $query .= "VALUES(0,'$user','$userid','$phone','$pin_code','$reseller_active','$cs_type')"; //Used concatenation (.) add two string
            
                    $result = mysqli_query($connect, $query);    
                    if(!$result)
                    {
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                        Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                        Redirect::to('sim_list.php'); 
          }
         
  
         
     
    }

    /* Sim Registration End*/

    /* Add New Drive Start*/

    public static function AddDrive() 
    {
          
          $operator = Input::get('operator'); 
          $drivename = Input::get('drivename'); 
          $amount = Input::get('amount');      
          $commission = Input::get('commission');         
          $reseller_active = Input::get('active');
          $balance = $amount - $commission;
     
          $us_id = Session::get('us_id');	
  
  
  
           /* Function for prevent of sql injection */
           
              global $connect;

              $drivename     = stripcslashes($drivename);
              $amount        = stripcslashes($amount);
              $commission    = stripcslashes($commission);           
           
              $drivename    = mysqli_real_escape_string($connect, $drivename);
              $amount   = mysqli_real_escape_string($connect, $amount);
              $commission     = mysqli_real_escape_string($connect, $commission);             
           
          /* End of Function for prevent of sql injection */



          if(empty($drivename)) 
          {               
                  Session::Flush('error', 'You must enter drive name!');
                  Redirect::to('drive_add.php');
          }        
          elseif(empty($amount)) 
          {               
                  Session::Flush('error', 'You must enter Amount!');
                  Redirect::to('drive_add.php');
          }
          elseif(!is_numeric($amount)) 
          {                
                Session::Flush('error', 'Amount allow only number!');
                Redirect::to('drive_add.php');
          }
          elseif(empty($commission)) 
          {               
                  Session::Flush('error', 'You must enter Commission! If have no commission enter 0');
                  Redirect::to('drive_add.php');
          }
          elseif(!is_numeric($commission)) 
          {                
                Session::Flush('error', 'Commission allow only number!');
                Redirect::to('drive_add.php');
          }          
          else
          {
                    $query  = "INSERT INTO drive (id,operator_id,drive_name,amount,commission,balance,status)";
                    $query .= "VALUES(0,'$operator','$drivename','$amount','$commission','$balance','$reseller_active')"; //Used concatenation (.) add two string
            
                    $result = mysqli_query($connect, $query);    
                    if(!$result)
                    {
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }
                    
                        Session::Flush('success', 'New drive Successfully Added. Thank You!');
                        Redirect::to('drive_list.php'); 
          }
         
  
         
     
    }
    
/* Add New Drive End*/



/* Add New Package Start*/

public static function AddPackage() 
{
      
      $operator = Input::get('operator');
      $pkg_type = Input::get('pkg_type');
      $pkgname  = Input::get('pkgname'); 
      $amount   = Input::get('amount');      
      $commission = Input::get('commission');         
      $reseller_active = Input::get('active');
      $balance = $amount - $commission;
 
      $us_id = Session::get('us_id');	



       /* Function for prevent of sql injection */
       
          global $connect;

          $pkgname     = stripcslashes($pkgname);
          $amount        = stripcslashes($amount);
          $commission    = stripcslashes($commission);           
       
          $pkgname    = mysqli_real_escape_string($connect, $pkgname);
          $amount   = mysqli_real_escape_string($connect, $amount);
          $commission     = mysqli_real_escape_string($connect, $commission);             
       
      /* End of Function for prevent of sql injection */



      if(empty($pkgname)) 
      {               
              Session::Flush('error', 'You must enter Package name!');
              Redirect::to('package_add.php');
      }        
      elseif(empty($amount)) 
      {               
              Session::Flush('error', 'You must enter Amount!');
              Redirect::to('package_add.php');
      }
      elseif(!is_numeric($amount)) 
      {                
            Session::Flush('error', 'Amount allow only number!');
            Redirect::to('package_add.php');
      }
      elseif(empty($commission)) 
      {               
              Session::Flush('error', 'You must enter Commission! If have no commission enter 0');
              Redirect::to('package_add.php');
      }
      elseif(!is_numeric($commission)) 
      {                
            Session::Flush('error', 'Commission allow only number!');
            Redirect::to('package_add.php');
      }          
      else
      {
                $query  = "INSERT INTO package (id,operator_id,package_name,amount,commission,balance,status,pkg_type)";
                $query .= "VALUES(0,'$operator','$pkgname','$amount','$commission','$balance','$reseller_active','$pkg_type')"; //Used concatenation (.) add two string
        
                $result = mysqli_query($connect, $query);    
                if(!$result)
                {
                    
                    die("Query Failed!" . mysqli_error($connect));
                }
                
                    Session::Flush('success', 'New package Successfully Added. Thank You!');
                    Redirect::to('package_list.php'); 
      }
     

     
 
}

/* Add New Package End*/




}

?>