<?php
class Security
{
        
        /*Start Function for total login failed*/

        public static function totalloginfailed()
        {
        // FAILED attempts (status 0) from this IP in the last 15 minutes. The legacy version
        // counted every attempt in a 60-second window, so slow brute force was never stopped.
        global $connect;
        $ip_address = mysqli_real_escape_string($connect, (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        $time = time() - 900;
        $query = "SELECT count(*) as total_count FROM login_logs WHERE TryTime > $time AND status = '0' AND ip = '$ip_address'";
        $result = mysqli_query($connect, $query); 
        if(!$result)
        {
            die("Query Failed!" . mysqli_error($connect));      
        }        
        $data = mysqli_fetch_object($result);
        return (int) ($data->total_count ?? 0);
        }

        /*Function for total login failed end*/


        /*Start Function for total login failed*/

        public static function totalloginfaileduser()
        {
        // FAILED attempts for this username in the last 15 minutes (escaped: this runs on the
        // public login page; the legacy query was SQL-injectable before authentication).
        global $connect;
        $time     = time() - 900;
        $username = mysqli_real_escape_string($connect, (string) Input::get('username'));
        $query = "SELECT count(*) as total_count FROM login_logs WHERE TryTime > $time AND status = '0' AND username = '$username'";
        $result = mysqli_query($connect, $query); 
        if(!$result)
        {
            die("Query Failed!" . mysqli_error($connect));      
        }        
        $data = mysqli_fetch_object($result);
        return (int) ($data->total_count ?? 0);
        }

        /*Function for total login failed end*/

        /*IP Block Method Start*/

        public static function IPBlock()
        {
            $block_ip = $_SERVER['REMOTE_ADDR'];

            $query  = "INSERT INTO ip_block (id,block_ip)";
            $query .= "VALUES(0,'$block_ip')"; 

            global $connect;                     
            $result = mysqli_query($connect, $query);    
            if(!$result)
            {
                        
            die("Query Failed!" . mysqli_error($connect));
            }

        }

        /*IP Block Method End*/


         /*IP Block Method Start*/

         public static function UserBlock()
         {
             $username    = Input::get('username');
 
             $query  = "INSERT INTO user_blocklist (id,username)";
             $query .= "VALUES(0,'$username')"; 
 
             global $connect;                     
             $result = mysqli_query($connect, $query);    
             if(!$result)
             {
                         
             die("Query Failed!" . mysqli_error($connect));
             }
 
         }
 
         /*IP Block Method End*/

         /*Check IP Block Method Start*/
         public static function CheckBlockIP()
         {
            $user_ip = $_SERVER['REMOTE_ADDR'];

            global $connect;
            $query  = "SELECT * FROM ip_block where block_ip='$user_ip'"; 
            $result = mysqli_query($connect, $query); 
            $count = mysqli_num_rows($result);

            return $count;
         }

         /*Check IP Block Method End*/



          /*Check Block User Method Start*/
          public static function CheckBlockUser($username)
          {
             
             global $connect;
             $query  = "SELECT * FROM user_blocklist where username='$username'"; 
             $result = mysqli_query($connect, $query); 
             $count = mysqli_num_rows($result);
 
             return $count;
          }
 
          /*Check IP Block Method End*/

         /*Check Country Start*/
         public static function CheckCountry()
         {
        
            $ip=$_SERVER['REMOTE_ADDR'];
            //Using the API to get information about this IP
            $details = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=$ip"));
            //Using the geoplugin to get the continent for this IP
            $continent=$details->geoplugin_continentCode;
            //And for the country
            $country=$details->geoplugin_countryCode;
            
            return $country;
         }

         /*Check Country End*/


 
 
}

?>
