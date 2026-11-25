<?php
class reseller
{
    public static $reseller = [];
    public static function add()
    {
        $type = '';
        # get all reseller information from input filed
        $userName = Xss::xssClean(Input::get('username'));
        $password = Xss::xssClean(Input::get('password'));
        $pin = Xss::xssClean(Input::get('pin'));
        $name = Xss::xssClean(Input::get('name'));
        $phone = Xss::xssClean(Input::get('mobile'));
        $email = Xss::xssClean(Input::get('email'));
        $note = Xss::xssClean(Input::get('note'));
        $status = Input::get('active');
        $otp = Input::get('otptype');
        $api = Input::get('api');
        $oversale = Input::get('oversale');
        $userPin = Input::get('pincode');
        $browserAllow = Input::get('browser_allow');
        $crtype = self::type(Session::get('userId'));
        if ($crtype == 'reseller5')
        {
            $type = 'reseller4';
        }
        elseif ($crtype == 'reseller4')
        {
            $type = 'reseller3';
        }
        elseif ($crtype == 'reseller3')
        {
            $type = 'reseller2';
        }
        elseif ($crtype == 'reseller2')
        {
            $type = 'reseller1';
        }
        elseif ($crtype == 'subadmin')
        {
            $type = 'reseller5';
        }
        $permission = json_encode($_POST['per']);
        $per = $_POST['per'];
        if (count($per) > 0)
        {
            $reseller = ['username' => $userName, 'password' => Hash::make($password) , 'pincode' => Hash::make($pin) , 'type' => $permission, 'name' => $name, 'mobile' => $phone, 'email' => $email, 'note' => $note, 'custype' => $type, 'p_id' => Session::get('userId') , 'admin_id' => '', 'create_date' => date('Y-m-d') , 'ip' => $_SERVER['REMOTE_ADDR'], 'domain_name' => '', 'enbale_otp' => ($otp == '' || $otp == 0) ? 3 : $otp, 'api' => $api, 'oversale' => $oversale, 'status' => $status, 'de_pin' => $pin, 'browser_allow' => $browserAllow
            //'de_pass'     => $password,
            ];
            if (Verify::isExist('reseller.username', $userName) == true)
            {
                if (Verify::pin($userPin) === true)
                {
                    $create = DB::getInstance()->insert('reseller', $reseller);
                    $lastresellerId = $create;
                    # insert into rate_module table
                    if ($create)
                    {
                        $setRate = Rate::addRateModule($lastresellerId);
                        Session::Flush('success', 'reseller Created Successfully');
                        Redirect::to('update_rates.php?reseller=' . base64_encode($lastresellerId));
                    }
                    else
                    {
                        Session::Flush('error', 'Please Try Again');
                        Redirect::to('add_customer.php');
                    }
                }
                else
                {
                    Session::Flush('error', 'Please Enter Valid Pin');
                    Redirect::to('add_customer.php');
                }
            }
            else
            {
                Session::Flush('error', 'Your Entered userName Already Exists');
                Redirect::to('add_customer.php');
            }
        }
        else
        {
            Session::Flush('error', 'You Need To Minimum One Permission Set');
            Redirect::to('add_customer.php');
        }
    }

    // end Reseller::add() function
    
    /**
     *      reseller filter function
     */
    public static function filter()
    {
        $currentUser = Session::get('userId');
        # get all input data from filter for
        $flterUser = Input::get('cname');
        $flterName = Input::get('name');
        $mobile = Input::get('mobile');
        $email = Input::get('email');
        $sql = '';
        if ($flterUser != '' && $flterName != '' && $mobile != '' && $email != '')
        {
            $sql .= "SELECT * FROM reseller WHERE username LIKE '%$flterUser%' AND name LIKE '%$flterName' AND mobile LIKE '%$mobile%' AND email LIKE '%$email%' AND p_id ='$currentUser' ORDER BY id DESC ";
        }
        elseif ($flterUser != '')
        {
            $sql .= "SELECT * FROM reseller WHERE username LIKE '%$flterUser%' AND p_id ='$currentUser' ORDER BY id DESC ";
        }
        elseif ($flterName != '')
        {
            $sql .= "SELECT * FROM reseller WHERE name LIKE '%$flterName%' AND p_id ='$currentUser' ORDER BY id DESC ";
        }
        elseif ($mobile != '')
        {
            $sql .= "SELECT * FROM reseller WHERE mobile = '$mobile' AND p_id ='$currentUser' ORDER BY id DESC ";
        }
        elseif ($email != '')
        {
            $sql .= "SELECT * FROM reseller WHERE email LIKE '%$email%' AND p_id ='$currentUser'  ORDER BY id DESC ";
        }
        else
        {
            $sql .= "SELECT * FROM reseller WHERE p_id ='$currentUser' ORDER BY id DESC ";
        }
        return $sql;
    }
    // end filter function
    // count total filter reseller
    public static function total()
    {
        return DB::getInstance()->get(self::filter())
            ->Count();
    }
    // end total function
    
