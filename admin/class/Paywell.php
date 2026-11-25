<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
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
            // Paywell is now configured (encrypted, Sandbox/Live, PIN-protected) in
            // Gateway Settings. Writing the legacy table too would create two sources of truth.
            Session::Flush('error', 'Paywell credentials are now managed in Gateway Settings (Sandbox/Live, Self-PIN protected). Nothing was saved here.');
            Redirect::to('gateway_settings.php?tab=paywell');
            exit();
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


    /*
     * ---- Adapters over the new Paywell integration (lib/Midhub/Recharge/PaywellService.php) ----
     * Same method names the admin dashboard and menu already call. Configuration now comes
     * from payment_gateways (Gateway Settings); the API/encryption keys that used to be
     * hardcoded in this file have been removed.
     */

    /** 1 when the Paywell gateway is active in Gateway Settings, else 0. */
    public static function apistatus($apiid, $apicompany, $apiservice)
    {
        if ($apicompany !== "Paywell") {
            $query = "SELECT id FROM api_paywell WHERE id= . (int)  .  AND company= . mysqli_real_escape_string([connect], (string) ) .  AND service= . mysqli_real_escape_string([connect], (string) ) .  AND api_status=1 LIMIT 1";
            $result = mysqli_query($GLOBALS["connect"], $query);
            return $result ? mysqli_num_rows($result) : 0;
        }
        try {
            return \Midhub\Gateway\GatewayConfig::load("paywell")->isActive() ? 1 : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /** Paywell retailer balance for the dashboard card, or "N/A". */
    public static function recharge_balance()
    {
        try {
            $svc = \Midhub\Recharge\PaywellService::active();
            if (!$svc) {
                return "N/A";
            }
            $bal = $svc->balance();
            return $bal === null ? "N/A" : $bal;
        } catch (\Throwable $e) {
            error_log("Paywell balance: " . $e->getMessage());
            return "N/A";
        }
    }

    /** Number of active providers (Gateway Settings + legacy API table), for the menu badge. */
    public static function apiallstatus()
    {
        $n = 0;
        try {
            $n += (int) \Midhub\Db::scalar("SELECT COUNT(*) FROM payment_gateways WHERE status = \x27active\x27");
        } catch (\Throwable $e) {
        }
        $result = mysqli_query($GLOBALS["connect"], "SELECT id FROM api_paywell WHERE api_status=\x271\x27 AND company <> \x27Paywell\x27 LIMIT 1");
        if ($result) {
            $n += mysqli_num_rows($result);
        }
        return $n;
    }
}
