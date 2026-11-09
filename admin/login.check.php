<?php
require_once dirname(__DIR__) . '/config/env.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//store session data in variable
$user_name      = $_SESSION["user_name"] ?? null;  
$us_id          = $_SESSION["us_id"] ?? null;
$sess_uservalid = $_SESSION["sess_uservalid"] ?? null;
$level          = $_SESSION["level"] ?? null;
$userip         = $_SESSION["userip"] ?? null;

if (!isset($_SESSION['user_name']) || empty($_SESSION['user_name'])) {
	session_destroy();
	header("location:./index.php");
	exit();
}

if (intval($us_id) == 0) {
 	session_destroy();  
  	header('location: ./index.php');
	exit();
}

if (!isset($user_name) || $user_name == "") {
  session_destroy();  
  header('location: ./index.php');
  exit();
}

// print_r($_SESSION);
// exit();

/*

if($level!=3)
{
session_destroy();
header('location: ../hummm.php');
exit;
}


if($userip != $_SERVER["REMOTE_ADDR"])
{
	if(!isset($user_name) or $user_name=="")
	{
		session_destroy();
		$_SESSION=array();
		header('location: ../login.php');
		exit;
	}

	if(intval($us_id)==0)
	{
		session_destroy();
		$_SESSION=array();
		header('location: ../login.php');
		exit;
	}

	if($level!=3)
	{
		session_destroy();
		$_SESSION=array();
		header('location: ../apps_login.php');
		exit;
	}
}

*/

?>