    /**
     *     function name : parent
     *      @param id int |null | this reseller id
     *      @return String
     */
    public static function parent($id = null)
    {
        $res = DB::getInstance()->get("SELECT * FROM reseller WHERE id= '$id'");
        foreach ($res->result() as $parent):
            return $parent->username;
        endforeach;
    }

    /** get parent id */
    public static function parentId($userid)
    {
        $p = '';
        $sql = "SELECT * FROM reseller WHERE id =$userid";
        foreach (DB::getInstance()->get($sql)->result() as $pi)
        {
            $p .= $pi->p_id;
        }
        return $p;
    }
    /** reseller name */
    public static function name($resellerId = '')
    {
        $res = DB::getInstance()->get("SELECT user FROM user WHERE id= $resellerId");
        foreach ($res->result() as $name):
            return $name->user;
        endforeach;
    }

    

    public static function delreseller($id = '')
    {
        $del = DB::getInstance()->get("DELETE FROM reseller WHERE id=$id");
        if ($del)
        {
            DB::getInstance()->get("DELETE FROM reseller WHERE p_id=$id");
            DB::getInstance()->get("DELETE FROM rate_module WHERE userid=$id");
        }
    }

    public static function active($id = '')
    {
        $sql = "UPDATE reseller SET status ='1' WHERE id = $id";
        $childSql = "UPDATE reseller SET status ='1' WHERE p_id =$id";
        $active = DB::getInstance()->get($sql);
        if ($active)
        {
            DB::getInstance()->get($childSql);
        }
    }

    public static function inActive($id = '')
    {
        $sql = "UPDATE reseller SET status ='0' WHERE id = $id";
        $childSql = "UPDATE reseller SET status ='0' WHERE p_id =$id";
        $active = DB::getInstance()->get($sql);
        if ($active)
        {
            DB::getInstance()->get($childSql);
        }
    }

    /*
     *     function name:getreseller() |
     *     reseller get for update
     *     @param $index = null | for array index
     *     @return data array
    */
    public static function get($index = '')
    {
        $member = [];
        $resellerId = Input::get('resId');
        $get = DB::getInstance()->get("SELECT * FROM reseller WHERE id=$resellerId");
        foreach ($get->result() as $mem):
            $member['user'] = $mem->username;
            $member['pass'] = $mem->password;
            $member['name'] = $mem->name;
            $member['phone'] = $mem->mobile;
            $member['email'] = $mem->email;
            $member['note'] = $mem->note;
            $member['pin'] = $mem->pincode;
            $member['otp'] = $mem->enbale_otp;
            $member['status'] = $mem->status;
            $member['oversale'] = $mem->oversale;
            $member['type'] = $mem->type;
            $member['domain'] = $mem->domain_name;
        endforeach;
        return $member[$index];
    }

    /** decompile reseller service permission */
    public static function decodePermission()
    {
        $per = self::get('type');
        return $permission = json_decode($per);
    }
    /** edit reseller or update reseller */

    public static function updatereseller()
    {
        $resId = Input::get('resId');
        # get all reseller information from input filed
        $selfPin = Input::get('ownpin');
        $userName = Xss::xssClean(Input::get('username'));
        $password = Xss::xssClean(Input::get('password'));
        $pin = Xss::xssClean(Input::get('upin'));
        $name = Xss::xssClean(Input::get('name'));
        $phone = Xss::xssClean(Input::get('mobile'));
        $email = Xss::xssClean(Input::get('email'));
        $note = Xss::xssClean(Input::get('note'));
        $status = Input::get('active');
        $otp = Input::get('otp');
        $oversell = Input::get('oversell');        
        $permission = json_encode($_POST['per']);
        $per = $_POST['per'];
        if (count($per) > 0)
        {
            $reseller = ['username' => $userName, 'type' => $permission, 'name' => $name, 'mobile' => $phone, 'email' => $email, 'note' => $note, 'enbale_otp' => $otp, 'oversale' => $oversell, 'status' => $status, ];
            if (!empty($password))
            {
                $reseller['password'] = Hash::make($password);
                //$reseller['de_pass']  = $password;
                
            }
            if (!empty($pin))
            {
                $reseller['pincode'] = Hash::make($pin);
                $reseller['de_pin'] = $pin;
            }
            if (Verify::pin($selfPin) === true)
            {
                $update = DB::getInstance()->update('reseller', $resId, $reseller);
                if ($update)
                {
                    Session::Flush('success', 'reseller Updated Successfully');
                    Redirect::to('reseller.php');
                }
                else
                {
                    Session::Flush('error', 'Please Try Again');
                    Redirect::to('edit-reseller.php?resId=' . $resId);
                }
            }
            else
            {
                Session::Flush('error', 'Dear Client Your Pin Verification Fail');
                Redirect::to('edit-reseller.php?resId=' . $resId);
            }
        }
        else
        {
            Session::Flush('error', 'You Need To Minimum One Permission Set');
            Redirect::to('edit-reseller.php?resId=' . $resId);
        }
    }

