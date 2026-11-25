<?php
/**
 * Bill fetch / bill payment AJAX endpoint used by bill_electricity.php, bill_water.php,
 * bill_gas.php, bill_tv.php and bill_internet.php.
 *
 * Rewritten (the legacy version had no login check, was SQL-injectable, had no balance or PIN
 * check, charged repeatedly, and marked bills paid without paying the biller). All rules live
 * in lib/Midhub/BillPay/BillPayment.php.
 *
 * Responses are JSON text. The pages call JSON.parse() themselves, so the default text/html
 * content type is kept on purpose (jQuery would otherwise pre-parse it).
 */
include "login.check.php";
require_once 'autoload.php';
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\BillPay\BillPayment;

\Midhub\Security\Csrf::assertSameOrigin();

$action = isset($_POST['service_type']) ? (string) $_POST['service_type'] : '';
$in = array();
foreach ($_POST as $k => $v) {
    if (is_string($v)) {
        $in[(string) $k] = $v;
    }
}

try {
    switch ($action) {
        case 'bill_fetch_request':
            $out = BillPayment::fetch((int) $us_id, $in);
            break;
        case 'bill_payment_request':
            $out = BillPayment::pay((int) $us_id, isset($in['bill_payment_id']) ? $in['bill_payment_id'] : '');
            break;
        default:
            $out = array('result' => 'error', 'message' => 'Invalid request.');
    }
} catch (\Throwable $e) {
    error_log('process_bill_payment: ' . $e->getMessage());
    $out = array('result' => 'error', 'message' => 'Something went wrong. If your balance was deducted it will be completed or refunded automatically.');
}
echo json_encode($out);
