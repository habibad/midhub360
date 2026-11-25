<?php
namespace Midhub\BillPay;

use Midhub\Alert\AlertService;
use Midhub\Db;
use Midhub\Gateway\GatewayException;
use Midhub\Money;
use Midhub\Security\PinVerifier;
use Midhub\Wallet\InsufficientBalanceException;
use Midhub\Wallet\Wallet;

/**
 * Utility bill payment from the Banking & Utility wallet (user.bkash_balance).
 *
 *   fetch  -> PIN verified, bill fetched from the aggregator, stored as a DRAFT (status 9).
 *             A draft holds no money and is invisible to the admin queues.
 *   pay    -> only for a draft fetched (with PIN) in this session in the last 15 minutes.
 *             Row lock + wallet debit of bill total + service charge in one transaction.
 *             API mode  : sent to the aggregator -> 1 paid | 3 failed + refund | 6 processing
 *             MANUAL    : status 0 in the admin Bill Pay queue; the admin pays it and marks it
 *                         Success (receipt) or Cancel (automatic refund of the debited amount).
 *
 * Replaces the legacy flow, which had no login check, no balance check, no PIN, was
 * SQL-injectable, charged twice on repeat, and marked bills PAID without paying the biller.
 *
 * billpay_request.status: 9 draft, 0 queued for admin, 6 processing at aggregator, 1 paid, 3 failed/refunded.
 */
final class BillPayment
{
    const DRAFT = '9';
    const AUTH_TTL = 900;

    /** Service charge by the admin's 4 amount slots (Bill Pay Charge page). */
    public static function charge(string $total): string
    {
        $row = Db::one('SELECT slot1, slot2, slot3, slot4 FROM billpay_charge WHERE id = 1');
        if (!$row) {
            return '0.00';
        }
        $t = (float) $total;
        $slot = $t >= 1 && $t <= 400 ? 'slot1' : ($t <= 1500 ? 'slot2' : ($t <= 5000 ? 'slot3' : ($t <= 30000 ? 'slot4' : null)));
        return $slot === null ? '0.00' : Money::fromPaisa(Money::toPaisa((string) (is_numeric($row[$slot]) ? $row[$slot] : '0')));
    }