    /* end this function */

    /**
     *     function search for customer type
     *     @param $id |null | mixed
     *     @return String | Int |
     */
    public static function type($id = '')
    {
        $sql = "SELECT custype FROM reseller WHERE id = $id";
        $type = DB::getInstance()->get($sql)->result();
        foreach ($type as $value)
        {
            return $value->custype;
        }
    }

    public static function getInfo($id = '')
    {
        $info = [];
        $sql = DB::getInstance()->get("SELECT * FROM user WHERE id = $id")->result();
        foreach ($sql as $el)
        {
            $info['country'] = $el->country;
            $info['fullname'] = $el->fullname;
            $info['user'] = $el->user;
            $info['email'] = $el->email;
            $info['phone'] = $el->phone;
            $info['last_ip'] = $el->last_ip;
            $info['sign_date'] = $el->sign_date;
            $info['pin_code'] = $el->pin_code;
            $info['balance2'] = $el->balance2;
            $info['bkash_balance'] = $el->bkash_balance;
            $info['active'] = $el->active;
            $info['level'] = $el->level;
        }
        return $info;
    }

    public static function info($id = '', $index = '')
    {
        return self::getInfo($id) [$index];
    }

    public static function all($type = '')
    {
        $sql = "SELECT *FROM reseller WHERE custype = '$type'";
        return DB::getInstance()->get($sql)->result();
    }

    /* get reseller permission */

    public static function permissionDecode()
    {
        $currentUser = Session::get('userId');
        $per = self::info($currentUser, 'type');
        return $permission = json_decode($per);
    }

    /**
     *     get reseller by parent id
     */
    public static function childreseller()
    {
        $userId = Session::get('userId');
        $sql = "SELECT * FROM reseller WHERE p_id = '$userId' ORDER BY id DESC";
        return DB::getInstance()->get($sql)->result();
    }

    public static function Decode($index)
    {
        $id = [];
        $currentUser = Session::get('userId');
        $per = self::info($currentUser, 'type');
        $permission = json_decode($per);
        foreach ($permission as $per)
        {
            if ($per == 1)
            {
                $id['bKash'] = $per;
            }
            elseif ($per == 2)
            {
                $id['Topup'] = $per;
            }
            elseif ($per == 3)
            {
                $id['sms'] = $per;
            }
        }
        return $id[$index];
    }
    /** function for reseller sending information */

    public static function balanceUpdate($userid, $amount)
    {
        $sql = "UPDATE reseller SET balance = balance - $amount WHERE id = $userid";
        $up = DB::getInstance()->get($sql);
    }

    public static function overSale()
    {
        $id = Session::get('userId');
        $sql = "SELECT * FROM reseller WHERE id = ? AND oversale =? ";
        $query = DB::getInstance()->get($sql, array(
            $id,
            '1'
        ));
        $count = $query->Count();
        return ($count > 0) ? true : false;
    }

    public static function get_boss_id($user_id)
    {
        $query = "SELECT * from user where id='$user_id'";
        global $connect; 
        $row  = mysqli_query($connect, $query);    
        if(!$row ){
            
            die("Query Failed!" . mysqli_error($connect));
        }
        $reseller_data= mysqli_fetch_object($row);        
        $balance = $reseller_data->created_by;
        return $balance;
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


    public static function validuser($username)
    {
        $error = "";
        
        if (empty($username)) 
            {               
                $error = 'You must enter your customer Username!';
            }
        elseif (strlen($username) > 12)
        {
            $error = 'Username needs to have less than 13 characters!';
        }
        elseif (!preg_match("/^([A-Za-z0-9@])+$/", $username)) 
        //(!preg_match ("/^[a-zA-z]*$/", $name)
        {

            $error = 'Username only allow letter, number and @ Space not allowed';
        }
        else
        {
            $error = "";
        }

        return $error;
    }

    public static function validfullname($fullname)
    {
        $error = "";
        
        if (empty($fullname)) 
            {               
                $error = 'You must enter your customer Fullname!';
            }
        elseif (strlen($fullname) > 25)
        {
            $error = 'Fullname needs to have less than 25 characters!';
        }
        elseif (!preg_match("/^([a-zA-Z ])*$/", $fullname))        
        {

            $error = 'Fullname only allow letter and space!';
        }
        else
        {
            $error = "";
        }

        return $error;
    }


    public static function validaddress($address)
    {
        $error = "";
        
        if (empty($address)) 
            {               
                $error = 'You must enter your customer Address!';
            }
        elseif (strlen($address) > 100)
        {
            $error = 'Address needs to have less than 100 characters!';
        }
        elseif (!preg_match("/^([A-Za-z0-9,-: ])*$/", $address)) 
        //(!preg_match ("/^[a-zA-z]*$/", $name)
        {

            $error = 'Address only allow letter, number and space. Also allow comma (,), dashes (-), colon (:)!';
        }
        else
        {
            $error = "";
        }

        return $error;
    }


    public static function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 12; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }


    /** Start Add Reseller*/

    public static function createNewReseller()
    {
        $fullname = Input::get('full_name');
        $address = Input::get('address');;
        $city = "";
        $zip = "";
        $phone = Input::get('mobile');
        $email = Input::get('email');
        $user = Input::get('username');
        $pass = Input::get('password');
        $pin_code = Input::get('pin');
        $cs_level = Input::get('level');
        $limit_balance = Input::get('limitbl');
        $country = "";
        $reseller_active = Input::get('active');		
		$reseller_ip_block_check = Input::get('ip_block_check');
		$reseller_devices_permitted = Input::get('devices_permitted');
        $api_allow = Input::get('api_allow');
        $apikey = self::randomPassword();
        $api_key =  md5($apikey);	
		
		
        $userlevel = Session::get('level');
        $us_id = Session::get('us_id');	
		
        $boss_id=self::get_boss_id($us_id);

        $resel3_id = self::get_boss_id($us_id);

        $resel4_id = self::get_boss_id($resel3_id);
		
		$resel5_id = self::get_boss_id($resel4_id);	

        $ip	    = $_SERVER["REMOTE_ADDR"];

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

        $account_fee = 0;


        /*Start Find Account Fee*/

        $subadmin_comm = Mixedfn::findacfee("1","subadmin");
        $level4_comm   = Mixedfn::findacfee("1","level4");
        $level3_comm   = Mixedfn::findacfee("1","level3");
        $level2_comm   = Mixedfn::findacfee("1","level2");
        $level1_comm   = Mixedfn::findacfee("1","level1");
       
        /*Start Find Account Fee*/

        if($cs_level == 0)
        {
            $account_fee = $level1_comm;
        }
        if($cs_level == 1)
        {
            $account_fee = $level2_comm;
        }
        if($cs_level == 2)
        {
            $account_fee = $level3_comm;
        }
        if($cs_level == 3)
        {
            $account_fee = $level4_comm;
        }


        /*Start Find User Balance */

        $querybl= "SELECT balance2 from user where id='$us_id'";                        
        $resultbl = mysqli_query($connect, $querybl);

        if(!$resultbl){
        die("Query Failed!3" . mysqli_error($connect));      
        } 

        $reseller_data   = mysqli_fetch_object($resultbl);
        $get_selfbalance = $reseller_data->balance2;

        /*End Find User Balance */

        $query = "SELECT *from user where user='$user'";        
        $result = mysqli_query($connect, $query);

        if(!$result){
            die("Query Failed!" . mysqli_error($connect));      
        }
        
        
        $queryp = "SELECT * from user where pass=md5('$pass')";        
        $resultpass = mysqli_query($connect, $queryp);

        if(!$resultpass){
            die("Query Failed!" . mysqli_error($connect));      
        }
        
        /*
        $reseller_data= mysqli_fetch_object($result);

        $search_user    =$reseller_data->user;
        $search_phone   =$reseller_data->phone;
        $search_email   =$reseller_data->email;
        */
        
        $count     = mysqli_num_rows($result);
        $countpass = mysqli_num_rows($resultpass); 

        $is_valid_pass     = self::validPassword($pass);
        $is_valid_user     = self::validuser($user);
        $is_valid_fullname = self::validfullname($fullname);
        $is_valid_address  = self::validaddress($address);

        /*
        if(empty($user || $pass || $fullname || $address || $phone || $email || $pin_code)) 
            {                
                Session::Flush('error', 'All field are required!');
                Redirect::to('add_customer.php');
            } 
         */
        if($get_selfbalance < $account_fee)
        {
              Session::Flush('error', 'Insufficient Balance!');
              Redirect::to('add_customer.php');
        }   
        elseif ($is_valid_user != "")
        {
            Session::Flush('error', $is_valid_user);
            Redirect::to('add_customer.php');
        }     
        elseif ($is_valid_pass != "")
        {
            Session::Flush('error', $is_valid_pass);
            Redirect::to('add_customer.php');
        }
        elseif ($is_valid_fullname != "")
        {
            Session::Flush('error', $is_valid_fullname);
            Redirect::to('add_customer.php');
        }
        elseif ($is_valid_address != "")
        {
            Session::Flush('error', $is_valid_address);
            Redirect::to('add_customer.php');
        }
        elseif ($pin_code == 0)
        {
            Session::Flush('error', 'Only number value is allowed for Pin Code.');
            Redirect::to('add_customer.php');
        }
        elseif (strlen($pin_code) != 6)
        {
            Session::Flush('error', 'PIN must be in 6 digits');
            Redirect::to('add_customer.php');
        }
        elseif ($pin_code == "123456" or $pin_code == "654321")
        {
            Session::Flush('error', 'You given PIN is not allowed. Try stronger PIN code.');
            Redirect::to('add_customer.php');
        }
        elseif ($count >= 1)
        {
            Session::Flush('error', 'Username already taken. Please try another one.');
            Redirect::to('add_customer.php');
        }
        elseif ($countpass >= 1)
        {
            Session::Flush('error', 'Password are easy. Please try to input hard password.');
            Redirect::to('add_customer.php');
        }
        elseif (empty($phone)) 
        {               
                Session::Flush('error', 'You must enter your customer phone number');
                Redirect::to('add_customer.php');
        }
        elseif(!is_numeric($phone)) 
        {                
                Session::Flush('error', 'Phone Number allow only number!');
                Redirect::to('add_customer.php');
        } 
        elseif(!is_numeric($pin_code)) 
        {                
                Session::Flush('error', 'Pin Code allow only number!');
                Redirect::to('add_customer.php');
        }
        elseif (empty($pin_code)) 
        {               
                Session::Flush('error', 'You must enter your customer phone number');
                Redirect::to('add_customer.php');
        }
        elseif (empty($email)) 
        {               
                Session::Flush('error', 'You must enter your customer phone number');
                Redirect::to('add_customer.php');
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {            
            Session::Flush('error', 'Invalid email format');
            Redirect::to('add_customer.php');
        }
        elseif($cs_level >= $userlevel)
        {
            Session::Flush('error', 'New can not create this account. Contact your admin.');
            Redirect::to('add_customer.php');
        }
        else
        {           
		               
            $npassword = md5($pass);
            $npincode = md5($pin_code);
            
            if($account_fee > 0)
            {
                        
                /*Update Self Balance */                        
                                    
                $update_selfbalance = $get_selfbalance - $account_fee;

                $queryself = "UPDATE user SET balance2='$update_selfbalance' WHERE id='$us_id'";
                
                $resultself = mysqli_query($connect, $queryself);    
                if(!$resultself)
                {                      
                die("Query Failed4!" . mysqli_error($connect));
                }
                
                $note = "Account Create Fee (".$user .")";
                $queryp  = "INSERT INTO  reseller_balance (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
                $queryp .= "VALUES(0,'$us_id','$account_fee',NOW(),'Server',NOW(),'$note','$ip','1','$get_selfbalance','$update_selfbalance')"; 
                            
                $resultin = mysqli_query($connect, $queryp);    
                if(!$resultin)
                {                                          
                die("Query Failed2!" . mysqli_error($connect));
                }

                /*Update Self Balance End*/	

                if ($userlevel == 1) 
                {
                                
                        $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                        $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','$reseller_active','$npincode','0','0','0','$cs_level','0','0','$us_id','$resel5_id','$resel3_id','$us_id','0',4,'$limit_balance','$api_allow','$api_key')"; //Used concatenation (.) add two string
                
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
                        
                            Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                            Redirect::to('customer_list.php');
                    
                    
                }
                elseif ($userlevel == 2)
                {
                        //$query ="INSERT into user set user='$user',active='$reseller_active',pass=md5('$pass'),fullname='$fullname',pin_code=md5('$pin_code'),address='$address',city='$city',last_ip='0',phone='$phone',email='$email',level='2',country='$country',created_by='$us_id',pin='$boss_id',level3=0,level2=0,bkash_balance=0,balance=0,balance2=0,inserted_by=0,ip_block_check='0',devices_permitted='$reseller_devices_permitted'";
            
                        $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                        $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','$reseller_active','$npincode','0','0','0','$cs_level','0','0','$us_id','$resel4_id','$us_id','0','0',4,'$limit_balance','$api_allow','$api_key')"; //Used concatenation (.) add two string
                
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
                        
                            Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                            Redirect::to('customer_list.php'); 
                }
                elseif ($userlevel == 3)
                {
                        //$query ="INSERT into user set user='$user',active='$reseller_active',pass=md5('$pass'),fullname='$fullname',pin_code=md5('$pin_code'),address='$address',city='$city',last_ip='0',phone='$phone',email='$email',level='2',country='$country',created_by='$us_id',pin='$boss_id',level3=0,level2=0,bkash_balance=0,balance=0,balance2=0,inserted_by=0,ip_block_check='0',devices_permitted='$reseller_devices_permitted'";
            
                        $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                        $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','$reseller_active','$npincode','0','0','0','$cs_level','0','0','$us_id','$boss_id','0','0','0',4,'$limit_balance','$api_allow','$api_key')"; //Used concatenation (.) add two string
                
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
                        
                            Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                            Redirect::to('customer_list.php'); 
                } 
                elseif ($userlevel == 4)
                {
                        //$query ="INSERT into user set user='$user',active='$reseller_active',pass=md5('$pass'),fullname='$fullname',pin_code=md5('$pin_code'),address='$address',city='$city',last_ip='0',phone='$phone',email='$email',level='2',country='$country',created_by='$us_id',pin='$boss_id',level3=0,level2=0,bkash_balance=0,balance=0,balance2=0,inserted_by=0,ip_block_check='0',devices_permitted='$reseller_devices_permitted'";
            
                        $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                        $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','$reseller_active','$npincode','0','0','0','$cs_level','0','0','$us_id','$us_id','0','0','0',4,'$limit_balance','$api_allow','$api_key')"; //Used concatenation (.) add two string
                
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {
                            
                            die("Query Failed!" . mysqli_error($connect));
                        }
                        
                            Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                            Redirect::to('customer_list.php');
                
                }                     
                else
                {
                    echo "Invalid!";
                } 


            }
            else
            {
                 
                    if ($userlevel == 1) 
                    {
                                    
                            $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                            $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','$reseller_active','$npincode','0','0','0','$cs_level','0','0','$us_id','$resel5_id','$resel3_id','$us_id','0',4,'$limit_balance','$api_allow','$api_key')"; //Used concatenation (.) add two string
                    
                            $result = mysqli_query($connect, $query);    
                            if(!$result)
                            {
                                
                                die("Query Failed!" . mysqli_error($connect));
                            }
                            
                                Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                                Redirect::to('customer_list.php');
                        
                        
                    }
                    elseif ($userlevel == 2)
                    {
                            //$query ="INSERT into user set user='$user',active='$reseller_active',pass=md5('$pass'),fullname='$fullname',pin_code=md5('$pin_code'),address='$address',city='$city',last_ip='0',phone='$phone',email='$email',level='2',country='$country',created_by='$us_id',pin='$boss_id',level3=0,level2=0,bkash_balance=0,balance=0,balance2=0,inserted_by=0,ip_block_check='0',devices_permitted='$reseller_devices_permitted'";
                
                            $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                            $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','$reseller_active','$npincode','0','0','0','$cs_level','0','0','$us_id','$resel4_id','$us_id','0','0',4,'$limit_balance','$api_allow','$api_key')"; //Used concatenation (.) add two string
                    
                            $result = mysqli_query($connect, $query);    
                            if(!$result)
                            {
                                
                                die("Query Failed!" . mysqli_error($connect));
                            }
                            
                                Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                                Redirect::to('customer_list.php'); 
                    }
                    elseif ($userlevel == 3)
                    {
                            //$query ="INSERT into user set user='$user',active='$reseller_active',pass=md5('$pass'),fullname='$fullname',pin_code=md5('$pin_code'),address='$address',city='$city',last_ip='0',phone='$phone',email='$email',level='2',country='$country',created_by='$us_id',pin='$boss_id',level3=0,level2=0,bkash_balance=0,balance=0,balance2=0,inserted_by=0,ip_block_check='0',devices_permitted='$reseller_devices_permitted'";
                
                            $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                            $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','$reseller_active','$npincode','0','0','0','$cs_level','0','0','$us_id','$boss_id','0','0','0',4,'$limit_balance','$api_allow','$api_key')"; //Used concatenation (.) add two string
                    
                            $result = mysqli_query($connect, $query);    
                            if(!$result)
                            {
                                
                                die("Query Failed!" . mysqli_error($connect));
                            }
                            
                                Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                                Redirect::to('customer_list.php'); 
                    } 
                    elseif ($userlevel == 4)
                    {
                            //$query ="INSERT into user set user='$user',active='$reseller_active',pass=md5('$pass'),fullname='$fullname',pin_code=md5('$pin_code'),address='$address',city='$city',last_ip='0',phone='$phone',email='$email',level='2',country='$country',created_by='$us_id',pin='$boss_id',level3=0,level2=0,bkash_balance=0,balance=0,balance2=0,inserted_by=0,ip_block_check='0',devices_permitted='$reseller_devices_permitted'";
                
                            $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                            $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','$reseller_active','$npincode','0','0','0','$cs_level','0','0','$us_id','$us_id','0','0','0',4,'$limit_balance','$api_allow','$api_key')"; //Used concatenation (.) add two string
                    
                            $result = mysqli_query($connect, $query);    
                            if(!$result)
                            {
                                
                                die("Query Failed!" . mysqli_error($connect));
                            }
                            
                                Session::Flush('success', 'New Customer Successfully Added. Thank You!');
                                Redirect::to('customer_list.php');
                    
                    }                     
                    else
                    {
                        echo "Invalid!";
                    } 

            }
            
                
        }
    }


    /** End Add Reseller*/

    /** Start Customer Registration*/

    public static function csregistration()
    {
        $fullname = Input::get('full_name');
        $address = Input::get('address');;
        $city = "";
        $zip = "";
        $phone = Input::get('mobile');
        $email = Input::get('email');
        $user = Input::get('username');
        $pass = Input::get('password');
        $pin_code = Input::get('pin');
        //$cs_level = Input::get('level');
        $country = "";
        //$reseller_active = Input::get('active');		
		//$reseller_ip_block_check = Input::get('ip_block_check');
		//$reseller_devices_permitted = Input::get('devices_permitted');
        //$api_allow = Input::get('api_allow');
        $apikey = self::randomPassword();
        $api_key =  md5($apikey);	
		
		
        //$userlevel = Session::get('level');
        //$us_id = Session::get('us_id');	
		
        
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

        $query = "SELECT * FROM user where user='$user'";        
        $result = mysqli_query($connect, $query);

        if(!$result)
        {
            die("Query Failed!" . mysqli_error($connect));      
        }
        
        
        $queryp = "SELECT * from user where pass=md5('$pass')";        
        $resultpass = mysqli_query($connect, $queryp);

        if(!$resultpass)
        {
            die("Query Failed!" . mysqli_error($connect));      
        }
        
        /*
        $reseller_data= mysqli_fetch_object($result);

        $search_user    =$reseller_data->user;
        $search_phone   =$reseller_data->phone;
        $search_email   =$reseller_data->email;
        */

        $queryadmin = "SELECT * FROM user where level='5' ORDER BY id DESC LIMIT 1";        
        $resultadmin = mysqli_query($connect, $queryadmin);

        if(!$resultadmin)
        {
            die("Query Failed!" . mysqli_error($connect));      
        }        
        
        $get_data = mysqli_fetch_object($resultadmin);
        $admin_id = $get_data->id;
               
        
        $count     = mysqli_num_rows($result);
        $countpass = mysqli_num_rows($resultpass); 

        $is_valid_pass     = self::validPassword($pass);
        $is_valid_user     = self::validuser($user);
        $is_valid_fullname = self::validfullname($fullname);
        $is_valid_address  = self::validaddress($address);

        /*
        if(empty($user || $pass || $fullname || $address || $phone || $email || $pin_code)) 
            {                
                Session::Flush('error', 'All field are required!');
                Redirect::to('add_customer.php');
            } 
         */   
        if ($is_valid_user != "")
        {
            Session::Flush('error', $is_valid_user);
            Redirect::to('registration.php');
        }     
        elseif ($is_valid_pass != "")
        {
            Session::Flush('error', $is_valid_pass);
            Redirect::to('registration.php');
        }
        elseif ($is_valid_fullname != "")
        {
            Session::Flush('error', $is_valid_fullname);
            Redirect::to('registration.php');
        }
        elseif ($is_valid_address != "")
        {
            Session::Flush('error', $is_valid_address);
            Redirect::to('registration.php');
        }
        elseif ($pin_code == 0)
        {
            Session::Flush('error', 'Only number value is allowed for Pin Code.');
            Redirect::to('registration.php');
        }
        elseif (strlen($pin_code) != 6)
        {
            Session::Flush('error', 'PIN must be in 6 digits');
            Redirect::to('registration.php');
        }
        elseif ($pin_code == "123456" or $pin_code == "654321")
        {
            Session::Flush('error', 'You given PIN is not allowed. Try stronger PIN code.');
            Redirect::to('registration.php');
        }
        elseif ($count >= 1)
        {
            Session::Flush('error', 'Username already taken. Please try another one.');
            Redirect::to('registration.php');
        }
        elseif ($countpass >= 1)
        {
            Session::Flush('error', 'Password are easy. Please try to input hard password.');
            Redirect::to('registration.php');
        }
        elseif (empty($phone)) 
        {               
                Session::Flush('error', 'You must enter your customer phone number');
                Redirect::to('registration.php');
        }
        elseif(!is_numeric($phone)) 
        {                
                Session::Flush('error', 'Phone Number allow only number!');
                Redirect::to('registration.php');
        } 
        elseif(!is_numeric($pin_code)) 
        {                
                Session::Flush('error', 'Pin Code allow only number!');
                Redirect::to('registration.php');
        }
        elseif (empty($pin_code)) 
        {               
                Session::Flush('error', 'You must enter your customer phone number');
                Redirect::to('registration.php');
        }
        elseif (empty($email)) 
        {               
                Session::Flush('error', 'You must enter your customer phone number');
                Redirect::to('registration.php');
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {            
            Session::Flush('error', 'Invalid email format');
            Redirect::to('registration.php');
        }       
        else
        {
				   
			$npassword = md5($pass);
            $npincode = md5($pin_code);

                        
                    $query  = "INSERT INTO user (id,country,fullname,user,pass,email,phone,sign_date,regi_date,address,last_ip,active,pin_code,bkash_balance,balance,balance2,level,inserted_by,city,created_by,pin,level3,level2,ip_block_check,devices_permitted,limit_balance,api_status,api_key)";
                    $query .= "VALUES(0,'0','$fullname','$user','$npassword','$email','$phone',CURDATE(),CURDATE(),'$address','0','3','$npincode','0','0','0','0','0','0','$admin_id','0','0','0','0',4,'200','0','$api_key')"; //Used concatenation (.) add two string
            
                    $result = mysqli_query($connect, $query);    
                    if(!$result)
                    {
                        
                        die("Query Failed!" . mysqli_error($connect));
                    }                    
                        //Session::Flush('success', 'Your account Successfully Registered. Thank You!');
                        Redirect::to('registration_success.php');
            
                
        }
    }


    /** End Customer Registration*/


    /** get all bottomTree */

    public static function labelFour()
    {
        $labelfour = [];
        $labelTree = [];
        $labelTwo = [];
        $labelOne = [];
        $userId = Session::get('userId');
        if (self::type($userId) == 'reseller5')
        {
            $sql = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller4'";
            $resfour = DB::getInstance()->get($sql)->result();
            foreach ($resfour as $key4 => $value4)
            {
                $labelfour[$value4
                    ->id] = $value4->username;
                $fourId = $value4->id;

                $sql3 = "SELECT * FROM reseller WHERE p_id = '$fourId'  AND custype = 'reseller3'";
                $resTree = DB::getInstance()->get($sql3)->result();
                foreach ($resTree as $key3 => $value3)
                {
                    $labelTree[$value3
                        ->id] = $value3->username;
                    $threeId = $value3->id;
                    $sql2 = "SELECT * FROM reseller WHERE p_id = '$threeId'  AND custype = 'reseller2'";
                    $resTwo = DB::getInstance()->get($sql2)->result();
                    foreach ($resTwo as $value2)
                    {
                        $labelTwo[$value2
                            ->id] = $value2->username;
                        $twoId = $value2->id;
                        $sql1 = "SELECT * FROM reseller WHERE p_id = '$twoId' AND custype = 'reseller1'";
                        $resOne = DB::getInstance()->get($sql1)->result();
                        foreach ($resOne as $value1)
                        {
                            $labelOne[$value1
                                ->id] = $value1->username;
                        }
                    }
                }
            }
        }
        elseif (self::type($userId) == 'reseller4')
        {
            $sql = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller3'";
            $resfour = DB::getInstance()->get($sql)->result();
            foreach ($resfour as $key4 => $value4)
            {
                $labelTree[$value4
                    ->id] = $value4->username;
                $fourId = $value4->id;

                $sql3 = "SELECT * FROM reseller WHERE p_id = '$fourId'  AND custype = 'reseller2'";
                $resTree = DB::getInstance()->get($sql3)->result();
                foreach ($resTree as $key3 => $value3)
                {
                    $labelTwo[$value3
                        ->id] = $value3->username;
                    $threeId = $value3->id;

                    $sql2 = "SELECT * FROM reseller WHERE p_id = '$threeId'  AND custype = 'reseller1'";
                    $resTwo = DB::getInstance()->get($sql2)->result();
                    foreach ($resTwo as $value2)
                    {
                        $labelOne[$value2
                            ->id] = $value2->username;
                    }
                }
            }
        }
        elseif (self::type($userId) == 'reseller3')
        {
            $sql = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller2'";
            $resfour = DB::getInstance()->get($sql)->result();
            foreach ($resfour as $key4 => $value4)
            {
                $labelTwo[$value4
                    ->id] = $value4->username;
                $fourId = $value4->id;

                $sql3 = "SELECT * FROM reseller WHERE p_id = '$fourId'  AND custype = 'reseller1'";
                $resTree = DB::getInstance()->get($sql3)->result();
                foreach ($resTree as $key3 => $value3)
                {
                    $labelOne[$value3
                        ->id] = $value3->username;
                }
            }
        }
        elseif (self::type($userId) == 'reseller2')
        {
            $sql = "SELECT * FROM reseller WHERE p_id = '$userId'  AND custype = 'reseller1'";
            $resfour = DB::getInstance()->get($sql)->result();
            foreach ($resfour as $key4 => $value4)
            {
                $labelOne[$value4
                    ->id] = $value4->username;
            }
        }
        return array(
            'four' => $labelfour,
            'tree' => $labelTree,
            'two' => $labelTwo,
            'one' => $labelOne
        );
    }
}