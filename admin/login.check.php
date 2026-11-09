<?php
//ini_set('session.use_trans_sid',"0");
//ini_set("session.gc_maxlifetime", "5414154000"); 

session_start();

// print_r($_SESSION);
// exit();

//store session data in variable

$user_name      = $_SESSION["user_name"];  
$us_id          = $_SESSION["us_id"];
$sess_uservalid = $_SESSION["sess_uservalid"];
$level          = $_SESSION["level"];
$userip         = $_SESSION["userip"];



if(!isset($_SESSION['user_name']))
{
	session_destroy();
	header("location:./index.php");
	die();
}


if(intval($us_id)==0)
{
 	session_destroy();  
  	header('location: ./index.php');
	die();
}


if(!isset($user_name) or $user_name=="")
{
  session_destroy();  
  header('location: ./index.php');
  exit;
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
