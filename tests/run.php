<?php
/**
 * Midhub360 automated test suite (CLI):  php tests/run.php
 *
 * Runs against the configured database (use a dev/staging DB, never production), in
 * STRICT SQL mode like MySQL 8. External providers are replaced by tests/mock_providers.php
 * on 127.0.0.1:8765, so the real gateway classes are exercised over real HTTP.
 * QA users and gateway settings are created and fully restored/removed afterwards.
 */
if (PHP_SAPI !== 'cli') {
    exit;
}
require_once __DIR__ . '/TestKit.php';

use Midhub\Crypto;
use Midhub\Db;
use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayFactory;
use Midhub\Gateway\GatewayRegistry;
use Midhub\Gateway\StripeService;
use Midhub\Http\HttpResponse;
use Midhub\Money;
use Midhub\Payment\DepositService;
use Midhub\Recharge\PaywellService;
use Midhub\Recharge\RechargeService;
use Midhub\Voucher\PopularVoucherService;
use Midhub\Voucher\VoucherService;
use Midhub\Wallet\Wallet;

$T = 'TestKit';
echo "Midhub360 test suite - DB " . DB_NAME . " (" . APP_ENV . ")\n";
if (!IS_LOCAL && !in_array('--allow-nonlocal', $argv, true)) {
    exit("Refusing to run outside a local environment. Pass --allow-nonlocal for a staging DB.\n");
}

