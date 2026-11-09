<?php
include "db.php";
include "class/Input.php";


/*Start Function for get User Pin code*/

function branddata($para)
{

$query= "SELECT * from branding where id='1'";
global $connect;
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
} 

$reseller_data= mysqli_fetch_object($result);
$getdata =$reseller_data->$para;
return $getdata;
}

/*End Function for get User Pin code*/



/*Start Function for Company Data*/

function CompanyData($para)
{

$query= "SELECT * from company where id='1'";
global $connect;
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
} 

$reseller_data= mysqli_fetch_object($result);
$getdata =$reseller_data->$para;
return $getdata;
}

/*End Function for Company Data*/



/*Start Function for get User Pin code*/

function user_pin_code($user_id)
{

$query= "SELECT pin_code from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
} 

$reseller_data= mysqli_fetch_object($result);
$user_pin =$reseller_data->pin_code;
return $user_pin;
}

/*End Function for get User Pin code*/


/*Start Function for Created By*/

function created_by($user_id)
{

$query= "SELECT created_by from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);

if(!$result){
    die("Query Failed!" . mysqli_error($connect));      
} 

$reseller_data= mysqli_fetch_object($result);
$name =$reseller_data->created_by ?? 0;
return $name;
}

/*End Function for Created By*/

/*Start Function for get Username*/

function username($user_id)
{

$query= "SELECT user from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$username =$reseller_data->user ?? 0;
//echo "$username";
return $username;
}

/*End Function for get Username*/

/*Start Function for level name*/

function cs_level($user_id)
          {

          $query= "SELECT level from user where id='$user_id'";
          global $connect;
          $result = mysqli_query($connect, $query);
          $reseller_data= mysqli_fetch_object($result);
          $user_level= $reseller_data->level ;
          $u_level  = $user_level;
		  if($u_level==4){$u_level="Sub Admin";}
		  elseif($u_level==3){$u_level="Distributor";}
		  elseif($u_level==2){$u_level="DGM";}
		  elseif($u_level==1){$u_level="Dealer";}
		  elseif($u_level==0){$u_level="Retailer";}
		  elseif($u_level==5){$u_level="Admin";}
		  elseif($u_level==6){$u_level="Operator";}
			else {
				echo "Unknown";
			}
		  //echo "$u_level";	
        return $u_level;          
      }	
/*End Function for level name*/


/*Start Function for get Fullname*/

function fullname($user_id)
{

$query= "SELECT fullname from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$fullname =$reseller_data->fullname;
//echo "$username";
return $fullname;
}

/*End Function for get Fullname*/

/*Start Function for get Address*/

function address($user_id)
{

$query= "SELECT address from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$address =$reseller_data->address;
//echo "$username";
return $address;
}

/*End Function for get Address*/


/*Start Function for get Email Address*/

function email_address($user_id)
{

$query= "SELECT email from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$email_address =$reseller_data->email;
//echo "$username";
return $email_address;
}

/*End Function for get Email Address*/


/*Start Function for get Phone Number*/

function phone_number($user_id)
{

$query= "SELECT phone from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$phone_number =$reseller_data->phone;
//echo "$username";
return $phone_number;
}

/*End Function for get Phone Number*/


/*Start Function for get Joining Date*/

function joining_date($user_id)
{

$query= "SELECT regi_date from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$regi_date =$reseller_data->regi_date;
//echo "$username";
return $regi_date;
}

/*End Function for get Joining Date*/


/*Start Function for get Recharge Balance*/

function recharge_balance($user_id)
{

$query= "SELECT balance2 from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$balance =$reseller_data->balance2;
//echo "$username";
return $balance;
}

/*End Function for get Recharge Balance*/


/*Start Function for get Mobile Banking Balance*/

function mbanking_balance($user_id)
{

$query= "SELECT bkash_balance from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$balance =$reseller_data->bkash_balance;
//echo "$username";
return $balance;
}

/*End Function for get Recharge Balance*/


/*Start Function for get Limit Balance*/

