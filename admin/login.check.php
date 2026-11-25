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

/*
 * SECURITY: the admin panel is for the Super Admin (level 5) only.
 * The shared root login.php signs in users of every level with the same session
 * keys, so checking that a session merely exists let any reseller open /admin/.
 * Re-verify level and active status against the database on every request so
 * demotion or deactivation takes effect immediately.
 */
require_once dirname(__DIR__) . '/db.php';
$__adm_ok = false;
$__adm_stmt = mysqli_prepare($connect, "SELECT level, active FROM user WHERE id = ? AND user = ? LIMIT 1");
if ($__adm_stmt) {
    $__adm_id = (int) $us_id;
    $__adm_un = (string) $user_name;
    mysqli_stmt_bind_param($__adm_stmt, 'is', $__adm_id, $__adm_un);
    mysqli_stmt_execute($__adm_stmt);
    mysqli_stmt_bind_result($__adm_stmt, $__adm_level, $__adm_active);
    if (mysqli_stmt_fetch($__adm_stmt)) {
        $__adm_ok = ((int) $__adm_level === 5 && (string) $__adm_active === '1');
    }
    mysqli_stmt_close($__adm_stmt);
}
if (!$__adm_ok) {
    $_SESSION = array();
    session_destroy();
    header('location: ./index.php');
    exit();
}
$level = 5;
unset($__adm_ok, $__adm_stmt, $__adm_id, $__adm_un, $__adm_level, $__adm_active);

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
