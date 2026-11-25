<?php

class Addfund
{


    /* Function for Balance Add start */


	 public static function autoaddbalance()
     {
           //$id           = Input::get('id');
           $balance_type = Input::get('bltype');
           $transid      = Input::get('transid');
           $balance      = Input::get('balance');
           $commission_m = Input::get('commission');             
           //$user_id      = Input::get('ucid');            
          
           $userId       = Session::get('us_id');

            /*Find User Data*/
          
            $querysl= "SELECT * FROM user WHERE id='$userId'";
            global $connect;                        
            $resultsl = mysqli_query($connect, $querysl);

            if(!$resultsl){
            die("Query Failed!3" . mysqli_error($connect));      
            } 

            $reseller_data= mysqli_fetch_object($resultsl);
            $recharge_balance = $reseller_data->balance2;
            $mbbalance = $reseller_data->bkash_balance;
            $uslevel   = $reseller_data->level;

            /*Find User Data*/



           $query  = "SELECT * FROM auto_addbalance where transactionid='$transid' and balance='$balance' and method='auto'";

           global $connect;

           $result = mysqli_query($connect, $query); 

           $get_data= mysqli_fetch_object($result);
           
           $count = mysqli_num_rows($result);

           if($count == 1) 
           {
                $status = $get_data->status;
                $sending_number = $get_data->number;
                $company = $get_data->company;
                $trans_type = $get_data->type;
                $amount = $get_data->amount;
                $id = $get_data->id;
                $verified = $get_data->verified;

                if($status == 1) 
                {
                    Session::Flush('error', 'ট্রাঞ্জেকশনটি একবার ব্যবহার করা হয়ে গিয়েছে। (This transaction is already used!)');
                    Redirect::to('auto_add_balance.php');         

                }
                elseif($verified == "n") 
                {
                    Session::Flush('error', 'ট্রাঞ্জেকশনটি ভেরিফাইড না। আমাদের সাপোর্টে যোগাযোগ করুন। (This transaction is not verified. Please contact with support number)');
                    Redirect::to('auto_add_balance.php');         

                }
                else
                {
                    $note = "Send Number: " . $sending_number . " TXN: " . $transid .  " Company: " . $company .  " Type: " . $trans_type;
          
           
           
                        /* Function for prevent of sql injection */            
                        
                            global $connect;
                            $commission_m  = stripcslashes($commission_m);                        
                        
                            $commission_m  = mysqli_real_escape_string($connect, $commission_m);                          
                        
                        /* End of Function for prevent of sql injection */ 


                        
                            /* Get Direct Recharge Commission */

                            $subadmin_comm   = self::findcommission('1','subadmin');
                            $l4_comm         = self::findcommission('1','level4');
                            $l3_comm         = self::findcommission('1','level3');
                            $l2_comm         = self::findcommission('1','level2');
                            $l1_comm         = self::findcommission('1','level1');

                            /* Get Direct Recharge Commission End*/

                            
                            /* Get Upper Recharge Commission */

                            $subadmin_upcomm   = self::findcommission('2','subadmin');
                            $l4_upcomm         = self::findcommission('2','level4');
                            $l3_upcomm         = self::findcommission('2','level3');
                            $l2_upcomm         = self::findcommission('2','level2');              

                            /* Get Upper Recharge Commission End*/


                            /* Get Direct M. Bank Commission */

                            $subadmin_mbcomm   = self::findcommission('3','subadmin');
                            $l4_mbcomm         = self::findcommission('3','level4');
                            $l3_mbcomm         = self::findcommission('3','level3');
                            $l2_mbcomm         = self::findcommission('3','level2');
                            $l1_mbcomm         = self::findcommission('3','level1');

                            /* Get Direct M. Bank Commission End*/

                            
                            /* Get Upper M. Bank Commission */

                            $subadmin_mbupcomm   = self::findcommission('4','subadmin');
                            $l4_mbupcomm         = self::findcommission('4','level4');
                            $l3_mbupcomm         = self::findcommission('4','level3');
                            $l2_mbupcomm         = self::findcommission('4','level2');              

                            /* Get Upper M. Bank Commission End*/


                        /*Start Update fund request*/
                        
                            
                            $query = "UPDATE auto_addbalance SET status='1' WHERE id = $id";
                            global $connect; 
                            $result = mysqli_query($connect, $query);    
                            if(!$result)
                            {                      
                            die("Query Failed!" . mysqli_error($connect));
                            }
                        

                        /*End Update fund request end*/

                                    

                                if($uslevel == 0) //Level1/Retailer Start
                                {
                                    
                                                        
                                    /* Get Boss User ID and Balance */

                                    $bossid_l2   = Mixedfn::boss_id($userId); //Dealer/Level2
                                    $bossrebl_l2 = Balance::rebalance($bossid_l2);
                                    $bossmbbl_l2 = Balance::mbbalance($bossid_l2);

                                    $bossid_l3   = Mixedfn::boss_id($bossid_l2); // DGM/Level3
                                    $bossrebl_l3 = Balance::rebalance($bossid_l3);
                                    $bossmbbl_l3 = Balance::mbbalance($bossid_l3);

                                    $bossid_l4   = Mixedfn::boss_id($bossid_l3); // Distributor/Level4
                                    $bossrebl_l4 = Balance::rebalance($bossid_l4);
                                    $bossmbbl_l4 = Balance::mbbalance($bossid_l4);

                                    $bossid_l5   = Mixedfn::boss_id($bossid_l4); // Subadmin
                                    $bossrebl_l5 = Balance::rebalance($bossid_l5);
                                    $bossmbbl_l5 = Balance::mbbalance($bossid_l5);

                                    /* Get Boss User ID and Balance End*/


                                    if ($balance_type == "Recharge")
                                    {
                                                                            
                                                $commission = ($l1_comm / 100) * $amount; 
                                                //$commission = (int)$commission; 
                                                
                                                $withcomm = $amount + $commission;
                                                
                                                $update_balance = $recharge_balance + $withcomm;
                        
                                                self::upbalance_autofund("balance2","$update_balance","$userId"); //Update Customer Balance

                                                self::insertpayhist_autofund("reseller_balance",$withcomm,$userId,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($userId,$notifica_msgre,$notifica_from);                                    
                                                

                                            /*Give commission to uppper level */

                                                if($bossid_l2 !=0 && $l2_upcomm !=0) //Dealer
                                                {
                                                    //echo "ID True"; 

                                                    $commission = ($l2_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l2 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l2"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l2,"Commission from Customer",$userId,$bossrebl_l2,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l2,$notifica_msgre,$notifica_from);
                                                

                                                }
                                                if($bossid_l3 !=0 && $l3_upcomm !=0) //DGM
                                                {
                                                    
                                                    $commission = ($l3_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l3 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l3"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l3,"Commission from Customer",$userId,$bossrebl_l3,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l3,$notifica_msgre,$notifica_from);
                                                

                                                }
                                                if($bossid_l4 !=0 && $l4_upcomm !=0) //Distributor House
                                                {
                                                    
                                                    $commission = ($l4_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l4 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l4"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossrebl_l4,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                                

                                                }
                                                if($bossid_l5 !=0 && $subadmin_upcomm !=0) //Sub Admin
                                                {
                                                    
                                                    $commission = ($subadmin_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l5 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l5"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossrebl_l5,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                                

                                                }                                                                   
                                                
                                            /*Give commission to uppper level end*/
                                
                                
                                                Session::Flush('success', 'Successfully Recharge Balance Added!');
                                                Redirect::to('auto_add_balance.php');         


                                    } 
                                    elseif ($balance_type == "Mobile Banking")          
                                    {
                                            $commission = ($l1_mbcomm / 100) * $amount; 
                                            //$commission = (int)$commission; 
                                            
                                            $withcomm = $amount + $commission;

                                            $update_balance = $mbbalance + $withcomm;

                                            self::upbalance_autofund("bkash_balance","$update_balance","$userId"); //Update Customer Balance

                                            self::insertpayhist_autofund("bkash_balance",$withcomm,$userId,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                                    
                                            $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                            $notifica_from  = "Balance Added";
                                            Notice::InsertNotification($userId,$notifica_msgre,$notifica_from); 


                                            /*Give commission to uppper level */

                                            if($bossid_l2 !=0 && $l2_mbupcomm !=0) //Dealer
                                            {
                                                //echo "ID True"; 

                                                $commission = ($l2_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l2 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l2"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l2,"Commission from Customer",$userId,$bossmbbl_l2,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l2,$notifica_msgre,$notifica_from);
                                            

                                            }
                                            if($bossid_l3 !=0 && $l3_mbupcomm !=0) //DGM
                                            {
                                            
                                                $commission = ($l3_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l3 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l3"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l3,"Commission from Customer",$userId,$bossmbbl_l3,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l3,$notifica_msgre,$notifica_from);
                                            

                                            }
                                            if($bossid_l4 !=0 && $l4_mbupcomm !=0) //Distributor House
                                            {
                                            
                                                $commission = ($l4_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l4 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l4"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossmbbl_l4,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                            

                                            }
                                            if($bossid_l5 !=0 && $subadmin_mbupcomm !=0) //Sub Admin
                                            {
                                            
                                                $commission = ($subadmin_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l5 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l5"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossmbbl_l5,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                            

                                            }                                                                   
                                        
                                            /*Give commission to uppper level end*/            
                                
                                            Session::Flush('success', 'Successfully M. Banking Balance Added!');
                                            Redirect::to('auto_add_balance.php');     


                                    }
                                    else
                                    {
                                        echo "Invalid!";
                                    }
                                    
                                } //Level1/Retailer End                  
                                elseif($uslevel == 1) //Level2/Dealer Start
                                {


                                    /* Get Boss User ID and Balance */
                                        
                                    $bossid_l3   = Mixedfn::boss_id($userId); // DGM/Level3
                                    $bossrebl_l3 = Balance::rebalance($bossid_l3);
                                    $bossmbbl_l3 = Balance::mbbalance($bossid_l3);

                                    $bossid_l4   = Mixedfn::boss_id($bossid_l3); // Distributor/Level4
                                    $bossrebl_l4 = Balance::rebalance($bossid_l4);
                                    $bossmbbl_l4 = Balance::mbbalance($bossid_l4);

                                    $bossid_l5   = Mixedfn::boss_id($bossid_l4); // Subadmin
                                    $bossrebl_l5 = Balance::rebalance($bossid_l5);
                                    $bossmbbl_l5 = Balance::mbbalance($bossid_l5);

                                    /* Get Boss User ID and Balance End*/
                                    
                                    if ($balance_type == "Recharge")
                                    {
                                                                            
                                                $commission = ($l2_comm / 100) * $amount; 
                                                //$commission = (int)$commission; 
                                                
                                                $withcomm = $amount + $commission;
                                                
                                                $update_balance = $recharge_balance + $withcomm;
                        
                                                self::upbalance_autofund("balance2","$update_balance","$userId"); //Update Customer Balance

                                                self::insertpayhist_autofund("reseller_balance",$withcomm,$userId,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($userId,$notifica_msgre,$notifica_from);                                    
                                                

                                            /*Give commission to uppper level */
                                                
                                                if($bossid_l3 !=0 && $l3_upcomm !=0) //DGM
                                                {
                                                    
                                                    $commission = ($l3_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l3 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l3"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l3,"Commission from Customer",$userId,$bossrebl_l3,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l3,$notifica_msgre,$notifica_from);
                                                

                                                }
                                                if($bossid_l4 !=0 && $l4_upcomm !=0) //Distributor House
                                                {
                                                    
                                                    $commission = ($l4_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l4 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l4"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossrebl_l4,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                                

                                                }
                                                if($bossid_l5 !=0 && $subadmin_upcomm !=0) //Sub Admin
                                                {
                                                    
                                                    $commission = ($subadmin_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l5 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l5"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossrebl_l5,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                                

                                                }                                                                   
                                                
                                            /*Give commission to uppper level end*/
                                
                                
                                                Session::Flush('success', 'Successfully Recharge Balance Added!');
                                                Redirect::to('auto_add_balance.php');         


                                    } 
                                    elseif ($balance_type == "Mobile Banking")          
                                    {
                                            $commission = ($l2_mbcomm / 100) * $amount; 
                                            //$commission = (int)$commission; 
                                            
                                            $withcomm = $amount + $commission;

                                            $update_balance = $mbbalance + $withcomm;

                                            self::upbalance_autofund("bkash_balance","$update_balance","$userId"); //Update Customer Balance

                                            self::insertpayhist_autofund("bkash_balance",$withcomm,$userId,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                                    
                                            $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                            $notifica_from  = "Balance Added";
                                            Notice::InsertNotification($userId,$notifica_msgre,$notifica_from); 


                                            /*Give commission to uppper level */
                                            
                                            if($bossid_l3 !=0 && $l3_mbupcomm !=0) //DGM
                                            {
                                            
                                                $commission = ($l3_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l3 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l3"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l3,"Commission from Customer",$userId,$bossmbbl_l3,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l3,$notifica_msgre,$notifica_from);
                                            

                                            }
                                            if($bossid_l4 !=0 && $l4_mbupcomm !=0) //Distributor House
                                            {
                                            
                                                $commission = ($l4_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l4 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l4"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossmbbl_l4,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                            

                                            }
                                            if($bossid_l5 !=0 && $subadmin_mbupcomm !=0) //Sub Admin
                                            {
                                            
                                                $commission = ($subadmin_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l5 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l5"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossmbbl_l5,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                            

                                            }                                                                   
                                        
                                            /*Give commission to uppper level end*/            
                                
                                            Session::Flush('success', 'Successfully M. Banking Balance Added!');
                                            Redirect::to('auto_add_balance.php');     


                                    }
                                    else
                                    {
                                        echo "Invalid!";
                                    }
                                    
                                } //Level2/Dealer End                  
                                elseif($uslevel == 2) //Level3/DGM Start
                                {


                                    /* Get Boss User ID and Balance */                    
                                    
                                    $bossid_l4   = Mixedfn::boss_id($userId); // Distributor/Level4
                                    $bossrebl_l4 = Balance::rebalance($bossid_l4);
                                    $bossmbbl_l4 = Balance::mbbalance($bossid_l4);

                                    $bossid_l5   = Mixedfn::boss_id($bossid_l4); // Subadmin
                                    $bossrebl_l5 = Balance::rebalance($bossid_l5);
                                    $bossmbbl_l5 = Balance::mbbalance($bossid_l5);

                                    /* Get Boss User ID and Balance End*/
                                    
                                    if ($balance_type == "Recharge")
                                    {
                                                                            
                                                $commission = ($l3_comm / 100) * $amount; 
                                                //$commission = (int)$commission; 
                                                
                                                $withcomm = $amount + $commission;
                                                
                                                $update_balance = $recharge_balance + $withcomm;
                        
                                                self::upbalance_autofund("balance2","$update_balance","$userId"); //Update Customer Balance

                                                self::insertpayhist_autofund("reseller_balance",$withcomm,$userId,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($userId,$notifica_msgre,$notifica_from);                                    
                                                

                                            /*Give commission to uppper level */
                                                
                                                
                                                if($bossid_l4 !=0 && $l4_upcomm !=0) //Distributor House
                                                {
                                                    
                                                    $commission = ($l4_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l4 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l4"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossrebl_l4,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                                

                                                }
                                                if($bossid_l5 !=0 && $subadmin_upcomm !=0) //Sub Admin
                                                {
                                                    
                                                    $commission = ($subadmin_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l5 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l5"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossrebl_l5,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                                

                                                }                                                                   
                                                
                                            /*Give commission to uppper level end*/
                                
                                
                                                Session::Flush('success', 'Successfully Recharge Balance Added!');
                                                Redirect::to('auto_add_balance.php');         


                                    } 
                                    elseif ($balance_type == "Mobile Banking")          
                                    {
                                            $commission = ($l3_mbcomm / 100) * $amount; 
                                            //$commission = (int)$commission; 
                                            
                                            $withcomm = $amount + $commission;

                                            $update_balance = $mbbalance + $withcomm;

                                            self::upbalance_autofund("bkash_balance","$update_balance","$userId"); //Update Customer Balance

                                            self::insertpayhist_autofund("bkash_balance",$withcomm,$userId,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                                    
                                            $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                            $notifica_from  = "Balance Added";
                                            Notice::InsertNotification($userId,$notifica_msgre,$notifica_from); 


                                            /*Give commission to uppper level */                              
                                        
                                            if($bossid_l4 !=0 && $l4_mbupcomm !=0) //Distributor House
                                            {
                                            
                                                $commission = ($l4_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l4 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l4"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l4,"Commission from Customer",$userId,$bossmbbl_l4,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l4,$notifica_msgre,$notifica_from);
                                            

                                            }
                                            if($bossid_l5 !=0 && $subadmin_mbupcomm !=0) //Sub Admin
                                            {
                                            
                                                $commission = ($subadmin_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l5 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l5"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossmbbl_l5,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                            

                                            }                                                                   
                                        
                                            /*Give commission to uppper level end*/            
                                
                                            Session::Flush('success', 'Successfully M. Banking Balance Added!');
                                            Redirect::to('auto_add_balance.php');     


                                    }
                                    else
                                    {
                                        echo "Invalid!";
                                    }
                                    
                                } //Level3/DGM End                  
                                elseif($uslevel == 3) //Level4/Distributor Start
                                {


                                    /* Get Boss User ID and Balance */                    
                                    
                                    $bossid_l5   = Mixedfn::boss_id($userId); // Subadmin
                                    $bossrebl_l5 = Balance::rebalance($bossid_l5);
                                    $bossmbbl_l5 = Balance::mbbalance($bossid_l5);

                                    /* Get Boss User ID and Balance End*/
                                    
                                    if ($balance_type == "Recharge")
                                    {
                                                                            
                                                $commission = ($l4_comm / 100) * $amount; 
                                                //$commission = (int)$commission; 
                                                
                                                $withcomm = $amount + $commission;
                                                
                                                $update_balance = $recharge_balance + $withcomm;
                        
                                                self::upbalance_autofund("balance2","$update_balance","$userId"); //Update Customer Balance

                                                self::insertpayhist_autofund("reseller_balance",$withcomm,$userId,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($userId,$notifica_msgre,$notifica_from);                                    
                                                

                                            /*Give commission to uppper level */                                   
                                                
                                                
                                                if($bossid_l5 !=0 && $subadmin_upcomm !=0) //Sub Admin
                                                {
                                                    
                                                    $commission = ($subadmin_upcomm / 100) * $amount; 
                                                    //$commission = (int)$commission;                                       
                                                    
                                                    $update_balance = $bossrebl_l5 + $commission;
                            
                                                    self::upbalance_autofund("balance2","$update_balance","$bossid_l5"); //Update Customer Balance
                
                                                    self::insertpayhist_autofund("reseller_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossrebl_l5,$update_balance); //Insert Payment History      
                                                
                                                    $notifica_msgre = "Congratulations! You got a commission for your customer added recharge balance. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                    $notifica_from  = "Balance Added";
                                                    Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                                

                                                }                                                                   
                                                
                                            /*Give commission to uppper level end*/
                                
                                
                                                Session::Flush('success', 'Successfully Recharge Balance Added!');
                                                Redirect::to('auto_add_balance.php');         


                                    } 
                                    elseif ($balance_type == "Mobile Banking")          
                                    {
                                            $commission = ($l4_mbcomm / 100) * $amount; 
                                            //$commission = (int)$commission; 
                                            
                                            $withcomm = $amount + $commission;

                                            $update_balance = $mbbalance + $withcomm;

                                            self::upbalance_autofund("bkash_balance","$update_balance","$userId"); //Update Customer Balance

                                            self::insertpayhist_autofund("bkash_balance",$withcomm,$userId,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                                    
                                            $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                            $notifica_from  = "Balance Added";
                                            Notice::InsertNotification($userId,$notifica_msgre,$notifica_from); 


                                            /*Give commission to uppper level */                            
                                        
                                            if($bossid_l5 !=0 && $subadmin_mbupcomm !=0) //Sub Admin
                                            {
                                            
                                                $commission = ($subadmin_mbupcomm / 100) * $amount; 
                                                //$commission = (int)$commission;                                       
                                            
                                                $update_balance = $bossmbbl_l5 + $commission;
                        
                                                self::upbalance_autofund("bkash_balance","$update_balance","$bossid_l5"); //Update Customer Balance

                                                self::insertpayhist_autofund("bkash_balance",$commission,$bossid_l5,"Commission from Customer",$userId,$bossmbbl_l5,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Congratulations! You got a commission for your customer added M. Banking balance. Commission: " . $commission . " Tk. New M. Bank Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($bossid_l5,$notifica_msgre,$notifica_from);
                                            

                                            }                                                                   
                                        
                                            /*Give commission to uppper level end*/            
                                
                                            Session::Flush('success', 'Successfully M. Banking Balance Added!');
                                            Redirect::to('auto_add_balance.php');     


                                    }
                                    else
                                    {
                                        echo "Invalid!";
                                    }
                                    
                                } //Level4/Distributor End
                                elseif($uslevel == 4) //Subadmin Start
                                {
                                    
                                    if ($balance_type == "Recharge")
                                    {
                                                                            
                                                $commission = ($subadmin_comm / 100) * $amount; 
                                                //$commission = (int)$commission; 
                                                
                                                $withcomm = $amount + $commission;
                                                
                                                $update_balance = $recharge_balance + $withcomm;
                        
                                                self::upbalance_autofund("balance2","$update_balance","$userId"); //Update Customer Balance

                                                self::insertpayhist_autofund("reseller_balance",$withcomm,$userId,$note,$userId,$recharge_balance,$update_balance); //Insert Payment History      
                                            
                                                $notifica_msgre = "Recharge Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                                $notifica_from  = "Balance Added";
                                                Notice::InsertNotification($userId,$notifica_msgre,$notifica_from);                                    
                                            
                                                Session::Flush('success', 'Successfully Recharge Balance Added! Test' . $update_balance);
                                                Redirect::to('auto_add_balance.php');         


                                    } 
                                    elseif ($balance_type == "Mobile Banking")          
                                    {
                                            $commission = ($subadmin_mbcomm / 100) * $amount; 
                                            //$commission = (int)$commission; 
                                            
                                            $withcomm = $amount + $commission;

                                            $update_balance = $mbbalance + $withcomm;

                                            self::upbalance_autofund("bkash_balance","$update_balance","$userId"); //Update Customer Balance

                                            self::insertpayhist_autofund("bkash_balance",$withcomm,$userId,$note,$userId,$mbbalance,$update_balance); //Insert Payment History      
                                    
                                            $notifica_msgre = "M. Bank Balance added to your account from Admin. Amount: " . $amount . " Tk. Commission: " . $commission . " Tk. New Balance: " . $update_balance . " Tk.";
                                            $notifica_from  = "Balance Added";
                                            Notice::InsertNotification($userId,$notifica_msgre,$notifica_from); 

                                            Session::Flush('success', 'Successfully M. Banking Balance Added!');
                                            Redirect::to('auto_add_balance.php');     


                                    }
                                    else
                                    {
                                        echo "Invalid!";
                                    }
                                    
                                } //Subadmin End
                                else
                                {
                                    echo "Invalid!";
                                }


      
                }
            }
            else
            {
                Session::Flush('error', 'আপনি ভুল তথ্য দিয়েছেন। (Invalid Information!)');
                Redirect::to('auto_add_balance.php');     

            }
       
                
      
     } 

     /* End Function for Balance Add resquest*/



      /*Start Function for find commission*/

      public static function findcommission($id,$level)
      {

      $query= "SELECT * FROM commission WHERE id='$id'";
      global $connect;
      $result = mysqli_query($connect, $query);

      if(!$result){
          die("Query Failed!" . mysqli_error($connect));      
      } 

      $row= mysqli_fetch_object($result);
      $commission =$row->$level ?? 0;
      return $commission;
      }

      /*End Function for recharge balance */

      
    /*Start Function for update balance from auto add*/

    public static function upbalance_autofund($balance,$update_balance,$userid)
    {
       
        $query = "UPDATE user SET $balance='$update_balance' WHERE id='$userid'";                                    
        global $connect;
        $result = mysqli_query($connect, $query);    
        if(!$result)
        {                      
        die("Query Failed4!" . mysqli_error($connect));
        }

    }

   /*Start Function for update balance from auto add end*/


    /*Start Function for update balance from auto add*/

    public static function insertpayhist_autofund($payment,$withcomm,$user_id,$note,$userId,$recharge_balance,$update_balance)
    {

        $ip =$_SERVER["REMOTE_ADDR"];

        $queryin  = "INSERT INTO  $payment (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
        $queryin .= "VALUES(0,'$user_id','$withcomm',CURDATE(),'$userId',NOW(),'$note','$ip','0','$recharge_balance','$update_balance')"; 
        global $connect;         
        $resultin = mysqli_query($connect, $queryin);    
        if(!$resultin)
        {
                    
        die("Query Failed2!" . mysqli_error($connect));
        }

    }

   /*Start Function for update balance from auto add end*/



    /*Start Function for add balance status*/

    public static function AddbalanceStatus()
    {

    $query= "SELECT * FROM addfund_settings WHERE id='1'";
    global $connect;
    $result = mysqli_query($connect, $query);

    if(!$result){
        die("Query Failed!" . mysqli_error($connect));      
    } 

    $row= mysqli_fetch_object($result);
    $status = $row->status;
    return $status;
    }

    /*End Function for add balance status*/


}

?>