function limit_balance($user_id)
{

$query= "SELECT limit_balance from user where id='$user_id'";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$limit_balance =$reseller_data->limit_balance;
//echo "$username";
return $limit_balance;
}

/*End Function for Title (User list)*/


/*Start Function for get Limit Balance*/

function operator_balance($user_id)
{

$query= "SELECT balance from reportserver where company='$user_id' ORDER BY id DESC LIMIT 1";
global $connect;
$result = mysqli_query($connect, $query);
$reseller_data= mysqli_fetch_object($result);
$op_balance =$reseller_data->balance ?? 0;
//echo "$username";
return $op_balance;
}

/*End Function for Title (User list)*/



function level_title($user_id)
          {

          $query= "SELECT level from user where id='$user_id'";
          global $connect;
          $result = mysqli_query($connect, $query);
          $reseller_data= mysqli_fetch_object($result);
          $user_level= $reseller_data->level ;
          $u_level  = $user_level;
		  if($u_level==4){$u_level="Distributor";}
		  elseif($u_level==3){$u_level="DGM";}
		  elseif($u_level==2){$u_level="Dealer";}
		  elseif($u_level==1){$u_level="Retailer";}		 
		  elseif($u_level==5){$u_level="Sub Admin";}	
      elseif($u_level==0){$u_level="Retailer";}	 
			else {
				echo "Unknown";
			}
		  //echo "$u_level";	
        return $u_level;          
      }	
/*End Function for level name*/


$limit_balance   = limit_balance($us_id);
$mb_balance      = mbanking_balance($us_id);
$re_balance      = recharge_balance($us_id);
$username        = username($us_id);
$created_by      = created_by($us_id);
$userlevel       = cs_level($us_id); 
$fullname        = fullname($us_id); 
$address         = address($us_id);
$email_address   = email_address($us_id);
$phone_number    = phone_number($us_id);
$regi_date       = joining_date($us_id);
$user_pin        = user_pin_code($us_id);
$title_level     = level_title($us_id);



/*Start of Company info*/

$query= "SELECT * from company where id='1'";
//global $connect;
$result           = mysqli_query($connect, $query);
$reseller_data    = mysqli_fetch_object($result);
$company_name     = $reseller_data->name;
$company_phone    = $reseller_data->phone;
$about            = $reseller_data->about;
$company_address  = $reseller_data->address;

/*End of Company info*/



/*Start of Server on/Off*/

function check_server_status()
{
  global $connect;
  $query= "SELECT * from server_status";
  $result         = mysqli_query($connect, $query);
  $reseller_data  = mysqli_fetch_object($result);
  $server_status  = $reseller_data->status;

if($server_status==0)
{
  echo "<h1 align='center'>$reseller_data->msg; <br><a href='home.php'>Back</a></h1>";
  exit;

}
}

$page_url=basename($_SERVER["PHP_SELF"]);

if($page_url=="send_recharge.php" || $page_url=="package_minute.php" || $page_url=="package.php" || $page_url=="drive.php" || $page_url=="send_rocket.php" || $page_url=="send_nagad.php" || $page_url=="send_upay.php" || $page_url=="send_bkash.php")
{
 check_server_status();
}

/*End of Server on/Off*/


/*Start of Operator on/Off*/


$page_url=basename($_SERVER["PHP_SELF"]);

if($page_url=="send_recharge.php" || $page_url=="modal_send_request.php" || $page_url=="modal_internet_request.php")
{
      
  $phone_for_validation = Input::get('number');
                
              if($phone_for_validation!="")
            {
                $extension=$phone_for_validation[0].$phone_for_validation[1].$phone_for_validation[2];

                //echo "$extension";  


                $query = "SELECT * from operator_status where extension='$extension'";

                        $result = mysqli_query($connect, $query);

                        while($r=mysqli_fetch_object($result ))
                        {
                            $extension_status=$r->status;

                            if($extension_status==0)
                            {
                                echo "<h2 align='center'>$r->msg<br/><a href='home.php'>Back</a></h2><br/>";
                                exit;
                            }
                        }

            }   

           
        
}

/*End of Operator on/Off*/
          
?>