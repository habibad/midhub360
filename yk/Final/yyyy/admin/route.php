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
	case 'returnpayment':
		Payment::ReturnBalance();
		break;	
	case 'addNewReseller':
		Reseller::createNewReseller();
		break;
	case 'addFund':
		Payment::addFund();
		break;
	case 'resuccess':
		History::rechargesuccess();
		break;
	case 'recancel':
		History::rechargecancel();
		break;
	case 'banksuccess':
		History::bankingsuccess();
		break;
	case 'bankcancel':
		History::bankingcancel();
		break;
	case 'resendrecharge':
		History::resendrecharge();
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
	case 'SimUpdate':
		Update::EditSim();
		break;	
	case 'DriveAdd':
		Newdata::AddDrive();
		break;	
	case 'DriveEdit':
		Update::EditDrive();
		break;
	case 'PackageAdd':
		Newdata::AddPackage();
		break;
	case 'PackageEdit':
		Update::EditPkg();
		break;	
	case 'UpdateMySettings':
		Update::UpdateMySettings();
		break;
	case 'UpdateMyProfile':
		Update::UpdateMyProfile();
		break;
	case 'UpdateSettings':
		Update::UpdateSettings();
		break;
	case 'UpdateServerStatus':
		Update::UpdateServerStatus();
		break;
	case 'OperatorOnOff':
		Update::OperatorOnOff();
		break;
	case 'UpdateBrand':
		Upload::UpdateBrand();
		break;
	case 'UpdateCompanyInfo':
		Update::UpdateCompanyInfo();
		break;
	case 'APIUpdate':
		Update::UpdateAPI();
		break;
	case 'PaywellAPIUpdate':
		Paywell::PaywellUpdateAPI();
		break;	
	case 'APIAdd':
		Update::AddAPI();
		break;
	case 'DataDelete':
		Delete::DataDelete();
		break;	
	case 'UpPaymentInfo':
		Update::UpdatePaymentInfo();
		break;		
	case 'ipblock':
		Mixedfn::blockip();
		break;		
	case 'CountryAllow':
		Security::AllowCountry();
		break;		
	case 'BlockUser':
		Security::blockuser();
		break;		
	case 'addnotice':
		Notice::addnotice();
		break;		
	case 'commissionup':
		Update::CommissionUp();
		break;		
	case 'acfeeup':
		Update::acfeeup();
		break;
	case 'billpaycharge':
		Update::billpaycharge();
		break;
	case 'levelchange':
		Update::levelchange();
		break;		
	case 'ForgotPassword':
		Account::ForgotPassword();
		break;		
	default:
		//print 'page not found';
		break;
}
