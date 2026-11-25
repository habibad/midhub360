<?php

class AddFundM
{
    /** Add Fund Request Start */
    public static function addFund(){

        $user_id         = Session::get('us_id');
        $balance_type    = Input::get('bltype');        
        $amount          = Input::get('amount');
        $mcategory       = Input::get('mcategory');
        $mtype           = Input::get('mtype');
        $account_number  = Input::get('acnumber');
        $transaction_id  = Input::get('transid');
        $sending_number  = Input::get('sendnumber'); 
        $amount          = (int)$amount;
        


        /* Find Charge start*/

        if ($mcategory == 'bKash' && $mtype =='cashin')
        {
              $charge = Charge::findcharge("1","bkash_personal");
        }
        elseif ($mcategory == 'bKash' && $mtype =='sendmoney')
        {
              $charge = Charge::findcharge("1","bkash_personal");
        }
        elseif ($mcategory == 'bKash' && $mtype =='payment')
        {
              $charge = Charge::findcharge("1","bkash_payment");
        }
        elseif ($mcategory == 'Nagad' && $mtype =='cashin')
        {
              $charge = Charge::findcharge("1","nagad_personal");
        }
        elseif ($mcategory == 'Nagad' && $mtype =='sendmoney')
        {
              $charge = Charge::findcharge("1","nagad_personal");
        }
        elseif ($mcategory == 'Nagad' && $mtype =='payment')
        {
              $charge = Charge::findcharge("1","nagad_payment");
        }
        elseif ($mcategory == 'Rocket' && $mtype =='cashin')
        {
              $charge = Charge::findcharge("1","rocket_personal");
        }
        elseif ($mcategory == 'Rocket' && $mtype =='sendmoney')
        {
              $charge = Charge::findcharge("1","rocket_personal");
        }
        elseif ($mcategory == 'Rocket' && $mtype =='payment')
        {
              $charge = Charge::findcharge("1","rocket_payment");
        }
        else
        {
              $charge = 0;
        }


        $total_charge = ($charge / 1000) * $amount;

        $total_amount = $amount - $total_charge;

        /* Find Charge end*/


        /* Function for prevent of sql injection */
     
        global $connect;
        $amount          = stripcslashes($amount);
        $account_number  = stripcslashes($account_number);
        $transaction_id  = stripcslashes($transaction_id);
        $sending_number  = stripcslashes($sending_number);           
        
        $amount            = mysqli_real_escape_string($connect, $amount);
        $account_number    = mysqli_real_escape_string($connect, $account_number);
        $transaction_id    = mysqli_real_escape_string($connect, $transaction_id);
        $sending_number    = mysqli_real_escape_string($connect, $sending_number);                     

    /* End of Function for prevent of sql injection */

        
        $query= "SELECT user, level, created_by FROM user where id='$user_id'";
        global $connect;
        $result = mysqli_query($connect, $query);

        if(!$result){
              die("Query Failed!" . mysqli_error($connect));      
        } 

        $reseller_data= mysqli_fetch_object($result);
        $created_by =$reseller_data->created_by;
        $username =$reseller_data->user;
        $user_level =$reseller_data->level;


        /*Check Duplicate Transaction ID Start*/

        $query1  = "SELECT * FROM add_fund where transaction_id='$transaction_id'";    

        $result1 = mysqli_query($connect, $query1); 
        if(!$result1)
        {
            die("Query Failed!" . mysqli_error($connect));      
        } 
       
        $count1 = mysqli_num_rows($result1);  // If result matched $user and $pass, table row must be 1 row
    
        /*Check Duplicate Transaction ID End */


        /*Check Duplicate Transaction ID from auto Start*/

        $queryauto  = "SELECT * FROM auto_addbalance where transactionid='$transaction_id'";    

        $resultauto = mysqli_query($connect, $queryauto); 
        if(!$resultauto)
        {
            die("Query Failed!" . mysqli_error($connect));      
        } 
       
        $countauto = mysqli_num_rows($resultauto);  // If result matched $user and $pass, table row must be 1 row
    
        /*Check Duplicate Transaction ID from auto End */



        if (empty($transaction_id)) 
        {               
              Session::Flush('error', 'You must enter Transaction ID');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif ($count1 >= 1) 
        {               
              Session::Flush('error', 'You have already submitted with the same Transaction ID.');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif ($countauto >= 1) 
        {               
              Session::Flush('error', 'এই ট্রানজেকশনটি অটো সিস্টেমে ব্যবহার হয়েছে। বিস্তারিত জানতে সাপোর্টে যোগাযোগ করুন। (This Transaction already used in auto system. Please conatct with support) ');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif (strlen($transaction_id) > 20)
        {
              Session::Flush('error', 'Transaction ID needs to have less than 20 characters!');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif (!preg_match("/^([A-Za-z0-9])+$/", $transaction_id))           
        {           
              Session::Flush('error', 'Transaction ID only allow letter and number! Space not allowed!');
              Redirect::to('add_fund_mbank.php');
        }
        elseif (empty($amount)) 
        {               
              Session::Flush('error', 'You must enter amount!');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif(!is_numeric($amount)) 
        {                
            Session::Flush('error', 'Amount allow only number!');
            Redirect::to('add_fund_mbank.php');
        } 
        elseif (strlen($amount) > 7)
        {
              Session::Flush('error', 'Amount needs to have less than 7 digit!');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif (empty($account_number)) 
        {               
              Session::Flush('error', 'You must enter Payment Number!');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif(!is_numeric($account_number)) 
        {                
            Session::Flush('error', 'Payment Number allow only number!');
            Redirect::to('add_fund_mbank.php');
        } 
        elseif (strlen($account_number) > 12)
        {
              Session::Flush('error', 'Payment Number needs to have less than 13 digit!');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif (empty($sending_number)) 
        {               
              Session::Flush('error', 'You must enter Sending Number!');
              Redirect::to('add_fund_mbank.php'); 
        }
        elseif(!is_numeric($sending_number)) 
        {                
            Session::Flush('error', 'Sending Number allow only number!');
            Redirect::to('add_fund_mbank.php');
        } 
        elseif (strlen($sending_number) > 12)
        {
              Session::Flush('error', 'Sending Number needs to have less than 13 digit!');
              Redirect::to('add_fund_mbank.php'); 
        }
        else 
        {

              $query  = "INSERT INTO add_fund (id,user_id,username,level,boss_id,balance_type,category,mcategory,type,balance,charge,amount,account_name,account_number,transaction_id,sending_number,date,status,comment,document)";
              $query .= "VALUES(0,'$user_id','$username','$user_level','$created_by','$balance_type','Mobile Banking','$mcategory','$mtype','$amount','$total_charge','$total_amount','0','$account_number','$transaction_id','$sending_number',NOW(),'0', '0','0')"; //Used concatenation (.) add two string
  
              $result = mysqli_query($connect, $query);    
              if(!$result){
                  
                  die("Query Failed!" . mysqli_error($connect));
              }
              
                  Session::Flush('success', 'Your request successfullly submitted. Thank You!');
                  Redirect::to('add_fund_mbank.php');  

        }


  }

  /** Add Fund Request Start End */


}

?>