TestKit::setUp();
try {
    // =====================================================================
    TestKit::section('Money & crypto');
    TestKit::eq('parse normalizes', '100.50', Money::parse('100.5'));
    TestKit::eq('percent rounds half-up', '18.51', Money::percent('1000.50', '1.85'));
    TestKit::eq('USD->BDT multiply', '1225.00', Money::multiply('10.00', '122.5'));
    TestKit::eq('USD->BDT 4dp rate', '1219.13', Money::multiply('9.99', '122.0350'));
    $bad = 0;
    foreach (array('-5', '1e3', '10.555', 'abc', '', '12345678901') as $v) {
        try { Money::parse($v); } catch (\InvalidArgumentException $e) { $bad++; }
    }
    TestKit::eq('parse rejects negative/exponent/3dp/text/empty/overflow', 6, $bad);
    $enc = Crypto::encrypt('s3cret-value');
    TestKit::check('encrypt is prefixed and not plaintext', strpos($enc, 'enc:v1:') === 0 && strpos($enc, 's3cret') === false);
    TestKit::eq('decrypt roundtrip', 's3cret-value', Crypto::decrypt($enc));
    $tampered = substr($enc, 0, -4) . (substr($enc, -4) === 'AAAA' ? 'BBBB' : 'AAAA');
    $thrown = false;
    try { Crypto::decrypt($tampered); } catch (\RuntimeException $e) { $thrown = true; }
    TestKit::check('tampered ciphertext is rejected (GCM tag)', $thrown);
    TestKit::eq('legacy plaintext passes through', 'plain', Crypto::decrypt('plain'));

    // =====================================================================
    TestKit::section('Stripe webhook signature');
    $payload = '{"id":"evt_1"}';
    $now = time();
    $sig = 't=' . $now . ',v1=' . hash_hmac('sha256', $now . '.' . $payload, 'whsec_x');
    TestKit::check('valid signature accepted', StripeService::verifySignature($payload, $sig, 'whsec_x', $now));
    TestKit::check('wrong secret rejected', !StripeService::verifySignature($payload, $sig, 'whsec_y', $now));
    TestKit::check('modified payload rejected', !StripeService::verifySignature('{"id":"evt_2"}', $sig, 'whsec_x', $now));
    TestKit::check('replay outside 5 min rejected', !StripeService::verifySignature($payload, $sig, 'whsec_x', $now + 301));
    TestKit::check('any matching v1 accepted (secret rotation)', StripeService::verifySignature($payload, 't=' . $now . ',v1=deadbeef,' . substr($sig, strpos($sig, 'v1=')), 'whsec_x', $now));

    // =====================================================================
    TestKit::section('Paywell response classification (never refund an unknown outcome)');
    TestKit::configure('paywell', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => TestKit::MOCK . '/paywell',
        'creds' => array('username' => 'qa_user', 'password' => 'qa_pass', 'api_username' => 'qa_api_user', 'api_password' => 'qa_api_pass', 'api_key' => 'qa_api_key_123', 'encryption_key' => 'qa_encryption_key_456'),
        'min_amount' => '5', 'max_amount' => '10000'));
    $pw = new PaywellService(GatewayConfig::load('paywell', true));
    $mk = function ($errno, $status, $body) { $r = new HttpResponse(); $r->errno = $errno; $r->status = $status; $r->body = $body; return $r; };
    TestKit::eq('connection refused -> FAILED (safe to refund)', 'FAILED', $pw->classify($mk(7, 0, ''))['outcome']);
    TestKit::eq('timeout -> PENDING', 'PENDING', $pw->classify($mk(28, 0, ''))['outcome']);
    TestKit::eq('HTTP 500 -> PENDING', 'PENDING', $pw->classify($mk(0, 500, 'oops'))['outcome']);
    TestKit::eq('non-JSON 200 -> PENDING', 'PENDING', $pw->classify($mk(0, 200, '<html>'))['outcome']);
    TestKit::eq('status 200 -> SUCCESS', 'SUCCESS', $pw->classify($mk(0, 200, '{"status":200,"data":{"trx_id":"X"}}'))['outcome']);
    TestKit::eq('status 100 -> PENDING', 'PENDING', $pw->classify($mk(0, 200, '{"status":100}'))['outcome']);
    TestKit::eq('structured rejection 405 -> FAILED', 'FAILED', $pw->classify($mk(0, 200, '{"status":405,"message":"bad"}'))['outcome']);
    TestKit::eq('operator code GP', 'GP', $pw->operatorCode('grameenphone'));
    TestKit::eq('skitto falls back to registry default SK', 'SK', $pw->operatorCode('skitto'));

    // =====================================================================
    TestKit::section('Upline hierarchy columns (must match the legacy engine)');
    $u = TestKit::$users;
    TestKit::eq('L0 retailer', array(1 => (string) $u['qa_l0'], 2 => (string) $u['qa_l1'], 3 => (string) $u['qa_l2'], 4 => (string) $u['qa_l3'], 5 => (string) $u['qa_l4']), RechargeService::uplineLevels($u['qa_l0'], 0));
    TestKit::eq('L1', array(1 => '0', 2 => (string) $u['qa_l1'], 3 => (string) $u['qa_l2'], 4 => (string) $u['qa_l3'], 5 => (string) $u['qa_l4']), RechargeService::uplineLevels($u['qa_l1'], 1));
    TestKit::eq('L4 sole distributor', array(1 => '0', 2 => '0', 3 => '0', 4 => '0', 5 => (string) $u['qa_l4']), RechargeService::uplineLevels($u['qa_l4'], 4));

    // =====================================================================
    TestKit::section('Concurrency: double-spend is impossible');
    TestKit::setBalance('qa_l0', 'balance2', '100.00');
    $out = TestKit::parallel(10, array('wallet_debit', (string) $u['qa_l0'], '30'));
    $ok = count(array_filter($out, function ($l) { return $l === 'OK'; }));
    $errs = array_filter($out, function ($l) { return strpos($l, 'ERR') === 0; });
    TestKit::eq('Wallet::debit 10 racers x 30 from 100: exactly 3 succeed', 3, $ok);
    TestKit::check('no racer errored', !$errs, implode(' | ', $errs));
    TestKit::eq('balance is exactly 10.00', '10.00', TestKit::balance('qa_l0', 'balance2'));
    $led = Db::one("SELECT COUNT(*) n, SUM(amount) s, MIN(balance_after) lo FROM wallet_ledger WHERE user_id = ? AND ref_type = 'qa_race'", 'i', array($u['qa_l0']));
    TestKit::check('ledger has 3 debits totalling 90, never negative', (int) $led['n'] === 3 && (float) $led['s'] === 90.0 && (float) $led['lo'] >= 0, json_encode($led));

    TestKit::setBalance('qa_l1', 'balance2', '100.00');
    $out = TestKit::parallel(10, array('legacy_debit', (string) $u['qa_l1'], '30'));
    TestKit::eq('legacy Request::atomic_debit SQL: exactly 3 succeed', 3, count(array_filter($out, function ($l) { return $l === 'OK'; })));
    TestKit::eq('legacy path balance exactly 10.00', '10.00', TestKit::balance('qa_l1', 'balance2'));

    // =====================================================================
    TestKit::section('Recharge: manual queue when no provider is active');
    TestKit::configure('paywell', array('status' => 'inactive'));
    TestKit::setBalance('qa_l0', 'balance2', '500.00');
    $r = RechargeService::submit($u['qa_l0'], 0, '01711000001', '50', '1', '0.01');
    $row = Db::one('SELECT * FROM send_query WHERE id = ?', 'i', array(isset($r['id']) ? $r['id'] : 0));
    TestKit::check('submitted to manual queue (status 0)', $r['ok'] && $row && $row['status'] === '0' && $row['gateway'] === null, json_encode($r));
    TestKit::eq('debited 50', '450.00', TestKit::balance('qa_l0', 'balance2'));
    TestKit::check('pre/new balance recorded', $row && (float) $row['pre_balance'] === 500.0 && (float) $row['new_balance'] === 450.0);
    $r2 = RechargeService::submit($u['qa_l0'], 0, '01711000001', '50', '1', '0.01');
    TestKit::check('same number pending -> second request refused', !$r2['ok'] && strpos($r2['message'], 'already request') !== false, $r2['message']);
    TestKit::eq('refused request did not debit', '450.00', TestKit::balance('qa_l0', 'balance2'));
    $r3 = RechargeService::submit($u['qa_l0'], 0, '01711000002', '50.50', '1', '0.01');
    TestKit::check('fractional Taka refused', !$r3['ok']);
    $r4 = RechargeService::submit($u['qa_l0'], 0, '01711000003', '450', '1', '0.01');
    TestKit::check('low-balance floor: cannot drain to 0 (legacy strict rule)', !$r4['ok'] && TestKit::balance('qa_l0', 'balance2') === '450.00', $r4['message']);
    $r5 = RechargeService::submit($u['qa_l0'], 0, '01811000004', '10', '9', '0.01');
    TestKit::check('unknown load type refused', !$r5['ok']);

    // =====================================================================
    TestKit::section('Recharge via Paywell (mock): success / reject / pending / ambiguous');
    TestKit::configure('paywell', array('status' => 'active'));
    TestKit::setBalance('qa_l0', 'balance2', '1000.00');
    $r = RechargeService::submit($u['qa_l0'], 0, '01712000011', '11', '1', '0.01');
    $row = Db::one('SELECT * FROM send_query WHERE id = ?', 'i', array($r['id']));
    TestKit::check('amount 11 -> success (status 1)', $r['ok'] && $row['status'] === '1' && strpos($row['txnid'], 'PWTRX') === 0, json_encode($r));
    TestKit::check('gateway, sandbox mode and ref recorded', $row['gateway'] === 'paywell' && $row['gateway_mode'] === 'sandbox' && preg_match('/^RC\d{18}$/', $row['gateway_ref']));
    $mockState = json_decode((string) @file_get_contents(sys_get_temp_dir() . '/midhub_mock_state.json'), true);
    $sent = $mockState['pw'][$row['gateway_ref']];
    TestKit::check('provider received GP + prepaid + exact amount (HMAC accepted)', $sent['operator'] === 'GP' && $sent['con_type'] === 'prepaid' && $sent['amount'] === 11, json_encode($sent));
    TestKit::eq('reportserver success row written', 1, (int) Db::scalar("SELECT COUNT(*) FROM reportserver WHERE f_id = ? AND status = '1'", 's', array((string) $row['id'])));
    TestKit::eq('balance 989', '989.00', TestKit::balance('qa_l0', 'balance2'));

    $r = RechargeService::submit($u['qa_l0'], 0, '01713000011', '11', '2', '0.01');
    $ref = Db::scalar('SELECT gateway_ref FROM send_query WHERE id = ?', 'i', array($r['id']));
    $mockState = json_decode((string) file_get_contents(sys_get_temp_dir() . '/midhub_mock_state.json'), true);
    TestKit::eq('type 2 sent as postpaid', 'postpaid', $mockState['pw'][$ref]['con_type']);
    $r = RechargeService::submit($u['qa_l0'], 0, '01714000011', '11', '3', '0.01');
    $ref = Db::scalar('SELECT gateway_ref FROM send_query WHERE id = ?', 'i', array($r['id']));
    $mockState = json_decode((string) file_get_contents(sys_get_temp_dir() . '/midhub_mock_state.json'), true);
    TestKit::eq('type 3 sent with Skitto operator code', 'SK', $mockState['pw'][$ref]['operator']);
    TestKit::eq('balance 967 after 3 successes', '967.00', TestKit::balance('qa_l0', 'balance2'));

    $r = RechargeService::submit($u['qa_l0'], 0, '01915000013', '13', '1', '0.01');
    $row = Db::one('SELECT * FROM send_query WHERE id = ?', 'i', array($r['id']));
    TestKit::check('amount 13 -> provider rejection -> status 3', !$r['ok'] && $row['status'] === '3', json_encode($r));
    TestKit::eq('rejected recharge refunded', '967.00', TestKit::balance('qa_l0', 'balance2'));
    $again = RechargeService::settle((int) $row['id'], array('outcome' => PaywellService::FAILED, 'status_code' => 'x', 'message' => 'x', 'trx_id' => null, 'raw' => null));
    TestKit::check('second settle is a no-op (no double refund)', $again === '3' && TestKit::balance('qa_l0', 'balance2') === '967.00');
    TestKit::eq('ledger: one debit + one refund for that ref', 2, (int) Db::scalar('SELECT COUNT(*) FROM wallet_ledger WHERE ref_id = ?', 's', array($row['gateway_ref'])));

    $r = RechargeService::submit($u['qa_l0'], 0, '01616000012', '12', '1', '0.01');
    $pendId = $r['id'];
    $row = Db::one('SELECT * FROM send_query WHERE id = ?', 'i', array($pendId));
    TestKit::check('amount 12 -> provider pending -> stays processing (6), money held', $row['status'] === '6' && TestKit::balance('qa_l0', 'balance2') === '955.00');
    $c = RechargeService::poll(50);
    TestKit::eq('poller waits 60s before first enquiry', 0, array_sum($c));
    Db::exec('UPDATE send_query SET submitted_date = NOW() - INTERVAL 5 MINUTE WHERE id = ?', 'i', array($pendId));
    RechargeService::poll(50);
    TestKit::eq('poller also waits 60s after the last check', '6', Db::scalar('SELECT status FROM send_query WHERE id = ?', 'i', array($pendId)));
    Db::exec('UPDATE send_query SET status_checked_at = NOW() - INTERVAL 2 MINUTE WHERE id = ?', 'i', array($pendId));
    RechargeService::poll(50);
    TestKit::eq('enquiry resolves pending -> success', '1', Db::scalar('SELECT status FROM send_query WHERE id = ?', 'i', array($pendId)));

    $r = RechargeService::submit($u['qa_l0'], 0, '01517000015', '15', '1', '0.01');
    $ambId = $r['id'];
    $ambRef = Db::scalar('SELECT gateway_ref FROM send_query WHERE id = ?', 'i', array($ambId));
    TestKit::eq('HTTP 500 (ambiguous) -> stays processing, not refunded', '6', Db::scalar('SELECT status FROM send_query WHERE id = ?', 'i', array($ambId)));
    TestKit::mock('/__pw_final?ref=' . $ambRef . '&final=failed');
    $statuses = array();
    for ($i = 1; $i <= 3; $i++) {
        Db::exec('UPDATE send_query SET submitted_date = NOW() - INTERVAL 5 MINUTE, status_checked_at = NOW() - INTERVAL 5 MINUTE WHERE id = ?', 'i', array($ambId));
        RechargeService::poll(50);
        $statuses[] = Db::scalar('SELECT status FROM send_query WHERE id = ?', 'i', array($ambId));
    }
    TestKit::eq('enquiry "failed" must repeat 3x before refunding', array('6', '6', '3'), $statuses);
    TestKit::eq('refunded exactly once after 3rd failed enquiry', '955.00', TestKit::balance('qa_l0', 'balance2'));

    Db::exec("UPDATE send_query SET gateway_mode = 'live', status = '6', submitted_date = NOW() - INTERVAL 5 MINUTE, status_checked_at = NULL WHERE id = ?", 'i', array($pendId));
    $before = Db::scalar('SELECT check_attempts FROM send_query WHERE id = ?', 'i', array($pendId));
    RechargeService::poll(50);
    TestKit::eq('a LIVE row is never enquired while in sandbox mode', $before, Db::scalar('SELECT check_attempts FROM send_query WHERE id = ?', 'i', array($pendId)));
    Db::exec("UPDATE send_query SET status = '1' WHERE id = ?", 'i', array($pendId));

    $failId = RechargeService::submit($u['qa_l0'], 0, '01616000099', '12', '1', '0.01')['id'];
    $out = TestKit::parallel(6, array('recharge_settle_fail', (string) $failId));
    TestKit::eq('6 concurrent failure settlements refund exactly once', '955.00', TestKit::balance('qa_l0', 'balance2'));
    TestKit::eq('exactly one refund ledger row', 1, (int) Db::scalar("SELECT COUNT(*) FROM wallet_ledger WHERE ref_type = 'recharge_refund' AND ref_id = (SELECT gateway_ref FROM send_query WHERE id = ?)", 'i', array($failId)));

    // =====================================================================
    TestKit::section('Deposit via bKash Tokenized (mock): exactly-once credit');
    TestKit::configure('bkash', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => TestKit::MOCK . '/bkash',
        'creds' => array('app_key' => 'qa_bkash_app_key', 'app_secret' => 'qa_bk_secret', 'username' => 'qa_bk_user', 'password' => 'qa_bk_pass'),
        'charge_percentage' => '1.50', 'min_amount' => '10', 'max_amount' => '25000'));
    TestKit::check('bKash test connection (grant token + create payment with our callback URL)', GatewayFactory::any('bkash')->testConnection()['ok']);
    TestKit::setBalance('qa_l2', 'bkash_balance', '0.00');
    $nonce = bin2hex(random_bytes(16));
    $d = DepositService::create($u['qa_l2'], 'bkash', 'bkash_balance', '1000', $nonce);
    $txn = DepositService::find($d['invoice_no']);
    TestKit::check('invoice created, pending, checkout URL from bKash', $txn['status'] === 'pending' && strpos($d['redirect_url'], '/bkash-pay?paymentID=') !== false);
    TestKit::check('charge 1.5% added: pays 1015.00, credit 1000.00', $txn['payable_amount'] === '1015.00' && $txn['credit_amount'] === '1000.00');
    $d2 = DepositService::create($u['qa_l2'], 'bkash', 'bkash_balance', '1000', $nonce);
    TestKit::eq('double-submit with same nonce returns the same invoice', $d['invoice_no'], $d2['invoice_no']);
    $r = DepositService::settle($d['invoice_no'], 'return', array('paymentID' => 'TR00forged', 'status' => 'success'));
    TestKit::check('forged paymentID on return is rejected', $r['status'] === 'failed' && TestKit::balance('qa_l2', 'bkash_balance') === '0.00');

    $d = DepositService::create($u['qa_l2'], 'bkash', 'bkash_balance', '1000', bin2hex(random_bytes(16)));
    $txn = DepositService::find($d['invoice_no']);
    $r = DepositService::settle($d['invoice_no'], 'return', array('paymentID' => $txn['gateway_payment_id'], 'status' => 'success'));
    TestKit::check('customer return -> execute -> completed', $r['status'] === 'completed', json_encode($r['message']));
    TestKit::eq('Banking wallet credited 1000.00 (not the 1015 incl. charge)', '1000.00', TestKit::balance('qa_l2', 'bkash_balance'));
    $r = DepositService::settle($d['invoice_no'], 'return', array('paymentID' => $txn['gateway_payment_id'], 'status' => 'success'));
    TestKit::check('replayed return URL credits nothing', $r['status'] === 'completed' && $r['message'] === 'Already processed.' && TestKit::balance('qa_l2', 'bkash_balance') === '1000.00');
    TestKit::eq('mirrored to legacy add_fund history', 1, (int) Db::scalar("SELECT COUNT(*) FROM add_fund WHERE invoice_no = ? AND status = '1'", 's', array($d['invoice_no'])));
    TestKit::eq('customer notified', 1, (int) Db::scalar("SELECT COUNT(*) FROM notification WHERE UserId = ? AND message LIKE ?", 'is', array($u['qa_l2'], '%' . $d['invoice_no'] . '%')));

    // concurrent settlement of one paid invoice
    $d = DepositService::create($u['qa_l2'], 'bkash', 'bkash_balance', '500', bin2hex(random_bytes(16)));
    $txn = DepositService::find($d['invoice_no']);
    // customer paid + bKash executed out-of-band (as if our return handler crashed after execute)
    $ch = curl_init(TestKit::MOCK . '/bkash/tokenized/checkout/execute');
    curl_setopt_array($ch, array(CURLOPT_POST => 1, CURLOPT_RETURNTRANSFER => 1, CURLOPT_POSTFIELDS => json_encode(array('paymentID' => $txn['gateway_payment_id'])),
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: bk-id-token', 'X-APP-Key: qa_bkash_app_key')));
    curl_exec($ch);
    curl_close($ch);
    $out = TestKit::parallel(6, array('deposit_settle', $d['invoice_no']));
    TestKit::eq('6 concurrent reconcilers -> credited exactly once', '1500.00', TestKit::balance('qa_l2', 'bkash_balance'));
    TestKit::eq('exactly one deposit ledger credit', 1, (int) Db::scalar("SELECT COUNT(*) FROM wallet_ledger WHERE ref_type = 'deposit' AND ref_id = ?", 's', array($d['invoice_no'])));

    // amount tampering at the provider
    $d = DepositService::create($u['qa_l2'], 'bkash', 'bkash_balance', '700', bin2hex(random_bytes(16)));
    $txn = DepositService::find($d['invoice_no']);
    TestKit::mock('/__tamper/bkash?pid=' . $txn['gateway_payment_id'] . '&amount=7.00');
    $r = DepositService::settle($d['invoice_no'], 'return', array('paymentID' => $txn['gateway_payment_id'], 'status' => 'success'));
    TestKit::check('paid amount != invoice -> held for review, NOT credited', $r['status'] === 'review' && TestKit::balance('qa_l2', 'bkash_balance') === '1500.00', $r['status']);
    TestKit::check('admin alert raised for review', (int) Db::scalar('SELECT COUNT(*) FROM admin_alerts WHERE alert_key = ?', 's', array('deposit_review:' . $d['invoice_no'])) === 1);

    // Callback URL not registered for the merchant: bKash error 2049, which is what bKash's
    // public demo credentials now return for every callback URL. Grant Token still succeeds.
    TestKit::mock('/__bkash/reject_callback?on=1');
    $t = GatewayFactory::any('bkash')->testConnection();
    TestKit::check('test connection reports an unregistered callback URL (2049), not just "token OK"',
        !$t['ok'] && strpos($t['message'], '2049') !== false && strpos($t['message'], 'gateway/bkash_callback.php') !== false, $t['message']);
    $userMsg = '';
    $adminMsg = '';
    try {
        DepositService::create($u['qa_l2'], 'bkash', 'bkash_balance', '100', bin2hex(random_bytes(16)));
    } catch (\Midhub\Gateway\GatewayException $e) {
        $userMsg = $e->userMessage;
        $adminMsg = $e->getMessage();
    }
    TestKit::check('2049: customer sees a plain message, not "Invalid Merchant Callback URL"',
        $userMsg !== '' && stripos($userMsg, 'callback') === false && stripos($userMsg, 'another payment method') !== false, $userMsg);
    TestKit::check('2049: the admin-side reason keeps the bKash code and the callback URL',
        strpos($adminMsg, '2049') !== false && strpos($adminMsg, 'gateway/bkash_callback.php') !== false, $adminMsg);
    TestKit::eq('2049: nothing credited', '1500.00', TestKit::balance('qa_l2', 'bkash_balance'));
    TestKit::mock('/__bkash/reject_callback?on=0');
    Db::exec("DELETE FROM circuit_breakers WHERE cb_key = 'deposit:bkash'");

    $errs = array();
    foreach (array(array('bkash', '5'), array('bkash', '30000'), array('nagad', '100')) as $case) {
        try { DepositService::create($u['qa_l2'], $case[0], 'balance', $case[1], bin2hex(random_bytes(16))); $errs[] = 'accepted ' . implode('/', $case); } catch (\InvalidArgumentException $e) { /* expected */ }
    }
    TestKit::check('below min / above max / inactive gateway rejected', !$errs, implode(', ', $errs));
    $thrown = false;
    try { DepositService::create($u['qa_l2'], 'bkash', 'pass', '100', bin2hex(random_bytes(16))); } catch (\InvalidArgumentException $e) { $thrown = true; }
    TestKit::check('invalid wallet column rejected', $thrown);

    $pendInv = DepositService::create($u['qa_l2'], 'bkash', 'bkash_balance', '300', bin2hex(random_bytes(16)))['invoice_no'];
    TestKit::configure('bkash', array('mode' => 'live', 'live_base_url' => 'https://tokenized.pay.bka.sh/v1.2.0-beta'));
    $r = DepositService::settle($pendInv, 'reconcile');
    TestKit::check('a sandbox invoice is never settled after switching to live', $r['status'] === 'pending' && strpos($r['message'], 'mode changed') !== false);
    TestKit::configure('bkash', array('mode' => 'sandbox'));

    // =====================================================================
    TestKit::section('Deposit via Stripe Checkout Session (mock): 3DS, rate margin, new-customer limit, webhook');
    TestKit::configure('stripe', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => TestKit::MOCK . '/stripe',
        'creds' => array('publishable_key' => 'pk_test_qa', 'secret_key' => 'sk_test_qa123', 'webhook_secret' => 'whsec_qa_secret'),
        'exchange_rate' => '122.5000', 'charge_percentage' => '2.50', 'min_amount' => '5', 'max_amount' => '1000',
        'extra' => array('rate_margin_bdt' => '2.50', 'new_user_days' => '3', 'new_user_daily_usd' => '30')));
    TestKit::check('Stripe test connection (test key in sandbox mode)', GatewayFactory::any('stripe')->testConnection()['ok']);
    TestKit::setBalance('qa_l3', 'balance', '0.00');
    $d = DepositService::create($u['qa_l3'], 'stripe', 'balance', '10', bin2hex(random_bytes(16)));
    $txn = DepositService::find($d['invoice_no']);
    TestKit::check('USD invoice: pays 10.25, credit 1200.00 BDT at 122.5 minus 2.5 safety margin', $txn['currency'] === 'USD' && $txn['payable_amount'] === '10.25' && $txn['credit_amount'] === '1200.00' && (float) $txn['exchange_rate'] === 120.0, json_encode($txn));
    TestKit::check('redirects to the Stripe-hosted Checkout page', strpos($d['redirect_url'], '/stripe-pay/cs_test_') !== false && strpos($txn['gateway_payment_id'], 'cs_test_') === 0);
    $r = DepositService::settle($d['invoice_no'], 'reconcile');
    TestKit::eq('unpaid session stays pending', 'pending', $r['status']);
    $m = TestKit::mock('/__succeed/stripe?cs=' . $txn['gateway_payment_id']);
    TestKit::check('3DS requested on cards, idempotency key sent, all wallets allowed (no method lock)', $m['three_ds'] === 'any' && strpos($m['idem'], 'midhub-MH') === 0 && $m['pmt'] === null, json_encode($m));
    TestKit::check('success_url carries {CHECKOUT_SESSION_ID}', strpos($m['success_url'], 'session_id={CHECKOUT_SESSION_ID}') !== false);
    $r = DepositService::settle($d['invoice_no'], 'return', array('session_id' => 'cs_test_forged'));
    TestKit::check('forged session_id on return rejected', $r['status'] === 'failed' && TestKit::balance('qa_l3', 'balance') === '0.00');
    $d = DepositService::create($u['qa_l3'], 'stripe', 'balance', '10', bin2hex(random_bytes(16)));
    $txn = DepositService::find($d['invoice_no']);
    TestKit::mock('/__succeed/stripe?cs=' . $txn['gateway_payment_id']);
    $svc = GatewayFactory::deposit('stripe');
    $event = json_encode(array('id' => 'evt_qa_' . bin2hex(random_bytes(4)), 'type' => 'checkout.session.completed', 'livemode' => false,
        'data' => array('object' => array('id' => $txn['gateway_payment_id'], 'object' => 'checkout.session', 'client_reference_id' => $d['invoice_no'], 'metadata' => array('invoice_no' => $d['invoice_no'])))));
    $ts = time();
    $info = $svc->parseWebhook($event, array('stripe-signature' => 't=' . $ts . ',v1=' . hash_hmac('sha256', $ts . '.' . $event, 'whsec_qa_secret')));
    TestKit::check('checkout.session.completed webhook parsed to our invoice + session', $info['invoice_no'] === $d['invoice_no'] && $info['gateway_payment_id'] === $txn['gateway_payment_id']);
    $thrown = false;
    try { $svc->parseWebhook($event, array('stripe-signature' => 't=' . $ts . ',v1=' . hash_hmac('sha256', $ts . '.' . $event, 'whsec_wrong'))); } catch (\Throwable $e) { $thrown = true; }
    TestKit::check('webhook with wrong secret rejected', $thrown);
    $r = DepositService::settle($d['invoice_no'], 'webhook');
    TestKit::check('webhook settlement -> completed', $r['status'] === 'completed');
    TestKit::eq('Main wallet credited 1200.00 BDT', '1200.00', TestKit::balance('qa_l3', 'balance'));
    $thrown = '';
    try { DepositService::create($u['qa_l3'], 'stripe', 'balance', '25', bin2hex(random_bytes(16))); } catch (\InvalidArgumentException $e) { $thrown = $e->getMessage(); }
    TestKit::check('new card customer: $10 already today + $25 > $30/day -> refused', strpos($thrown, 'up to $30 per day') !== false, $thrown);
    $d = DepositService::create($u['qa_l3'], 'stripe', 'balance', '5', bin2hex(random_bytes(16)));
    $txn = DepositService::find($d['invoice_no']);
    $r = DepositService::settle($d['invoice_no'], 'return', array('cancel' => '1'));
    $st = json_decode((string) file_get_contents(sys_get_temp_dir() . '/midhub_mock_state.json'), true);
    TestKit::check('customer cancel expires the session at Stripe before marking cancelled', $r['status'] === 'cancelled' && $st['cs'][$txn['gateway_payment_id']]['status'] === 'expired', $r['status']);
    Db::exec("UPDATE gateway_transactions SET completed_at = NOW() - INTERVAL 5 DAY WHERE user_id = ? AND gateway_slug = 'stripe' AND status = 'completed'", 'i', array($u['qa_l3']));
    $ok = true;
    try { DepositService::create($u['qa_l3'], 'stripe', 'balance', '200', bin2hex(random_bytes(16))); } catch (\InvalidArgumentException $e) { $ok = false; }
    TestKit::check('established customer (first card payment > 3 days ago) is not limited', $ok);

    // =====================================================================
    TestKit::section('Digital vouchers');
    TestKit::configure('popular_voucher', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => ''));
    $s = VoucherService::syncCatalog();
    TestKit::check('mock catalog synced, new products start inactive', $s['mock'] && $s['done'] && $s['new'] + $s['updated'] === 12 && (int) Db::scalar('SELECT COUNT(*) FROM voucher_products WHERE active = 1 AND id > ?', 'i', array(TestKit::$productMaxId)) === 0, json_encode($s));
    Db::exec("UPDATE voucher_products SET active = 1, sell_price = 90.00 WHERE product_code = 'FF-100'");
    $pid = (int) Db::scalar("SELECT id FROM voucher_products WHERE product_code = 'FF-100'");
    TestKit::setBalance('qa_l1', 'balance', '200.00');
    $nonce = bin2hex(random_bytes(16));
    $r = VoucherService::purchase($u['qa_l1'], $pid, $nonce);
    TestKit::check('purchase delivered instantly in mock mode', $r['ok'] && $r['status'] === 'delivered', $r['message']);
    TestKit::eq('Main wallet debited sell price 90', '110.00', TestKit::balance('qa_l1', 'balance'));
    $o = Db::one('SELECT * FROM voucher_orders WHERE order_no = ?', 's', array($r['order_no']));
    TestKit::check('voucher code + PIN encrypted at rest', strpos((string) $o['voucher_code'], 'enc:v1:') === 0 && strpos((string) $o['voucher_pin'], 'enc:v1:') === 0);
    $vault = VoucherService::vault($u['qa_l1']);
    TestKit::check('vault decrypts TEST code for the owner', strpos((string) $vault[0]['voucher_code'], 'TEST-') === 0);
    $r2 = VoucherService::purchase($u['qa_l1'], $pid, $nonce);
    TestKit::check('re-submitted form (same nonce) does not charge twice', TestKit::balance('qa_l1', 'balance') === '110.00' && $r2['order_no'] === $r['order_no']);
    TestKit::setBalance('qa_l1', 'balance', '50.00');
    $r3 = VoucherService::purchase($u['qa_l1'], $pid, bin2hex(random_bytes(16)));
    TestKit::check('insufficient Main balance refused, nothing debited', !$r3['ok'] && TestKit::balance('qa_l1', 'balance') === '50.00');

    // --- Popular Vouchers v1.1 (mock server with the real API's shape) ---
    TestKit::mock('/__pv?count=230&bump=0&drop=&env=sandbox');
    TestKit::configure('popular_voucher', array('sandbox_base_url' => TestKit::MOCK . '/voucher', 'creds' => array('api_key' => 'qa_voucher_key'),
        'extra' => array('catalog_path' => '/distributor/catalog', 'order_path' => '/distributor/orders', 'order_status_path' => '/distributor/orders/{order_id}',
            'balance_path' => '/distributor/account', 'auth_header' => 'Authorization', 'order_product_field' => 'variant_id', 'order_reference_field' => 'reference', 'order_delivery_field' => 'delivery_data')));
    Db::exec("DELETE FROM platform_settings WHERE setting_key IN ('voucher_sync_state', 'voucher_sync_last')");
    $s1 = VoucherService::syncCatalog(0.0);
    $prog = VoucherService::syncProgress();
    TestKit::check('v1.1 sync is resumable: one page per run, offset saved', !$s1['done'] && $s1['fetched'] === 100 && $s1['total'] === 230 && $prog && (int) $prog['offset'] === 100, json_encode($s1));
    $s2 = VoucherService::syncCatalog(60.0);
    TestKit::check('continues to the end: all 230 read over 3 pages, the 2-field product skipped', $s2['done'] && $s2['fetched'] === 230 && $s2['skipped'] === 1 && VoucherService::syncProgress() === null
        && (int) Db::scalar("SELECT COUNT(*) FROM voucher_products WHERE product_code LIKE 'variant\_QA\_%'") === 229, json_encode($s2));
    $vp = function ($code) { return Db::one('SELECT * FROM voucher_products WHERE product_code = ?', 's', array($code)); };
    $ff = $vp('variant_QA_FFID');
    $gp = $vp('variant_QA_GP100');
    $gplay = $vp('variant_QA_GPLAY');
    TestKit::check('fields parsed: brand, country, denomination, thumbnail, BDT', $gplay['brand'] === 'Google Play' && $gplay['country_code'] === 'us' && $gplay['face_value'] === 'USD 5'
        && $gplay['currency'] === 'BDT' && strpos((string) $gplay['thumbnail_url'], 'https://') === 0 && $gplay['delivery_type'] === 'code' && (int) $gplay['active'] === 0, json_encode($gplay));
    TestKit::check('delivery_schema: game UID and phone-number products need the customer\'s ID', $ff['delivery_type'] === 'player_id' && $ff['player_id_label'] === 'Player ID (UID)'
        && $gp['delivery_type'] === 'player_id' && $gp['player_id_label'] === 'Mobile / Account Number' && strpos((string) $gp['delivery_schema'], 'DestNo') !== false);

    TestKit::mock('/__pv?drop=variant_QA_F010');
    $s3 = VoucherService::syncCatalog(60.0);
    TestKit::check('after a complete pass, a product the provider no longer lists is marked out of stock', $s3['done'] && $s3['delisted'] >= 1 && (int) $vp('variant_QA_F010')['in_stock'] === 0 && (int) $vp('variant_QA_F011')['in_stock'] === 1, json_encode($s3));

    Db::exec("UPDATE voucher_products SET markup_pct = 10.00, sell_price = CEILING(cost_price * 110 / 100), active = 1 WHERE product_code = 'variant_QA_GPLAY'");
    Db::exec("UPDATE voucher_products SET sell_price = cost_price, active = 1 WHERE product_code = 'variant_QA_F020'");
    TestKit::mock('/__pv?bump=20&drop=');
    VoucherService::syncCatalog(60.0);
    TestKit::check('markup product follows the provider cost (520 + 10% = 572)', $vp('variant_QA_GPLAY')['cost_price'] === '520.00' && $vp('variant_QA_GPLAY')['sell_price'] === '572.00', json_encode($vp('variant_QA_GPLAY')));
    $f20 = $vp('variant_QA_F020');
    $inStore = array_map(function ($r) { return (int) $r['id']; }, VoucherService::storefront()['items']);
    TestKit::check('fixed-price product whose cost rose above its price is hidden and flagged', $f20['cost_price'] === '140.00' && $f20['sell_price'] === '120.00' && !in_array((int) $f20['id'], $inStore, true)
        && (int) Db::scalar("SELECT COUNT(*) FROM admin_alerts WHERE alert_key = 'voucher_below_cost' AND resolved = 0") >= 1);
    TestKit::setBalance('qa_l1', 'balance', '5000.00');
    $r = VoucherService::purchase($u['qa_l1'], (int) $f20['id'], bin2hex(random_bytes(16)));
    TestKit::check('below-cost product cannot be bought, nothing debited', !$r['ok'] && TestKit::balance('qa_l1', 'balance') === '5000.00');
    Db::exec("UPDATE voucher_products SET active = 1, sell_price = 10.00 WHERE product_code = 'variant_QA_USD'");
    $r = VoucherService::purchase($u['qa_l1'], (int) $vp('variant_QA_USD')['id'], bin2hex(random_bytes(16)));
    TestKit::check('a product priced in USD is never sold as Taka', !$r['ok'] && $vp('variant_QA_USD')['currency'] === 'USD' && TestKit::balance('qa_l1', 'balance') === '5000.00');

    Db::exec("UPDATE voucher_products SET active = 1, sell_price = cost_price + 5 WHERE product_code IN ('variant_QA_FFID','variant_QA_GP100','variant_QA_OOS','variant_QA_PEND','variant_QA_409')");
    $r = VoucherService::purchase($u['qa_l1'], (int) $gplay['id'], bin2hex(random_bytes(16)));
    $last = TestKit::mock('/__state')['pv_last_order'] ?? null;
    TestKit::check('code product delivered; code from the voucher list, encrypted in the vault', $r['ok'] && $r['status'] === 'delivered' && strpos((string) VoucherService::vault($u['qa_l1'])[0]['voucher_code'], 'LIVECODE-') === 0, json_encode($r));
    TestKit::check('order body: variant_id, quantity 1, our order no as reference + Idempotency-Key', $last && $last['body']['variant_id'] === 'variant_QA_GPLAY' && (int) $last['body']['quantity'] === 1
        && $last['body']['reference'] === $r['order_no'] && $last['idem'] === $r['order_no'], json_encode($last));
    TestKit::eq('Main wallet debited the marked-up price 572', '4428.00', TestKit::balance('qa_l1', 'balance'));
    $r = VoucherService::purchase($u['qa_l1'], (int) $ff['id'], bin2hex(random_bytes(16)), '512345678');
    $last = TestKit::mock('/__state')['pv_last_order'] ?? null;
    TestKit::check('game UID top-up: schema field sent as delivery_data.User, delivered', $r['ok'] && $r['status'] === 'delivered' && isset($last['body']['delivery_data']['User']) && $last['body']['delivery_data']['User'] === '512345678', json_encode($last));
    $r = VoucherService::purchase($u['qa_l1'], (int) $gp['id'], bin2hex(random_bytes(16)), '8801712345678');
    $last = TestKit::mock('/__state')['pv_last_order'] ?? null;
    TestKit::check('mobile recharge product: number sent as delivery_data.DestNo', $r['ok'] && $r['status'] === 'delivered' && ($last['body']['delivery_data']['DestNo'] ?? '') === '8801712345678', json_encode($r));
    $bal = TestKit::balance('qa_l1', 'balance');
    $r = VoucherService::purchase($u['qa_l1'], (int) $gp['id'], bin2hex(random_bytes(16)), '01-ABC');
    TestKit::check('malformed phone number refused before any debit', !$r['ok'] && strpos($r['message'], 'Mobile / Account Number') !== false && TestKit::balance('qa_l1', 'balance') === $bal);
    $r = VoucherService::purchase($u['qa_l1'], (int) $ff['id'], bin2hex(random_bytes(16)), '000000');
    TestKit::check('provider says invalid player -> refunded at once', !$r['ok'] && $r['status'] === 'refunded' && TestKit::balance('qa_l1', 'balance') === $bal, json_encode($r));
    $r = VoucherService::purchase($u['qa_l1'], (int) $vp('variant_QA_OOS')['id'], bin2hex(random_bytes(16)));
    $o = Db::one('SELECT * FROM voucher_orders WHERE order_no = ?', 's', array($r['order_no']));
    TestKit::check('HTTP 422 (order not created) -> refunded with the provider reason', !$r['ok'] && $r['status'] === 'refunded' && TestKit::balance('qa_l1', 'balance') === $bal && strpos((string) $o['failure_reason'], 'out of stock') !== false, json_encode($o));
    $r = VoucherService::purchase($u['qa_l1'], (int) $vp('variant_QA_409')['id'], bin2hex(random_bytes(16)));
    TestKit::check('HTTP 409 (order may exist) -> stays processing, money held, no refund', $r['ok'] && $r['status'] === 'processing' && TestKit::balance('qa_l1', 'balance') === Money::sub($bal, '305.00'), json_encode($r));
    $bal = TestKit::balance('qa_l1', 'balance');
    $r = VoucherService::purchase($u['qa_l1'], (int) $vp('variant_QA_PEND')['id'], bin2hex(random_bytes(16)));
    $o = Db::one('SELECT * FROM voucher_orders WHERE order_no = ?', 's', array($r['order_no']));
    TestKit::check('pending order: the order-level "code" is NOT taken as a voucher code', $r['status'] === 'processing' && $o['voucher_code'] === null && strpos((string) $o['provider_order_id'], 'ord_PEND_') === 0, json_encode($o));
    Db::exec('UPDATE voucher_orders SET created_at = NOW() - INTERVAL 5 MINUTE, status_checked_at = NULL WHERE id = ?', 'i', array((int) $o['id']));
    VoucherService::poll(50);
    $vault = VoucherService::vault($u['qa_l1']);
    $pend = null;
    foreach ($vault as $v) { if ($v['order_no'] === $r['order_no']) { $pend = $v; } }
    TestKit::check('status poller delivers it with the real voucher code', $pend && $pend['status'] === 'delivered' && strpos((string) $pend['voucher_code'], 'PENDCODE-') === 0 && TestKit::balance('qa_l1', 'balance') === Money::sub($bal, '905.00'), json_encode($pend));

    // LIVE store + SANDBOX key = customers would pay real money for test vouchers
    TestKit::configure('popular_voucher', array('mode' => 'live', 'live_base_url' => TestKit::MOCK . '/voucher', 'live_credentials' => \Midhub\Crypto::encryptJson(array('api_key' => 'qa_voucher_key'))));
    TestKit::mock('/__pv?env=sandbox');
    $t = PopularVoucherService::load()->testConnection();
    TestKit::check('test connection flags LIVE mode with a SANDBOX key', !$t['ok'] && strpos($t['message'], 'BLOCKED') !== false, $t['message']);
    $bal = TestKit::balance('qa_l1', 'balance');
    $r = VoucherService::purchase($u['qa_l1'], (int) $gplay['id'], bin2hex(random_bytes(16)));
    TestKit::check('...and no order is placed, nothing debited', !$r['ok'] && TestKit::balance('qa_l1', 'balance') === $bal, json_encode($r));
    TestKit::mock('/__pv?env=live');
    PopularVoucherService::load()->account();
    $r = VoucherService::purchase($u['qa_l1'], (int) $gplay['id'], bin2hex(random_bytes(16)));
    TestKit::check('with a live key the live store sells again', $r['ok'] && $r['status'] === 'delivered', json_encode($r));
    TestKit::configure('popular_voucher', array('mode' => 'sandbox'));
    TestKit::mock('/__pv?count=8&bump=0&drop=&env=sandbox');

    // =====================================================================
    TestKit::section('5-level recharge commission (exactly once, success only)');
    TestKit::configure('paywell', array('status' => 'active'));
    \Midhub\Commission\CommissionService::save(array(0 => '1.00', 1 => '0.50', 2 => '0.30', 3 => '0.20', 4 => '0.10'), true, 'qa_admin');
    foreach (array('qa_l0', 'qa_l1', 'qa_l2', 'qa_l3', 'qa_l4') as $q) { TestKit::setBalance($q, 'balance2', '0.00'); }
    TestKit::setBalance('qa_l0', 'balance2', '5000.00');
    $cr = RechargeService::submit($u['qa_l0'], 0, '01712111111', '11', '1', '0.01');
    $pay = array();
    foreach (array('qa_l0', 'qa_l1', 'qa_l2', 'qa_l3', 'qa_l4') as $q) {
        $pay[$q] = (string) Db::scalar("SELECT COALESCE(SUM(amount),0) FROM commission_payouts WHERE source_id = ? AND beneficiary_id = ?", 'ii', array($cr['id'], $u[$q]));
    }
    TestKit::eq('11 Tk recharge pays 1% / 0.5% / 0.3% / 0.2% / 0.1% up the chain (half-up)', array('qa_l0' => '0.11', 'qa_l1' => '0.06', 'qa_l2' => '0.03', 'qa_l3' => '0.02', 'qa_l4' => '0.01'), $pay);
    TestKit::eq('requester: 5000 - 11 + 0.11 commission', '4989.11', TestKit::balance('qa_l0', 'balance2'));
    TestKit::eq('upline L1 Recharge wallet credited', '0.06', TestKit::balance('qa_l1', 'balance2'));
    TestKit::eq('calling it again pays nothing (unique payout key)', 0, \Midhub\Commission\CommissionService::payForRecharge((int) $cr['id']));
    $fr = RechargeService::submit($u['qa_l0'], 0, '01912111113', '13', '1', '0.01');
    TestKit::eq('rejected (refunded) recharge earns no commission', 0, (int) Db::scalar('SELECT COUNT(*) FROM commission_payouts WHERE source_id = ?', 'i', array($fr['id'])));
    \Midhub\Commission\CommissionService::save(array(0 => '1.00', 1 => '0.50', 2 => '0.30', 3 => '0.20', 4 => '0.10'), false, 'qa_admin');
    $cr2 = RechargeService::submit($u['qa_l0'], 0, '01712111112', '11', '1', '0.01');
    TestKit::eq('switched OFF: no commission', 0, (int) Db::scalar('SELECT COUNT(*) FROM commission_payouts WHERE source_id = ?', 'i', array($cr2['id'])));

    // =====================================================================
    TestKit::section('Upline -> downline transfer: RBAC + no money creation');
    TestKit::setBalance('qa_l1', 'balance2', '100.00');
    TestKit::setBalance('qa_l0', 'balance2', '0.00');
    $t = \Midhub\Wallet\TransferService::toDownline($u['qa_l1'], $u['qa_l0'], 'balance2', '40', 'qa');
    TestKit::check('transfer to own direct downline works', $t['to_after'] === '40.00' && TestKit::balance('qa_l1', 'balance2') === '60.00');
    $msg = '';
    foreach (array($u['qa_l2'], $u['qa_l3'], $u['qa_admin']) as $target) {
        try { \Midhub\Wallet\TransferService::toDownline($u['qa_l1'], $target, 'balance2', '1', 'qa'); $msg .= 'ALLOWED '; } catch (\DomainException $e) { $msg .= 'blocked '; }
    }
    TestKit::eq('transfer to an upline / non-downline / admin is blocked', 'blocked blocked blocked ', $msg);
    TestKit::setBalance('qa_l1', 'balance2', '100.00');
    TestKit::setBalance('qa_l0', 'balance2', '0.00');
    $out = TestKit::parallel(10, array('transfer', (string) $u['qa_l1'], (string) $u['qa_l0'], '30'));
    TestKit::eq('10 parallel transfers of 30 from 100: exactly 3 succeed', 3, count(array_filter($out, function ($l) { return $l === 'OK'; })));
    TestKit::check('no money created: sender 10.00 + receiver 90.00', TestKit::balance('qa_l1', 'balance2') === '10.00' && TestKit::balance('qa_l0', 'balance2') === '90.00', implode('|', $out));

    // =====================================================================
    TestKit::section('Circuit breaker: failing provider is paused, money routed to the manual queue');
    TestKit::configure('paywell', array('sandbox_base_url' => 'http://127.0.0.1:9'));
    TestKit::setBalance('qa_l0', 'balance2', '1000.00');
    $st = array();
    for ($i = 0; $i < 5; $i++) {
        $r = RechargeService::submit($u['qa_l0'], 0, '0171300000' . $i, '20', '1', '0.01');
        $st[] = Db::scalar('SELECT status FROM send_query WHERE id = ?', 'i', array($r['id']));
    }
    TestKit::eq('unreachable provider: each recharge refunded (never sent)', array('3', '3', '3', '3', '3'), $st);
    TestKit::check('breaker opened after 5 consecutive failures + alert', \Midhub\Ops\CircuitBreaker::isOpen('paywell') && (int) Db::scalar("SELECT COUNT(*) FROM admin_alerts WHERE alert_key = 'breaker:paywell'") >= 1);
    $r = RechargeService::submit($u['qa_l0'], 0, '01713000009', '20', '1', '0.01');
    TestKit::check('while paused, the next recharge goes to the manual queue (status 0), not to Paywell', $r['ok'] && Db::scalar('SELECT status FROM send_query WHERE id = ?', 'i', array($r['id'])) === '0' && Db::scalar('SELECT gateway FROM send_query WHERE id = ?', 'i', array($r['id'])) === null);
    \Midhub\Ops\CircuitBreaker::reset('paywell');
    TestKit::configure('paywell', array('sandbox_base_url' => TestKit::MOCK . '/paywell'));

    // =====================================================================
    TestKit::section('Utility bill pay: PIN, draft, debit with service charge, manual & API settlement');
    TestKit::configure('billpay', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => TestKit::MOCK . '/billpay', 'creds' => array('token' => 'qa_bill_token'),
        'extra' => array('payment_mode' => 'manual')));
    TestKit::setBalance('qa_l2', 'bkash_balance', '5000.00');
    $form = array('company' => 'DESCO', 'company_type' => 'Postpaid', 'type' => 'Electricity', 'number' => '1234567', 'contact_number' => '01711111111', 'cs_name' => 'QA', 'month' => 'September 2026', 'pincode' => '111111');
    $_SESSION = array();
    $f = \Midhub\BillPay\BillPayment::fetch($u['qa_l2'], $form);
    TestKit::check('wrong PIN at fetch is refused', $f['result'] === 'error' && strpos($f['message'], 'PIN') !== false, json_encode($f));
    Db::exec('DELETE FROM security_pin_attempts WHERE user_id = ?', 'i', array($u['qa_l2']));
    $form['pincode'] = TestKit::PIN;
    $f = \Midhub\BillPay\BillPayment::fetch($u['qa_l2'], $form);
    $bpid = isset($f['bill_ref']['bill_payment_id']) ? $f['bill_ref']['bill_payment_id'] : '';
    $draft = Db::one('SELECT * FROM billpay_request WHERE bill_payment_id = ? AND user_id = ?', 'si', array($bpid, $u['qa_l2']));
    TestKit::check('fetched bill stored as a DRAFT (status 9) holding no money', $f['result'] === 'success' && $draft && $draft['status'] === '9' && TestKit::balance('qa_l2', 'bkash_balance') === '5000.00', json_encode($f));
    $last = end($f['invoice']);
    TestKit::check('invoice shows service charge 15 and wallet total 1535 (slot 1501-5000)', $f['midhub']['charge'] === '15.00' && $last['value'] === '1535.00' && !empty($last['is_total_row']));
    $saveAuth = $_SESSION['bp_auth'];
    $_SESSION['bp_auth'] = array();
    $p = \Midhub\BillPay\BillPayment::pay($u['qa_l2'], $bpid);
    TestKit::check('pay without a PIN-verified fetch in this session is refused', $p['result'] === 'error');
    $_SESSION['bp_auth'] = $saveAuth;
    $p = \Midhub\BillPay\BillPayment::pay($u['qa_l2'], $bpid);
    $row = Db::one('SELECT * FROM billpay_request WHERE id = ?', 'i', array($draft['id']));
    TestKit::check('manual mode: wallet debited 1535, bill queued for admin (status 0), NOT marked paid', $p['result'] === 'success' && TestKit::balance('qa_l2', 'bkash_balance') === '3465.00' && $row['status'] === '0' && $row['is_bill_paid'] !== 'Y' && $row['cost'] === '1535.00', json_encode($p));
    $_SESSION['bp_auth'][$bpid] = time();
    $p2 = \Midhub\BillPay\BillPayment::pay($u['qa_l2'], $bpid);
    TestKit::check('paying the same bill twice is refused, no second debit', $p2['result'] === 'error' && TestKit::balance('qa_l2', 'bkash_balance') === '3465.00');
    $f = \Midhub\BillPay\BillPayment::fetch($u['qa_l2'], array_merge($form, array('number' => '9999999')));
    TestKit::check('bill already paid at the biller is not payable', $f['result'] === 'error' && strpos($f['message'], 'already paid') !== false);

    TestKit::configure('billpay', array('extra' => array('payment_mode' => 'api')));
    $f = \Midhub\BillPay\BillPayment::fetch($u['qa_l2'], $form);
    $p = \Midhub\BillPay\BillPayment::pay($u['qa_l2'], $f['bill_ref']['bill_payment_id']);
    $row = Db::one('SELECT * FROM billpay_request WHERE bill_payment_id = ? AND user_id = ?', 'si', array($f['bill_ref']['bill_payment_id'], $u['qa_l2']));
    TestKit::check('API mode: paid at the aggregator -> status 1, is_bill_paid Y, biller trx id, receipt link', $p['result'] === 'success' && $row['status'] === '1' && $row['is_bill_paid'] === 'Y' && strpos($row['txnid'], 'BILLTRX') === 0 && strpos((string) $p['receipt_url'], 'bill_receipt.php?id=') === 0, json_encode($p));
    $receiptId = (int) $row['id'];
    $f = \Midhub\BillPay\BillPayment::fetch($u['qa_l2'], array_merge($form, array('number' => '7654321')));
    $before = TestKit::balance('qa_l2', 'bkash_balance');
    $p = \Midhub\BillPay\BillPayment::pay($u['qa_l2'], $f['bill_ref']['bill_payment_id']);
    TestKit::check('aggregator rejects -> status 3 and full refund (bill + charge)', $p['result'] === 'error' && TestKit::balance('qa_l2', 'bkash_balance') === $before && Db::scalar('SELECT status FROM billpay_request WHERE bill_payment_id = ? AND user_id = ?', 'si', array($f['bill_ref']['bill_payment_id'], $u['qa_l2'])) === '3', $p['message'] . ' ' . $before . '/' . TestKit::balance('qa_l2', 'bkash_balance'));
    $f = \Midhub\BillPay\BillPayment::fetch($u['qa_l2'], array_merge($form, array('number' => '5555555')));
    \Midhub\BillPay\BillPayment::pay($u['qa_l2'], $f['bill_ref']['bill_payment_id']);
    $pid = (int) Db::scalar('SELECT id FROM billpay_request WHERE bill_payment_id = ? AND user_id = ?', 'si', array($f['bill_ref']['bill_payment_id'], $u['qa_l2']));
    TestKit::eq('aggregator pending -> processing (6), money held', '6', Db::scalar('SELECT status FROM billpay_request WHERE id = ?', 'i', array($pid)));
    Db::exec('UPDATE billpay_request SET submitted_date = NOW() - INTERVAL 5 MINUTE, checked_at = NULL WHERE id = ?', 'i', array($pid));
    \Midhub\BillPay\BillPayment::poll(30);
    TestKit::eq('status poller completes the processing bill', '1', Db::scalar('SELECT status FROM billpay_request WHERE id = ?', 'i', array($pid)));
    $pdf = (new \Midhub\Pdf\SimplePdf())->text(20, 20, 'QA receipt (ok)')->output();
    TestKit::check('PDF writer produces a valid PDF document', strpos($pdf, '%PDF-1.4') === 0 && strpos($pdf, '%%EOF') !== false && strpos($pdf, 'QA receipt \(ok\)') !== false);
    TestKit::check('DPDC, Akash DTH and internet billers are in the catalog', \Midhub\BillPay\BillerCatalog::resolve('DPDC', 'Prepaid')['slug'] === 'dpdc-prepaid' && \Midhub\BillPay\BillerCatalog::companies('Cable TV') === array('Akash DTH') && count(\Midhub\BillPay\BillerCatalog::companies('Internet')) === 3);
    TestKit::eq('legacy biller mapping preserved (BPDB always uses the prepaid meter endpoint)', 'bpdb-prepaid', \Midhub\BillPay\BillerCatalog::resolve('BPDB', 'Postpaid')['slug']);

    // =====================================================================
    TestKit::section('SIM modem gateway: bearer tokens, atomic claim, settle once');
    TestKit::configure('paywell', array('status' => 'inactive'));
    list($devId, $token) = \Midhub\Modem\ModemGateway::createDevice('QA Modem 1', 'grameenphone, robi', 'qa_admin');
    TestKit::check('device authenticates with its bearer token', \Midhub\Modem\ModemGateway::authenticate($token) !== null);
    TestKit::check('wrong token / legacy SQL-injection key rejected', \Midhub\Modem\ModemGateway::authenticate('mdm_' . str_repeat('0', 48)) === null && \Midhub\Modem\ModemGateway::authenticate("' OR id=1 -- ") === null && \Midhub\Modem\ModemGateway::authenticate('6eb769ff9d8038c6f90d7026d2c23425') === null);
    TestKit::check('only the token hash is stored', (int) Db::scalar('SELECT COUNT(*) FROM modem_devices WHERE id = ? AND token_hash = ?', 'is', array($devId, hash('sha256', $token))) === 1);
    TestKit::setBalance('qa_l0', 'balance2', '1000.00');
    Db::exec("UPDATE send_query SET status = '1' WHERE status = '0' AND operator = 'grameenphone' AND user_id IN (" . implode(',', array_map('intval', $u)) . ')');
    $q1 = RechargeService::submit($u['qa_l0'], 0, '01755000001', '50', '1', '0.01');
    $q2 = RechargeService::submit($u['qa_l0'], 0, '01755000002', '60', '1', '0.01');
    $q3 = RechargeService::submit($u['qa_l0'], 0, '01755000003', '70', '1', '0.01');
    $dev = Db::one('SELECT * FROM modem_devices WHERE id = ?', 'i', array($devId));
    TestKit::eq('device not serving teletalk gets nothing', null, \Midhub\Modem\ModemGateway::claimNext($dev, 'teletalk'));
    $out = TestKit::parallel(6, array('modem_claim', (string) $devId, 'grameenphone'));
    $ids = array();
    foreach ($out as $l) { if (strpos($l, 'OK ') === 0) { $ids[] = (int) substr($l, 3); } }
    sort($ids);
    $mine = array((int) $q1['id'], (int) $q2['id'], (int) $q3['id']);
    TestKit::check('6 phones racing for 3 jobs: each job handed out exactly once', array_values(array_intersect($ids, $mine)) == $mine && count($ids) === count(array_unique($ids)), implode('|', $out));
    \Midhub\Commission\CommissionService::save(array(0 => '1.00', 1 => '0', 2 => '0', 3 => '0', 4 => '0'), true, 'qa_admin');
    TestKit::eq('report success by id', 'ok', \Midhub\Modem\ModemGateway::success($dev, (int) $q1['id'], '', '', 'GPTRX1', 'Recharge 50 Tk successful'));
    TestKit::check('success settled, commission paid (1% of 50)', Db::scalar('SELECT status FROM send_query WHERE id = ?', 'i', array($q1['id'])) === '1' && (string) Db::scalar('SELECT amount FROM commission_payouts WHERE source_id = ?', 'i', array($q1['id'])) === '0.50');
    TestKit::eq('reporting the same success twice changes nothing', 'not_found', \Midhub\Modem\ModemGateway::success($dev, (int) $q1['id'], '', '', 'GPTRX1', ''));
    $bal = TestKit::balance('qa_l0', 'balance2');
    TestKit::eq('report failure by number+amount (legacy app format)', 'ok', \Midhub\Modem\ModemGateway::failed($dev, null, '1755000002', '60', 'SIM balance low'));
    TestKit::eq('failed recharge refunded exactly once', \Midhub\Money::add($bal, '60.00'), TestKit::balance('qa_l0', 'balance2'));
    TestKit::eq('second failure report is ignored', 'not_found', \Midhub\Modem\ModemGateway::failed($dev, null, '01755000002', '60', 'again'));
    Db::exec('UPDATE modem_devices SET active = 0 WHERE id = ?', 'i', array($devId));
    TestKit::check('disabled device is locked out', \Midhub\Modem\ModemGateway::authenticate($token) === null);
    \Midhub\Commission\CommissionService::save(array(0 => '0', 1 => '0', 2 => '0', 3 => '0', 4 => '0'), false, 'qa_admin');

    // =====================================================================
    TestKit::section('Game top-up to Player ID + pay directly with bKash');
    TestKit::configure('popular_voucher', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => ''));
    VoucherService::syncCatalog();
    Db::exec("UPDATE voucher_products SET active = 1, sell_price = 95.00 WHERE product_code = 'FF-UID-100'");
    $fid = (int) Db::scalar("SELECT id FROM voucher_products WHERE product_code = 'FF-UID-100'");
    TestKit::eq('direct top-up product detected', 'player_id', Db::scalar('SELECT delivery_type FROM voucher_products WHERE id = ?', 'i', array($fid)));
    TestKit::setBalance('qa_l1', 'balance', '500.00');
    $r = VoucherService::purchase($u['qa_l1'], $fid, bin2hex(random_bytes(16)), 'abc');
    TestKit::check('malformed Player ID refused before any debit', !$r['ok'] && TestKit::balance('qa_l1', 'balance') === '500.00');
    $r = VoucherService::purchase($u['qa_l1'], $fid, bin2hex(random_bytes(16)), '000000');
    TestKit::check('wrong Player ID rejected by provider -> instant refund', !$r['ok'] && $r['status'] === 'refunded' && TestKit::balance('qa_l1', 'balance') === '500.00', $r['message']);
    $r = VoucherService::purchase($u['qa_l1'], $fid, bin2hex(random_bytes(16)), '512345678');
    TestKit::check('valid Player ID: delivered directly, no code needed', $r['ok'] && $r['status'] === 'delivered' && TestKit::balance('qa_l1', 'balance') === '405.00' && strpos($r['message'], '512345678') !== false, $r['message']);
    TestKit::configure('bkash', array('status' => 'active', 'mode' => 'sandbox', 'charge_percentage' => '0'));
    TestKit::setBalance('qa_l1', 'balance', '0.00');
    $d = DepositService::create($u['qa_l1'], 'bkash', 'balance', '95', bin2hex(random_bytes(16)), array('type' => 'voucher', 'product_id' => $fid, 'player_id' => '598765432'));
    $txn = DepositService::find($d['invoice_no']);
    $r = DepositService::settle($d['invoice_no'], 'return', array('paymentID' => $txn['gateway_payment_id'], 'status' => 'success'));
    $ir = DepositService::runIntent($d['invoice_no']);
    $order = Db::one('SELECT * FROM voucher_orders WHERE deposit_invoice = ?', 's', array($d['invoice_no']));
    TestKit::check('bKash payment -> credit -> top-up delivered to the Player ID automatically', $r['status'] === 'completed' && $ir && $ir['ok'] && $order && $order['status'] === 'delivered' && $order['player_id'] === '598765432', json_encode($ir));
    TestKit::eq('wallet nets to zero (credited by bKash, spent on the top-up)', '0.00', TestKit::balance('qa_l1', 'balance'));
    TestKit::eq('running the intent again never buys twice', null, DepositService::runIntent($d['invoice_no']));
    TestKit::eq('exactly one order for that payment', 1, (int) Db::scalar('SELECT COUNT(*) FROM voucher_orders WHERE deposit_invoice = ?', 's', array($d['invoice_no'])));

    // =====================================================================
    TestKit::section('PIN brute force: temporary IP ban across accounts');
    $_SERVER['REMOTE_ADDR'] = '10.99.99.' . mt_rand(1, 250);
    $accounts = array('qa_l0', 'qa_l1', 'qa_l2', 'qa_l3', 'qa_l4');
    foreach ($accounts as $q) { \Midhub\Security\PinVerifier::verify($u[$q], '000000'); }
    $v = \Midhub\Security\PinVerifier::verify($u['qa_l0'], TestKit::PIN);
    TestKit::check('5 wrong PINs on 5 different accounts from one IP -> even the right PIN is refused', !$v['ok'] && strpos($v['error'], 'from your network') !== false, $v['error']);
    $_SERVER['REMOTE_ADDR'] = '10.98.98.' . mt_rand(1, 250);
    TestKit::check('another IP is unaffected', \Midhub\Security\PinVerifier::verify($u['qa_l0'], TestKit::PIN)['ok']);
    unset($_SERVER['REMOTE_ADDR']);

    // =====================================================================
    TestKit::section('Low-balance monitor & migrations');
    TestKit::eq('Paywell balance read from provider', '15000.50', (new PaywellService(GatewayConfig::load('paywell', true)))->balance());
    $out = array();
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(MIDHUB_ROOT . '/database/migrate.php'), $out, $code);
    TestKit::check('migrate.php is idempotent (nothing pending)', $code === 0 && strpos(implode("\n", $out), 'Nothing to migrate') !== false, implode(' | ', $out));
    $reran = 0;
    foreach (glob(MIDHUB_ROOT . '/database/migrations/*.php') as $f) {
        $fn = require $f;
        try { $fn(); $reran++; } catch (\Throwable $e) { echo "     re-run " . basename($f) . ": " . $e->getMessage() . "\n"; }
    }
    TestKit::eq('every migration can safely re-run', count(glob(MIDHUB_ROOT . '/database/migrations/*.php')), $reran);

    // The live server failed here: on a FRESH database under MySQL 8 strict mode, migration 003
    // tried to seed gateways added later (billpay, sms) whose category did not exist yet.
    // Local re-runs never noticed because the rows already existed and MariaDB was not strict.
    $scratch = 'qa_migtest_' . getmypid();
    $mode = (string) Db::scalar('SELECT @@SESSION.sql_mode');
    Db::raw("CREATE DATABASE `$scratch` CHARACTER SET utf8mb4");
    try {
        Db::raw("USE `$scratch`");
        Db::raw("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
        $err = '';
        foreach (array('2026_11_24_000003_payment_gateways', '2026_11_25_000012_billpay_and_sms_gateways') as $m) {
            $fn = require MIDHUB_ROOT . "/database/migrations/$m.php";
            try { $fn(); } catch (\Throwable $e) { $err .= "$m: " . $e->getMessage() . ' '; }
        }
        $rows = $err === '' ? (int) Db::scalar('SELECT COUNT(*) FROM payment_gateways') : 0;
        TestKit::check('gateway migrations run on a fresh database in MySQL strict mode (9 gateways)', $err === '' && $rows === count(GatewayRegistry::all()), $err . "rows=$rows");
    } finally {
        Db::raw('USE `' . DB_NAME . '`');
        Db::raw("SET SESSION sql_mode = '" . str_replace("'", '', $mode) . "'");
        Db::raw("DROP DATABASE IF EXISTS `$scratch`");
    }
} catch (\Throwable $e) {
    TestKit::check('suite aborted: ' . get_class($e), false, $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
} finally {
    TestKit::tearDown();
}

echo "\n" . TestKit::$pass . ' passed, ' . TestKit::$fail . " failed\n";
foreach (TestKit::$failures as $f) {
    echo "  - $f\n";
}
exit(TestKit::$fail === 0 ? 0 : 1);
