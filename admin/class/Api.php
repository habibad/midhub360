<?php
class Api
{

    
    /*API History Status Check Function Start */

    //function apihistorystatus($reqnumber,$reqamount)
    public static function apihistorystatus($reqid)
    {

        /* Query for API Link Information Start*/

            $query= "SELECT * from api where id='1' and company='eFlexiload' and service='Recharge' ORDER BY id DESC LIMIT 1";

            global $connect;
            
            $result = mysqli_query($connect, $query); 

            if(!$result)
            {                        
                die("Query Failed!" . mysqli_error($connect));
            }


            $reseller_data= mysqli_fetch_object($result);

            $apilink      = $reseller_data->status_url;
            $username     = $reseller_data->username;
            $apikey       = $reseller_data->ap_key;
            $pera_user    = $reseller_data->pera_user;
            $pera_key     = $reseller_data->pera_key;
            $pera_number  = $reseller_data->pera_number;
            $pera_amount  = $reseller_data->pera_amount;
            $pera_type    = $reseller_data->pera_type;
            $custom1      = $reseller_data->pera_customone;
            $custom2      = $reseller_data->pera_customtwo;

            /*Query for API Link Information End*/

            /*Request data Information Start*/

            //$req_number = "$reqnumber";
            //$req_amount = "$reqamount";

             $req_id = "$reqid";           

            /*Request data Information End*/


            /*API Request Start*/

            //$url = $apilink.'?'. $pera_number .'=' . $req_number .'&'. $pera_amount .'=' . $req_amount . '&' . $pera_user . '=' . $username . '&' . $pera_key. '=' . $apikey;
            
             $url = $apilink.'?'. $custom1 .'=' . $req_id . '&' . $pera_user . '=' . $username . '&' . $pera_key. '=' . $apikey;
           
            
             /*          

            $client = curl_init($url);
            curl_setopt($client, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($client, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
            curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($client, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($client, CURLOPT_SSLVERSION, 1);
            curl_setopt($client, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

            $response = curl_exec($client); 
            $result = json_decode($response); 

            curl_close($client);

            //echo "<br><br>";

            
            $request_status = $result->STATUS;

            */
                       
            /*API Request End*/



    return $url;

    }


    /*API History Status Check Function End */



    /*API Recharge Balance Check Function Start */

    public static function apibalance_recharge()
    {

        /* Query for API Link Information Start*/

            $query= "SELECT * from api where id='1' and company='eFlexiload' and service='Recharge' ORDER BY id DESC LIMIT 1";

            global $connect;
            
            $result = mysqli_query($connect, $query); 

            if(!$result)
            {                        
                die("Query Failed!" . mysqli_error($connect));
            }


            $reseller_data= mysqli_fetch_object($result);

            $apilink      = $reseller_data->balance_url;
            $username     = $reseller_data->username;
            $apikey       = $reseller_data->ap_key;
            $pera_user    = $reseller_data->pera_user;
            $pera_key     = $reseller_data->pera_key;
           
            
            $url = $apilink.'?'. $pera_user . '=' . $username . '&' . $pera_key. '=' . $apikey;
          

            $client = curl_init($url);
            curl_setopt($client, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($client, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
            curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($client, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($client, CURLOPT_SSLVERSION, 1);
            curl_setopt($client, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

            $response = curl_exec($client); 
            $result = json_decode($response); 

            curl_close($client);

            $request_status = $result->BALANCE;

           
    return $request_status;

    }


    /*API Recharge balance Check Function End */



/*API Recharge Balance Check Function Start */

public static function apibalance_mb()
{

    /* Query for API Link Information Start*/

        $query= "SELECT * from api where id='1' and company='eFlexiload' and service='Recharge' ORDER BY id DESC LIMIT 1";

        global $connect;
        
        $result = mysqli_query($connect, $query); 

        if(!$result)
        {                        
            die("Query Failed!" . mysqli_error($connect));
        }


        $reseller_data= mysqli_fetch_object($result);

        $apilink      = $reseller_data->balance_url;
        $username     = $reseller_data->username;
        $apikey       = $reseller_data->ap_key;
        $pera_user    = $reseller_data->pera_user;
        $pera_key     = $reseller_data->pera_key;
       
        
        $url = $apilink.'?'. $pera_user . '=' . $username . '&' . $pera_key. '=' . $apikey;
      

        $client = curl_init($url);
        curl_setopt($client, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($client, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
        curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($client, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($client, CURLOPT_SSLVERSION, 1);
        curl_setopt($client, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

        $response = curl_exec($client); 
        $result = json_decode($response); 

        curl_close($client);

        $request_status = $result->MBALANCE;

       
return $request_status;

}


/*API Mobile Banking balance Check Function End */



    /*API Status Function Start */

    public static function apistatus($apiid,$apicompany,$apiservice)
    {

        $query= "SELECT * from api where id='$apiid' and company='$apicompany' and service='$apiservice' and api_status='1' ORDER BY id DESC LIMIT 1";

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


    public static function apiallstatus()
    {

        $query= "SELECT * from api where api_status='1' ORDER BY id DESC LIMIT 1";

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