    /**
     * @param array<string,string> $in company, company_type, type, number, contact_number, amount, cs_name, month, pincode
     * @return array response for the bill pages (aggregator JSON + our charge rows)
     */
    public static function fetch(int $userId, array $in): array
    {
        $company = trim((string) ($in['company'] ?? ''));
        $companyType = trim((string) ($in['company_type'] ?? ''));
        $b = BillerCatalog::resolve($company, $companyType);
        if ($b === null) {
            return self::err('This biller is not supported yet.');
        }
        $number = trim((string) ($in['number'] ?? ''));
        $contact = preg_replace('/\D/', '', (string) ($in['contact_number'] ?? ''));
        $amount = trim((string) ($in['amount'] ?? ''));
        $month = trim((string) ($in['month'] ?? ''));
        $csName = trim((string) ($in['cs_name'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9-]{3,30}$/', $number)) {
            return self::err('Enter a valid account / bill / meter number.');
        }
        if (in_array('contact', $b['fields'], true) && !preg_match('/^01[3-9]\d{8}$/', $contact)) {
            return self::err('Enter a valid 11-digit mobile number.');
        }
        if (in_array('amount', $b['fields'], true) && (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $amount) || (float) $amount <= 0)) {
            return self::err('Enter a valid amount.');
        }
        $pin = PinVerifier::verify($userId, trim((string) ($in['pincode'] ?? '')));
        if (!$pin['ok']) {
            return self::err($pin['error']);
        }

        $svc = BillPayService::load();
        if (!$svc->config()->isActive()) {
            return self::err('Bill payment is temporarily unavailable.');
        }
        try {
            $j = $svc->fetch($company, $b['type'], array('number' => $number, 'contact' => $contact, 'amount' => $amount, 'month' => $month));
        } catch (GatewayException $e) {
            return self::err($e->userMessage);
        }
        if (!isset($j['result']) || $j['result'] !== 'success' || empty($j['bill_ref']['bill_payment_id'])) {
            return self::err(isset($j['message']) ? (string) $j['message'] : 'Failed to fetch the bill.');
        }
        $d = isset($j['data']) && is_array($j['data']) ? $j['data'] : array();
        if (isset($d['is_bill_paid']) && strtoupper((string) $d['is_bill_paid']) === 'Y') {
            return self::err('This bill is already paid at the biller.');
        }
        $total = Money::fromPaisa(Money::toPaisa((string) ($d['bill_total_amount'] ?? '0')));
        if (Money::toPaisa($total) <= 0) {
            return self::err('The biller returned no payable amount.');
        }
        $charge = self::charge($total);
        $payable = Money::add($total, $charge);
        $bpid = substr((string) $j['bill_ref']['bill_payment_id'], 0, 30);
        $createdBy = (int) Db::scalar('SELECT created_by FROM user WHERE id = ?', 'i', array($userId));
        $ref = 'BP' . date('ymdHis') . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $s = function ($v, $len) {
            return substr(trim((string) $v), 0, $len);
        };
        Db::insert(
            "INSERT INTO billpay_request (user_id, type, company, ac_name, ac_number, ac_type, balance, charge, cost, billing_month, contact_number,
                submitted_date, s_time, s_date, level1, level2, level3, level4, level5, ip, rfrom, txnid, status, trx_status,
                bill_payment_id, bill_refer_id, bill_no, biller_acc_no, biller_mobile, bill_amount, bill_vat, vendor_charge, bill_total_amount,
                is_bill_paid, biller_id, created_at, gateway_mode, gateway_ref, provider_response)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, CURDATE(), '0', '0', '0', ?, ?, ?, 'web', '0', '9', 1,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
            'i' . str_repeat('s', 10) . 'i' . str_repeat('s', 17), // 29 params: user_id, 10 strings, s_time, 17 strings
            array($userId, $s($b['category'], 50), $s($company, 50), $s($csName, 50), $s($number, 20), $s($b['type'], 15),
                Money::fromPaisa(Money::toPaisa((string) ($d['bill_amount'] ?? $total))), $charge, $total, $s($month ?: (($d['bill_month'] ?? '') . ' ' . ($d['bill_year'] ?? '')), 100),
                $s($contact, 25), time(), (string) $userId, (string) $createdBy, $s(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli', 200),
                $bpid, $s($j['bill_ref']['bill_refer_id'] ?? '', 50), $s($d['bill_no'] ?? '', 50), $s($d['biller_acc_no'] ?? '', 50),
                $s($d['biller_mobile'] ?? '', 15), Money::fromPaisa(Money::toPaisa((string) ($d['bill_amount'] ?? '0'))),
                Money::fromPaisa(Money::toPaisa((string) ($d['bill_vat'] ?? '0'))), Money::fromPaisa(Money::toPaisa((string) ($d['charge'] ?? '0'))),
                $total, $s($d['is_bill_paid'] ?? 'N', 10), $s($d['bllr_id'] ?? '', 20), $svc->config()->mode, $ref,
                substr(json_encode($j), 0, 60000))
        );
        self::session();
        $_SESSION['bp_auth'][$bpid] = time(); // PIN verified for this bill; pay must follow within 15 min

        unset($j['_slug']);
        $j['invoice'] = isset($j['invoice']) && is_array($j['invoice']) ? $j['invoice'] : array(array('label_en' => 'Bill Amount', 'value' => $total));
        $j['invoice'][] = array('label_en' => 'Service Charge', 'value' => $charge);
        $j['invoice'][] = array('label_en' => 'Total from your wallet', 'value' => $payable, 'is_total_row' => true);
        $j['midhub'] = array('charge' => $charge, 'payable' => $payable, 'mode' => $svc->apiMode() ? 'api' : 'manual');
        return $j;
    }

    /** @return array{result:string,message:string,receipt_url?:string} */
    public static function pay(int $userId, string $bpid): array
    {
        self::session();
        $bpid = substr(trim($bpid), 0, 30);
        $authAt = isset($_SESSION['bp_auth'][$bpid]) ? (int) $_SESSION['bp_auth'][$bpid] : 0;
        if ($bpid === '' || $authAt < time() - self::AUTH_TTL) {
            return self::err('Please fetch the bill again and confirm with your PIN.');
        }
        $svc = BillPayService::load();
        $api = $svc->apiMode();
        try {
            $bill = Db::transaction(function () use ($userId, $bpid, $api) {
                $bill = Db::one("SELECT * FROM billpay_request WHERE bill_payment_id = ? AND user_id = ? AND status = '9' ORDER BY id DESC LIMIT 1 FOR UPDATE", 'si', array($bpid, $userId));
                if (!$bill) {
                    throw new \DomainException('This bill was already paid or has expired. Fetch it again.');
                }
                $total = Money::add((string) $bill['bill_total_amount'], (string) $bill['charge']);
                Wallet::debit($userId, Wallet::BANKING, $total, 'billpay', (string) $bill['gateway_ref'], $bill['company'] . ' ' . $bill['ac_number']);
                Db::exec("UPDATE billpay_request SET status = ?, cost = ?, debited_amount = ?, submitted_date = NOW() WHERE id = ?",
                    'sssi', array($api ? '6' : '0', $total, $total, (int) $bill['id']));
                $bill['debited_amount'] = $total;
                return $bill;
            });
        } catch (InsufficientBalanceException $e) {
            return self::err('Insufficient Banking & Utility wallet balance.');
        } catch (\DomainException $e) {
            return self::err($e->getMessage());
        }
        unset($_SESSION['bp_auth'][$bpid]);

        if (!$api) {
            return array('result' => 'success', 'message' => 'Bill payment submitted (' . $bill['debited_amount'] . ' Tk). It will be paid to ' . $bill['company'] . ' shortly; you can download the receipt once it is completed.');
        }
        $b = BillerCatalog::resolve((string) $bill['company'], (string) $bill['ac_type']);
        $r = $svc->pay($bill, (string) $bill['gateway_ref'], $b ? $b['slug'] : '');
        $status = self::settle((int) $bill['id'], $r, true);
        if ($status === '1') {
            return array('result' => 'success', 'message' => 'Bill paid successfully.', 'receipt_url' => 'bill_receipt.php?id=' . (int) $bill['id']);
        }
        if ($status === '3') {
            return self::err('The biller rejected the payment. ' . $bill['debited_amount'] . ' Tk was refunded to your wallet.');
        }
        return array('result' => 'success', 'message' => 'Bill payment is processing. The receipt will be available in Bill Receipts shortly.');
    }

