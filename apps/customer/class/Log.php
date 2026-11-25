<?php
class Log
{
      
      public static function loginlogs($type)
      {
            $ip          = $_SERVER['REMOTE_ADDR'];
            global $connect;
            $username    = mysqli_real_escape_string($connect, (string) Input::get('username')); // was unescaped (SQL injection on login)
            $password    = '[not stored]'; // SECURITY: never log attempted passwords
            $status_type = $type;
            $time        = time();


            /*Start for Get Browser and OS*/

            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $bname     = 'Unknown';
            $platform  = 'Unknown';
            $version   = "";

            // First get the platform?
            if (preg_match('/linux/i', $userAgent)) {
                  $platform = 'linux';
            } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
                  $platform = 'mac';
            } elseif (preg_match('/windows|win32/i', $userAgent)) {
                  $platform = 'windows';
            }

            // Next get the name of the useragent yes seperately and for good reason
            if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
                  $bname = 'Internet Explorer';
                  $ub    = "MSIE";
            } elseif (preg_match('/Firefox/i', $userAgent)) {
                  $bname = 'Mozilla Firefox';
                  $ub    = "Firefox";
            } elseif (preg_match('/Chrome/i', $userAgent)) {
                  $bname = 'Google Chrome';
                  $ub    = "Chrome";
            } elseif (preg_match('/Safari/i', $userAgent)) {
                  $bname = 'Apple Safari';
                  $ub    = "Safari";
            } elseif (preg_match('/Opera/i', $userAgent)) {
                  $bname = 'Opera';
                  $ub    = "Opera";
            } elseif (preg_match('/Netscape/i', $userAgent)) {
                  $bname = 'Netscape';
                  $ub    = "Netscape";
            }

            // finally get the correct version number
            $known   = array('Version', $ub, 'other');
            $pattern = '#(?<browser>' . join('|', $known) .
                  ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
            if (!preg_match_all($pattern, $userAgent, $matches)) {
                  // we have no matching number just continue
            }

            // see how many we have
            $i = count($matches['browser']);
            if ($i != 1) {
                  // we will have two since we are not using 'other' argument yet
                  // see if version is before or after the name
                  if (strripos($userAgent, "Version") < strripos($userAgent, $ub)) {
                        $version = $matches['version'][0];
                  } else {
                        $version = $matches['version'][1];
                  }
            } else {
                  $version = $matches['version'][0];
            }

            // check if we have a number
            if ($version == null || $version == "") {$version = "?";}


            /* End for Get Browser and OS*/

            $browser_name = $bname . ' , ' . $version;

            if($status_type == "Failed")
            {
                  $queryin  = "INSERT INTO login_logs (id,username,password,ip,browser,os,submitted_time,status,date,TryTime)";
                  $queryin .= "VALUES(0,'$username','$password','$ip','$browser_name','$platform',NOW(),'0',CURDATE(),'$time')"; 
      
                  global $connect;                     
                  $resultin = mysqli_query($connect, $queryin);    
                  if(!$resultin)
                  {
                              
                  die("Query Failed2!" . mysqli_error($connect));
                  }
            }
            elseif($status_type == "Success")
            {

                  $queryin  = "INSERT INTO login_logs (id,username,password,ip,browser,os,submitted_time,status,date,TryTime)";
                  $queryin .= "VALUES(0,'$username','n/a','$ip','$browser_name','$platform',NOW(),'1',CURDATE(),'$time')"; 

                  global $connect;                     
                  $resultin = mysqli_query($connect, $queryin);    
                  if(!$resultin)
                  {
                              
                  die("Query Failed!" . mysqli_error($connect));
                  }

            }
            else
            {
                  $queryin  = "INSERT INTO login_logs (id,username,password,ip,browser,os,submitted_time,status,date,TryTime)";
                  $queryin .= "VALUES(0,'0','0','$ip','$browser_name','$platform',NOW(),'0',CURDATE(),'$time')"; 

                  global $connect;                     
                  $resultin = mysqli_query($connect, $queryin);    
                  if(!$resultin)
                  {
                              
                  die("Query Failed!" . mysqli_error($connect));
                  }
            }


      }
      
      
      

     
}
