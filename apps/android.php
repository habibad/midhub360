<?php
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
if(!stripos($ua,'android') !== false)
{ // && stripos($ua,'mobile') !== false) {
    header("location: https://www.autoeload.com");
}


?>