    /** Applies an aggregator outcome to a processing (6) bill exactly once. */
    public static function settle(int $billId, array $r, bool $initial = false): string
    {
        return Db::transaction(function () use ($billId, $r, $initial) {
            $bill = Db::one('SELECT * FROM billpay_request WHERE id = ? FOR UPDATE', 'i', array($billId));
            if (!$bill || (string) $bill['status'] !== '6') {
                return $bill ? (string) $bill['status'] : '3';
            }
            $resp = substr(json_encode($r['raw']), 0, 60000);
            if ($r['outcome'] === BillPayService::SUCCESS) {
                Db::exec("UPDATE billpay_request SET status = '1', trx_status = 2, is_bill_paid = 'Y', txnid = ?, paid_at = NOW(), provider_response = ?, checked_at = NOW() WHERE id = ?",
                    'ssi', array(substr((string) ($r['trx_id'] ?: $bill['gateway_ref']), 0, 30), $resp, $billId));
                Db::insert("INSERT INTO notification (UserId, submitted_time, message, MsgFrom, seen, date) VALUES (?, NOW(), ?, 'Bill Pay', '0', CURDATE())",
                    'is', array((int) $bill['user_id'], substr("Your {$bill['company']} bill ({$bill['ac_number']}) is paid. Receipt: Bill Receipts.", 0, 250)));
                return '1';
            }
            if ($r['outcome'] === BillPayService::FAILED && ($initial || (int) $bill['check_attempts'] >= 2)) {
                Db::exec("UPDATE billpay_request SET status = '3', provider_response = ?, checked_at = NOW() WHERE id = ?", 'si', array($resp, $billId));
                $amt = $bill['debited_amount'] !== null ? (string) $bill['debited_amount'] : Money::add((string) $bill['bill_total_amount'], (string) $bill['charge']);
                Wallet::credit((int) $bill['user_id'], Wallet::BANKING, $amt, 'billpay_refund', (string) $bill['gateway_ref'], 'Refund: ' . substr($r['message'], 0, 100));
                return '3';
            }
            Db::exec('UPDATE billpay_request SET provider_response = ?, checked_at = NOW(), check_attempts = check_attempts + ? WHERE id = ?', 'sii', array($resp, $initial ? 0 : 1, $billId));
            return '6';
        });
    }

    /** Cron: re-check processing bills (current mode only). */
    public static function poll(int $limit = 30): array
    {
        $svc = BillPayService::load();
        $counts = array('1' => 0, '3' => 0, '6' => 0);
        if (!$svc->config()->isActive() || !$svc->apiMode()) {
            return $counts;
        }
        $rows = Db::all("SELECT * FROM billpay_request WHERE status = '6' AND gateway_mode = ? AND submitted_date < NOW() - INTERVAL 60 SECOND
                          AND (checked_at IS NULL OR checked_at < NOW() - INTERVAL 60 SECOND) ORDER BY id LIMIT ?", 'si', array($svc->config()->mode, $limit));
        foreach ($rows as $bill) {
            $b = BillerCatalog::resolve((string) $bill['company'], (string) $bill['ac_type']);
            $s = self::settle((int) $bill['id'], $svc->status($bill, (string) $bill['gateway_ref'], $b ? $b['slug'] : ''));
            $counts[$s]++;
            if ($s === '6' && (int) $bill['check_attempts'] >= 30) {
                AlertService::raise('bill_stuck:' . $bill['gateway_ref'], 'warning', 'Bill payment stuck', "Bill {$bill['gateway_ref']} ({$bill['company']} {$bill['ac_number']}) has no final status after 30 checks.", 1440);
            }
        }
        // drafts never paid are removed after a day (they hold no money)
        Db::exec("DELETE FROM billpay_request WHERE status = '9' AND created_at < NOW() - INTERVAL 1 DAY");
        return $counts;
    }

    /** The PIN-verified-fetch marker lives in the PHP session (web), or in $_SESSION as a plain array (CLI). */
    private static function session(): void
    {
        if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli' && !headers_sent()) {
            session_start();
        }
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = array();
        }
    }

    private static function err(string $m): array
    {
        return array('result' => 'error', 'message' => $m);
    }
}
