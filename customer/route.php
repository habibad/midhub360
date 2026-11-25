<?php
include "login.check.php";
include "../db.php";
require_once 'autoload.php';
require_once dirname(__DIR__, 1) . '/lib/bootstrap.php';

// CSRF defence for every legacy customer action: the request must originate from this site.
\Midhub\Security\Csrf::assertSameOrigin();

$action = Input::get('action');

// Every PIN-protected money action first passes the PIN lockout (5 wrong PINs in 15 min lock
// the account's PIN actions, and the IP temporarily); the legacy handler then re-checks the PIN.
$__pinActions = array('requestSend', 'requestSend_mnp', 'requestPackage', 'requestDrive', 'requestMBSend',
    'requestBillPaySend', 'BankSend', 'paymentTransfer', 'returnpayment');
if (in_array($action, $__pinActions, true)) {
    $__pv = \Midhub\Security\PinVerifier::verify((int) $us_id, trim((string) Input::get('pincode')));
    if (!$__pv['ok']) {
        $__back = basename((string) parse_url(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', PHP_URL_PATH));
        Session::Flush('error', $__pv['error']);
        Redirect::to(preg_match('/^[A-Za-z0-9_-]+\.php$/', $__back) ? $__back : 'home.php');
        exit();
    }
}

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
