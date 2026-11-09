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
	case 'ForgotPassword':
		Account::ForgotPassword();
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
		Payment::addFund();
		break;
	case 'addFundThroughOnline':
		Payment::addFundThroughOnline();
		break;
	case 'testService':
		TestService::testService();
		break;
	case 'resuccess':
		History::rechargesuccess();
		break;
	case 'recancel':
		History::rechargecancel();
		break;
	case 'drivesuccess':
		History::drivesuccess();
		break;
	case 'drivecancel':
		History::drivecancel();
		break;
	case 'mbanksuccess':
		History::mabnksuccess();
		break;
	case 'mbankcancel':
		History::mbankcancel();
		break;
	case 'billpaysuccess':
		History::billpaysuccess();
		break;
	case 'billpaycancel':
		History::billpaycancel();
		break;
	case 'balaaddsuccess':
		History::baladduccess();
		break;
	case 'baladdcancel':
		History::baladdcancel();
		break;
	case 'csupdate':
		Update::UpdateCustomer();
		break;
	case 'SimRegi':
		Newdata::SimRegistration();
		break;	
	case 'DriveAdd':
		Newdata::AddDrive();
		break;	
	case 'PackageAdd':
		Newdata::AddPackage();
		break;
	case 'UpdateMySettings':
		Update::UpdateMySettings();
		break;
	case 'UpdateMyProfile':
		Update::UpdateMyProfile();
		break;
	case 'UpdateAPIKey':
		Update::UpdateAPIKey();
		break;
	
	default:
		//print 'page not found';
		break;
}
