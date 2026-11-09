<?php
include "login.check.php";
include "../db.php";
require_once 'autoload.php';
$action = Input::get('action');

switch ($action) {	
	case 'requestSend':
		Request::send_flexiload();
		break;
	case 'requestSend_mnp':
		Request::send_flexiload_mnp();
		break;	
	case 'requestPackage':
		Request::send_package_request();
		break;
	case 'requestDrive':
		Request::send_drive_request();
		break;	
	case 'requestMBSend':
		Request::send_m_banking();
		break;
	case 'requestBillPaySend':
		Request::send_billpay_request();
		break;
	case 'BankSend':
		Request::send_bank_request();
		break;			
	case 'updateProfile':
		Account::updateProfile();
		break;
	case 'changePin':
		Account::changePin();
		break;
	case 'changePassword':
		Account::changePassword();
		break;
	case 'changePhone':
		Account::changePhone();
		break;
	case 'changeEmail':
		Account::changeEmail();
		break;	
	case 'paymentTransfer':
		Payment::transfer();
		break;
	case 'addNewReseller':
		Reseller::createNewReseller();
		break;
	case 'addFund':
		AddFundM::addFund();
		break;
	case 'autoaddFund':
		Addfund::autoaddbalance();
		break;	
	default:
		//print 'page not found';
		break;
}
