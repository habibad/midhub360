<?php
class Paywell
{

    /* API Update Start */

    public static function PaywellUpdateAPI()
    {
        $username     = Input::get('username');
        $password     = Input::get('password');        
        $api_username = Input::get('apiusername');
        $api_password = Input::get('apipassword');
        $api_key      = Input::get('apikey');
        $encryption_key = Input::get('ekey'); 
        $baseurl      = Input::get('baseurl');
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

                                     
        if ($company_name == 'Paywell') 
        {            
            $data = "username='$username',password='$password',apiusername='$api_username',apipassword='$api_password',ap_key='$api_key',encryption_key='$encryption_key',base_url='$baseurl',request_url='$request_link',status_url='$status_link',balance_url='$balance_link',api_status='$api_status'";
              
        }          
        elseif ($company_name == 'Others')
        {            
          $data = "username='$api_username',ap_key='$api_key',request_url='$request_link',status_url='$status_link',balance_url='$balance_link',api_status='$api_status',pera_user='$pera_user',pera_key='$pera_key',pera_number='$pera_number',pera_amount='$pera_amount',pera_type='$pera_type',pera_customone='$pera_customone',pera_customtwo='$peracustomtwo'";
        }
        else
        {            
          $data = "country='$country_name',company='$company_name',service='$service_name',username='$api_username',ap_key='$api_key',request_url='$request_link',status_url='$status_link',balance_url='$balance_link',api_status='$api_status',pera_user='$pera_user',pera_key='$pera_key',pera_number='$pera_number',pera_amount='$pera_amount',pera_type='$pera_type',pera_customone='$pera_customone',pera_customtwo='$peracustomtwo'";
        } 

        $query = "UPDATE api_paywell SET $data WHERE id = $api_id";
        global $connect; 
        $result = mysqli_query($connect, $query);    
        if(!$result)
        {                      
            die("Query Failed!" . mysqli_error($connect));
        }

        if ($company_name == 'Paywell') 
        { 

        Session::Flush('success', 'Successfully API Updated!');
        Redirect::to('api_setup_paywell.php'); 
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


    /*API Status Function Start */

    public static function apistatus($apiid,$apicompany,$apiservice)
    {

        $query= "SELECT * from api_paywell where id='$apiid' and company='$apicompany' and service='$apiservice' and api_status='1' ORDER BY id DESC LIMIT 1";

        global $connect;
        
        $result = mysqli_query($connect, $query); 

        if(!$result)
        {                        
            die("Query Failed!" . mysqli_error($connect));
        }

        $count = mysqli_num_rows($result);

    return $count;  

    }


    /*API Status Function End */



    public static function paywell_token()
     {
		
        /* Query for API Link Information Start*/

        $query= "SELECT * from api_paywell where id='1' and company='Paywell' and service='Recharge' ORDER BY id DESC LIMIT 1";

        global $connect;
        
        $result = mysqli_query($connect, $query); 

        if(!$result)
        {                        
            die("Query Failed!" . mysqli_error($connect));
        }


        $reseller_data= mysqli_fetch_object($result);

        $baseurl      = $reseller_data->base_url;
        $APIUserName  = $reseller_data->apiusername;
        $APIPassword  = $reseller_data->apipassword;
        
       
        $AUTH_VALUE = base64_encode($APIUserName.":".$APIPassword);

        //echo $AUTH_VALUE . "<br/><br/>";
        $url = "$baseurl"."/Authentication/PaywellAuth/getToken";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"$url");
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$vars);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array("Content-Type:application/json","Authorization: Basic ".$AUTH_VALUE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec ($ch);
        $result = json_decode($response);

        curl_close ($ch);

        //print  $server_output ;
        $token = $result->token->security_token;
            
        //echo $response . "<br/><br/>";
        //echo $token . "<br/><br/>";
        
    return $token;	
}	 

    public static function recharge_balance(){       
            

            $query= "SELECT * from api_paywell where id='1' and company='Paywell' and service='Recharge' ORDER BY id DESC LIMIT 1";

            global $connect;
            
            $result = mysqli_query($connect, $query); 

            if(!$result)
            {                        
                die("Query Failed!" . mysqli_error($connect));
            }


            $reseller_data= mysqli_fetch_object($result);

            $baseurl   = $reseller_data->base_url;
            $username  = $reseller_data->username;
            $password  = $reseller_data->password;
      
        $request_parameter=array(
            "username"=>"$username",
            "password"=>"$password"            
          );

          /* Start of Bearer Token */

          $request_data = json_encode($request_parameter);      
          $api_key = "5d0bb446e5fd0c6f79cf84fb93c3ccff52fdac76360dcae2389517479e80b79f";
          $encryption_key = "54ef8f9a457bf6e28fcae33f19134abc375696947b16a734dc101803d38ac94c9e8b4dbb87aa8b681370649fc06a13f55e401d577a7474307ec284ab8e2ec176";
          $security_token = self::paywell_token();
          $hashed_data = hash_hmac('sha256',$request_data, $encryption_key);
          $bearer_token = base64_encode(($security_token.":".$api_key.":".$hashed_data));

          /* End of Bearer Token */

            $var = $request_parameter;
            $url = "$baseurl"."/Retailer/RetailerService/retailerBalance";	

            $headers = array("Content-Type:application/json","Authorization: Bearer ".$bearer_token);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    
            if (count($var)>0)
            {
              $json_string = json_encode($var);
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
              curl_setopt($curl, CURLOPT_POSTFIELDS, $json_string);
            }
                    
              $response = curl_exec($curl);
              $result = json_decode($response);
              curl_close($curl);
              //var_dump($resp);$var

              $status = $result->balanceData->balance;
              //echo $response;

        return $status;

    }


            public static function apiallstatus()
            {

                $query= "SELECT * from api_paywell where api_status='1' ORDER BY id DESC LIMIT 1";

                global $connect;
                
                $result = mysqli_query($connect, $query); 

                if(!$result)
                {                        
                    die("Query Failed!" . mysqli_error($connect));
                }

                $count = mysqli_num_rows($result);

            return $count;  

            }
    

}






?>