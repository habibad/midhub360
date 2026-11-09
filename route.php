<?php
//include "login.check.php";
session_start();
include "db.php";
require_once 'autoload.php';
$action = Input::get('action');

switch ($action) {
	case 'ForgotPassword':
		Account::ForgotPassword();
		break;
	case 'csregistration':
		Reseller::csregistration();
		break;
	default:
		//print 'page not found';
		break;
}
