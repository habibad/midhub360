<?php
class Log
{
    public static function loginlogs($type)
    {
        global $connect;
        $ip          = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $username    = mysqli_real_escape_string($connect, Input::get('username') ?? '');
        $password    = mysqli_real_escape_string($connect, Input::get('password') ?? '');
        $status_type = $type;
        $time        = time();

        /* Start for Get Browser and OS */
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $bname     = 'Unknown';
        $platform  = 'Unknown';
        $ub        = 'other';
        $version   = "";

        // Platform detection
        if (preg_match('/linux/i', $userAgent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'windows';
        }

        // Browser detection
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

        // Version number
        $known   = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', array_filter($known)) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (preg_match_all($pattern, $userAgent, $matches) && !empty($matches['browser'])) {
            $i = count($matches['browser']);
            if ($i != 1) {
                if ($ub !== 'other' && strripos($userAgent, "Version") !== false && strripos($userAgent, $ub) !== false) {
                    if (strripos($userAgent, "Version") < strripos($userAgent, $ub)) {
                        $version = $matches['version'][0] ?? '';
                    } else {
                        $version = $matches['version'][1] ?? '';
                    }
                } else {
                    $version = $matches['version'][0] ?? '';
                }
            } else {
                $version = $matches['version'][0] ?? '';
            }
        }

        if (empty($version)) {
            $version = "?";
        }

        $browser_name = mysqli_real_escape_string($connect, $bname . ' , ' . $version);
        $platform     = mysqli_real_escape_string($connect, $platform);
        $ip           = mysqli_real_escape_string($connect, $ip);

        if ($status_type == "Failed") {
            $queryin = "INSERT INTO login_logs (id,username,password,ip,browser,os,submitted_time,status,date,TryTime)
                        VALUES(0,'$username','$password','$ip','$browser_name','$platform',NOW(),'0',CURDATE(),'$time')";
            @mysqli_query($connect, $queryin);
        } elseif ($status_type == "Success") {
            $queryin = "INSERT INTO login_logs (id,username,password,ip,browser,os,submitted_time,status,date,TryTime)
                        VALUES(0,'$username','n/a','$ip','$browser_name','$platform',NOW(),'1',CURDATE(),'$time')";
            @mysqli_query($connect, $queryin);
        } else {
            $queryin = "INSERT INTO login_logs (id,username,password,ip,browser,os,submitted_time,status,date,TryTime)
                        VALUES(0,'0','0','$ip','$browser_name','$platform',NOW(),'0',CURDATE(),'$time')";
            @mysqli_query($connect, $queryin);
        }
    }
}
?>
