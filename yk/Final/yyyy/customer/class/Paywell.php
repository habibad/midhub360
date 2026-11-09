<?php

class Paywell
{
     
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

    public static function paywell_recharge($number,$amount,$type,$ref_id){ 
      
            //$ref_id = mt_rand(100000,999999);            
            $operator = self::get_company($number);         

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
            "password"=>"$password",
            "ref_id"=>"$ref_id",
            "msisdn"=>"$number",
            "amount"=>$amount,
            "con_type"=>"$type",
            "operator"=>"$operator"
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
            $url = "$baseurl"."/Recharge/mobileRecharge/singleTopup";	

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
              //$result = json_decode($response);
              curl_close($curl);
              //var_dump($resp);$var

              //$status = $result->data->message;
              //echo $response;

        return $response;

    }

    public static function get_company($phone)
    {
        $three = $phone[0] . $phone[1] . $phone[2];
        if ($three == "017")
        {
            $abc = "GP";
        }
        if ($three == "016")
        {
            $abc = "AT";
        }
        if ($three == "018")
        {
            $abc = "RB";
        }
        if ($three == "019")
        {
            $abc = "BL";
        }
        if ($three == "013")
        {
            $abc = "GP";
        }
        if ($three == "015")
        {
            $abc = "TT";
        }
		    if ($three == "014")
        {
            $abc = "BL";
        }
        return $abc;
    }


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


}






?>