<?php
/** Downloadable PDF receipt for a PAID utility bill (owner only). */
include "login.check.php";
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Db;
use Midhub\Pdf\SimplePdf;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$b = Db::one("SELECT b.*, u.fullname, u.user AS username FROM billpay_request b JOIN user u ON u.id = b.user_id WHERE b.id = ? AND b.user_id = ? AND b.status = '1'", 'ii', array($id, (int) $us_id));
if (!$b) {
    http_response_code(404);
    exit('Receipt not available. Receipts can be downloaded once the bill is paid.');
}
$company = Db::one('SELECT * FROM company WHERE id = 1');
$brand = $company && !empty($company['name']) ? (string) $company['name'] : 'Midhub360';
$paidAt = $b['paid_at'] ?: ($b['updated_at'] ?: $b['submitted_date']);
$receiptNo = 'BP-' . str_pad((string) $b['id'], 8, '0', STR_PAD_LEFT);
$total = number_format((float) $b['bill_total_amount'], 2, '.', '');
$charge = number_format((float) $b['charge'], 2, '.', '');
$debited = number_format($b['debited_amount'] !== null ? (float) $b['debited_amount'] : (float) $b['bill_total_amount'] + (float) $b['charge'], 2, '.', '');

$pdf = new SimplePdf();
$pdf->box(0, 0, 420, 70, 0.12);
$pdf->text(28, 32, $brand, 16, true);
$pdf->text(28, 52, 'Utility Bill Payment Receipt', 10);
$pdf->text(392, 32, 'PAID', 16, true, 'right');
$pdf->text(392, 52, $receiptNo, 9, false, 'right');

$y = 100;
$rows = array(
    array('Receipt No', $receiptNo),
    array('Paid On', date('d M Y, h:i A', strtotime((string) $paidAt))),
    array('Biller', $b['company'] . ' (' . $b['type'] . ', ' . $b['ac_type'] . ')'),
    array('Account / Bill No', $b['ac_number']),
    array('Bill No', $b['bill_no'] ?: '-'),
    array('Billing Month', trim((string) $b['billing_month']) ?: '-'),
    array('Customer Name', $b['ac_name'] ?: '-'),
    array('Customer Mobile', $b['contact_number'] ?: '-'),
    array('Biller Transaction ID', ($b['txnid'] && $b['txnid'] !== '0') ? $b['txnid'] : ($b['gateway_ref'] ?: '-')),
    array('Paid By (Agent)', $b['username']),
);
foreach ($rows as $r) {
    $pdf->text(28, $y, $r[0], 9);
    $pdf->text(392, $y, (string) $r[1], 9, true, 'right');
    $pdf->line(28, $y + 7, 392, $y + 7);
    $y += 22;
}
$y += 8;
$pdf->box(28, $y - 14, 364, 74, 0.94);
$pdf->text(40, $y, 'Bill Amount (incl. VAT & biller charge)', 9);
$pdf->text(380, $y, $total . ' BDT', 9, false, 'right');
$pdf->text(40, $y + 20, 'Service Charge', 9);
$pdf->text(380, $y + 20, $charge . ' BDT', 9, false, 'right');
$pdf->text(40, $y + 44, 'Total Paid', 11, true);
$pdf->text(380, $y + 44, $debited . ' BDT', 11, true, 'right');

$pdf->text(210, 540, 'This is a computer-generated receipt and needs no signature.', 8, false, 'center');
$pdf->text(210, 556, 'Verify: ' . rtrim(APP_URL, '/') . '  |  Ref ' . $b['gateway_ref'], 8, false, 'center');

$file = 'receipt-' . $receiptNo . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: ' . (isset($_GET['view']) ? 'inline' : 'attachment') . '; filename="' . $file . '"');
header('Cache-Control: private, no-store');
echo $pdf->output();
