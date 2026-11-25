<?php
/**
 * HTTP smoke tests against the local web server (XAMPP Apache):  php tests/http_smoke.php
 * Exercises real pages and sessions: access control, CSRF, Self-PIN, admin gateway UI,
 * customer add-money / recharge / voucher flows and the webhook endpoint.
 * Uses only correct passwords (the legacy login throttle blocks an IP after failures).
 */
if (PHP_SAPI !== 'cli') {
    exit;
}
require_once __DIR__ . '/TestKit.php';

use Midhub\Crypto;
use Midhub\Db;

$BASE = getenv('SMOKE_BASE') ?: 'http://localhost/midhub360.com';
$jars = array();

/** @return array{code:int,location:string,body:string} */
function http(string $actor, string $method, string $url, array $post = null, array $headers = array()): array
{
    global $jars, $BASE;
    if (!isset($jars[$actor])) {
        $jars[$actor] = tempnam(sys_get_temp_dir(), 'jar');
    }
    $full = strpos($url, 'http') === 0 ? $url : $BASE . $url;
    $ch = curl_init($full);
    $hdrs = array();
    foreach ($headers as $k => $v) {
        $hdrs[] = "$k: $v";
    }
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $jars[$actor], CURLOPT_COOKIEFILE => $jars[$actor], CURLOPT_HTTPHEADER => $hdrs, CURLOPT_TIMEOUT => 60,
        CURLOPT_CUSTOMREQUEST => $method));
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $resp = (string) curl_exec($ch);
    $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $head = substr($resp, 0, $hs);
    preg_match('/^Location:\s*(.+)$/mi', $head, $m);
    return array('code' => $code, 'location' => isset($m[1]) ? trim($m[1]) : '', 'body' => substr($resp, $hs));
}
function sameOrigin(): array
{
    global $BASE;
    return array('Referer' => $BASE . '/', 'Origin' => preg_replace('#^(https?://[^/]+).*$#', '$1', $BASE));
}
function csrf(string $body): string
{
    return preg_match('/name="_csrf" value="([a-f0-9]{64})"/', $body, $m) ? $m[1] : '';
}
function phpErrors(string $body): string
{
    return preg_match('/(Fatal error|Parse error|Warning<\/b>|Notice<\/b>|Deprecated<\/b>|Uncaught|Query Failed)[^<]{0,160}/', $body, $m) ? strip_tags($m[0]) : '';
}

echo "HTTP smoke tests against $BASE\n";
TestKit::setUp();
try {
    $u = TestKit::$users;
    TestKit::configure('bkash', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => TestKit::MOCK . '/bkash',
        'creds' => array('app_key' => 'qa_bkash_app_key', 'app_secret' => 'qa_bk_secret', 'username' => 'qa_bk_user', 'password' => 'qa_bk_pass')));
    TestKit::configure('stripe', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => TestKit::MOCK . '/stripe',
        'creds' => array('publishable_key' => 'pk_test_qa', 'secret_key' => 'sk_test_qa123', 'webhook_secret' => 'whsec_qa_secret')));
    TestKit::configure('popular_voucher', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => ''));

    // =====================================================================
    TestKit::section('Previously unauthenticated tools are closed');
    foreach (array('/edit.php', '/editbkash.php', '/update.php', '/update_bkash_send.php', '/edit_bkash_send.php', '/fetch.php', '/fetch_bkash_send.php',
                 '/admin/editbkash.php', '/admin/bkashrep.php', '/admin/foreach.php', '/admin/api_status_update_recharge.php', '/admin/json_final.php') as $p) {
        $r = http('anon', 'GET', $p);
        TestKit::eq("anonymous $p -> 403", 403, $r['code']);
    }
    TestKit::eq('debug_login.php -> 404 over HTTP', 404, http('anon', 'GET', '/debug_login.php')['code']);
    TestKit::eq('database/migrate.php -> not runnable over HTTP', true, in_array(http('anon', 'GET', '/database/migrate.php')['code'], array(403, 404), true));
    foreach (array('/lib/bootstrap.php', '/storage/keys/app.key', '/tests/run.php', '/cron/worker.php') as $p) {
        TestKit::check("$p blocked", in_array(http('anon', 'GET', $p)['code'], array(403, 404), true));
    }

    // =====================================================================
    TestKit::section('Privilege escalation: a retailer cannot open the admin panel');
    $r = http('cust', 'POST', '/login.php', array('username' => 'qa_l0', 'password' => TestKit::PASSWORD));
    TestKit::check('retailer login redirects to customer panel', $r['code'] === 302 && strpos($r['location'], 'customer/home.php') !== false, $r['code'] . ' ' . $r['location']);
    $r = http('cust', 'GET', '/admin/home.php');
    TestKit::check('retailer session on /admin/home.php -> bounced to admin login', $r['code'] === 302 && strpos($r['location'], 'index.php') !== false, $r['code'] . ' ' . $r['location'] . ' ' . substr(strip_tags($r['body']), 0, 80));
    $r = http('cust', 'POST', '/admin/route.php?action=balaaddsuccess', array('id' => 1), sameOrigin());
    TestKit::check('retailer cannot call admin actions', $r['code'] === 302 && strpos($r['location'], 'index.php') !== false);

    // =====================================================================
    TestKit::section('Admin panel: pages, CSRF, Self-PIN, gateway settings');
    $r = http('adm', 'POST', '/admin/index.php', array('username' => 'qa_admin', 'password' => TestKit::PASSWORD));
    TestKit::check('super admin login', $r['code'] === 302 && strpos($r['location'], 'home.php') !== false, $r['code'] . ' ' . $r['location']);
    foreach (array('home.php', 'gateway_settings.php', 'gateway_transactions.php', 'gateway_transactions.php?view=recharge', 'voucher_products.php', 'admin_alerts.php',
                 'pending_recharge.php', 'waiting_recharge.php', 'recharge_history.php', 'customer_list.php', 'pending_add_balance.php') as $p) {
        $r = http('adm', 'GET', '/admin/' . $p);
        $err = phpErrors($r['body']);
        TestKit::check("admin/$p renders without PHP errors", $r['code'] === 200 && $err === '', $r['code'] . ' ' . $err);
    }
    $page = http('adm', 'GET', '/admin/gateway_settings.php?tab=paywell');
    $tok = csrf($page['body']);
    TestKit::check('settings page has CSRF token, 7 gateways, copyable webhook URL', $tok !== '' && substr_count($page['body'], 'name="slug"') >= 14 && strpos($page['body'], 'gateway/webhook.php?gw=stripe') !== false);
    TestKit::check('no secret value is rendered in the page', strpos($page['body'], '2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b') === false && strpos($page['body'], 'qa_bk_secret') === false);

    $r = http('adm', 'POST', '/admin/route.php?action=GatewaySave', array('slug' => 'paywell', 'self_pin' => TestKit::PIN), sameOrigin());
    TestKit::eq('save without CSRF token -> 403', 403, $r['code']);
    $r = http('adm', 'POST', '/admin/route.php?action=resuccess', array('id' => 1), array('Origin' => 'https://evil.example', 'Referer' => 'https://evil.example/x'));
    TestKit::eq('cross-site POST to a legacy admin action -> 403', 403, $r['code']);
    $r = http('adm', 'GET', '/admin/route.php?action=recancel&id=1', null, array());
    TestKit::eq('legacy GET action link without Referer (forged) -> 403', 403, $r['code']);

    $save = function (array $fields) use ($tok) {
        $base = array('_csrf' => $tok, 'self_pin' => TestKit::PIN, 'mode' => 'sandbox', 'charge_percentage' => '0', 'min_amount' => '5', 'max_amount' => '10000', 'exchange_rate' => '122');
        $r = http('adm', 'POST', '/admin/route.php?action=GatewaySave', array_merge($base, $fields), sameOrigin());
        $p = http('adm', 'GET', '/admin/' . ($r['location'] ?: 'gateway_settings.php'));
        return strip_tags(preg_match('/<div class="alert alert-(success|danger)[^>]*>(.*?)<button/s', $p['body'], $m) ? $m[1] . ': ' . trim($m[2]) : 'no flash');
    };
    $msg = $save(array('slug' => 'nagad', 'status' => 'active', 'sandbox_base_url' => 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0', 'live_base_url' => 'https://api.mynagad.com', 'min_amount' => '10', 'max_amount' => '25000'));
    TestKit::check('activating Nagad without credentials is refused', strpos($msg, 'danger') === 0 && strpos($msg, 'Cannot activate') !== false, $msg);
    $msg = $save(array('slug' => 'stripe', 'self_pin' => '111111', 'status' => 'active', 'sandbox_base_url' => TestKit::MOCK . '/stripe', 'live_base_url' => 'https://api.stripe.com'));
    TestKit::check('wrong Self-PIN refused', strpos($msg, 'Wrong Self-PIN') !== false, $msg);
    $msg = $save(array('slug' => 'stripe', 'status' => 'active', 'sandbox_base_url' => TestKit::MOCK . '/stripe', 'live_base_url' => 'https://api.stripe.com',
        'cred' => array('live' => array('publishable_key' => 'pk_test_wrongmode'))));
    TestKit::check('a test key in the LIVE slot is refused', strpos($msg, 'must start with pk_live_') !== false, $msg);
    $msg = $save(array('slug' => 'paywell', 'status' => 'active', 'sandbox_base_url' => TestKit::MOCK . '/paywell', 'live_base_url' => 'https://agentapi.paywellonline.com',
        'cred' => array('sandbox' => array('username' => 'qa_user', 'password' => 'qa_pass', 'api_username' => 'qa_api_user', 'api_password' => 'qa_api_pass', 'api_key' => 'qa_api_key_123', 'encryption_key' => 'qa_encryption_key_456')),
        'extra' => array('token_path' => '/Authentication/PaywellAuth/getToken', 'topup_path' => '/Recharge/mobileRecharge/singleTopup', 'enquiry_path' => '/Recharge/mobileRecharge/rechargeEnquiry',
            'balance_path' => '/Retailer/RetailerService/retailerBalance', 'operator_codes' => '{"grameenphone":"GP","robi":"RB","banglalink":"BL","airtel":"AT","teletalk":"TT","skitto":"SK"}',
            'success_codes' => '200', 'pending_codes' => '100,102,202', 'low_balance_threshold' => '20000')));
    TestKit::check('Paywell saved and activated in sandbox', strpos($msg, 'success') === 0, $msg);
    $row = Db::one("SELECT status, mode, sandbox_credentials, updated_by FROM payment_gateways WHERE gateway_slug = 'paywell'");
    TestKit::check('credentials stored encrypted, not plaintext', strpos($row['sandbox_credentials'], 'enc:v1:') === 0 && strpos($row['sandbox_credentials'], 'qa_encryption_key_456') === false);
    TestKit::check('status/mode/updated_by written', $row['status'] === 'active' && $row['mode'] === 'sandbox' && $row['updated_by'] === 'qa_admin');
    $msg = $save(array('slug' => 'paywell', 'status' => 'active', 'sandbox_base_url' => TestKit::MOCK . '/paywell', 'live_base_url' => 'https://agentapi.paywellonline.com',
        'cred' => array('sandbox' => array('username' => 'qa_user', 'api_username' => 'qa_api_user'))));
    $creds = Crypto::decryptJson(Db::scalar("SELECT sandbox_credentials FROM payment_gateways WHERE gateway_slug = 'paywell'"));
    TestKit::check('blank secret fields keep the saved secrets', $creds['encryption_key'] === 'qa_encryption_key_456' && $creds['api_password'] === 'qa_api_pass', $msg);
    $audit = Db::one("SELECT changes FROM gateway_audit_log WHERE gateway_slug = 'paywell' AND admin_user = 'qa_admin' ORDER BY id ASC LIMIT 1");
    TestKit::check('audit log lists field names, never values', $audit && strpos($audit['changes'], 'sandbox.encryption_key') !== false && strpos($audit['changes'], 'qa_encryption_key_456') === false);
    $r = http('adm', 'POST', '/admin/route.php?action=GatewayTest', array('_csrf' => $tok, 'slug' => 'paywell'), sameOrigin());
    $p = http('adm', 'GET', '/admin/gateway_settings.php?tab=paywell');
    TestKit::check('Test connection button works (Paywell token + balance)', strpos($p['body'], 'Token issued') !== false && strpos($p['body'], '15000.50') !== false);

    // =====================================================================
    TestKit::section('Customer: online Add Money via bKash (mock)');
    TestKit::setBalance('qa_l0', 'balance2', '0.00');
    http('cust', 'POST', '/login.php', array('username' => 'qa_l0', 'password' => TestKit::PASSWORD));
    $p = http('cust', 'GET', '/customer/add_money.php');
    TestKit::check('add_money.php lists active gateways', $p['code'] === 200 && strpos($p['body'], 'bKash Tokenized Checkout') !== false && phpErrors($p['body']) === '', phpErrors($p['body']));
    preg_match('/name="nonce" value="([a-f0-9]+)"/', $p['body'], $nm);
    $post = array('_csrf' => csrf($p['body']), 'nonce' => $nm[1], 'wallet' => 'balance2', 'gateway' => 'bkash', 'amount' => '250');
    $r = http('cust', 'POST', '/customer/add_money.php', $post, sameOrigin());
    TestKit::check('submitting redirects to the bKash checkout', $r['code'] === 302 && strpos($r['location'], '/bkash-pay?paymentID=') !== false, $r['code'] . ' ' . $r['location']);
    $r2 = http('cust', 'POST', '/customer/add_money.php', $post, sameOrigin());
    TestKit::eq('double-click resubmit goes to the SAME checkout', $r['location'], $r2['location']);
    parse_str((string) parse_url($r['location'], PHP_URL_QUERY), $q);
    $inv = Db::scalar("SELECT invoice_no FROM gateway_transactions WHERE gateway_payment_id = ?", 's', array($q['paymentID']));
    // bKash appends its parameters; simulate the sloppy "?"-appended variant too
    $ret = '/gateway/return.php?gw=bkash&inv=' . $inv . '?paymentID=' . $q['paymentID'] . '&status=success';
    $r = http('cust', 'GET', $ret);
    TestKit::check('return page: Payment successful (handles "?"-appended params)', strpos($r['body'], 'Payment successful') !== false && strpos($r['body'], '250.00 BDT') !== false, substr(strip_tags($r['body']), 0, 300));
    $r = http('anon', 'GET', $ret);
    TestKit::check('replaying the return URL (another browser) credits nothing, hides amounts', strpos($r['body'], 'Payment successful') !== false && strpos($r['body'], 'Wallet credit') === false);
    TestKit::eq('Recharge wallet credited exactly once', '250.00', TestKit::balance('qa_l0', 'balance2'));

    $p = http('cust', 'GET', '/customer/add_money.php');
    preg_match('/name="nonce" value="([a-f0-9]+)"/', $p['body'], $nm);
    $r = http('cust', 'POST', '/customer/add_money.php', array('_csrf' => csrf($p['body']), 'nonce' => $nm[1], 'wallet' => 'balance2', 'gateway' => 'bkash', 'amount' => '100'), sameOrigin());
    parse_str((string) parse_url($r['location'], PHP_URL_QUERY), $q);
    $mockState = json_decode((string) file_get_contents(sys_get_temp_dir() . '/midhub_mock_state.json'), true);
    TestKit::check('bKash was given the query-free callback URL', substr($mockState['bk'][$q['paymentID']]['callback'], -strlen('/gateway/bkash_callback.php')) === '/gateway/bkash_callback.php', $mockState['bk'][$q['paymentID']]['callback']);
    $r = http('cust', 'GET', '/gateway/bkash_callback.php?paymentID=' . $q['paymentID'] . '&status=success&apiVersion=1.2.0-beta');
    TestKit::check('bkash_callback.php resolves the invoice from paymentID and settles', strpos($r['body'], 'Payment successful') !== false, substr(strip_tags($r['body']), 0, 200));
    TestKit::eq('second deposit credited (250 + 100)', '350.00', TestKit::balance('qa_l0', 'balance2'));
    $r = http('cust', 'GET', '/gateway/bkash_callback.php?paymentID=TR00notours&status=success');
    TestKit::check('unknown paymentID on the callback -> not found, nothing credited', strpos($r['body'], 'Something went wrong') !== false && TestKit::balance('qa_l0', 'balance2') === '350.00');

    // =====================================================================
    TestKit::section('Customer: recharge through the legacy form -> Paywell (mock)');
    TestKit::setBalance('qa_l0', 'balance2', '500.00');
    $r = http('cust', 'POST', '/customer/route.php?action=requestSend', array('number' => '01812345678', 'type' => '1', 'amount' => '11', 'pincode' => TestKit::PIN), sameOrigin());
    $row = Db::one("SELECT status, gateway, txnid FROM send_query WHERE user_id = ? ORDER BY id DESC LIMIT 1", 'i', array($u['qa_l0']));
    TestKit::check('recharge accepted and delivered by Paywell (status 1)', $r['code'] === 302 && $row && $row['status'] === '1' && $row['gateway'] === 'paywell', json_encode($row));
    TestKit::eq('balance2 debited 11', '489.00', TestKit::balance('qa_l0', 'balance2'));
    $p = http('cust', 'GET', '/customer/send_recharge.php');
    TestKit::check('success message shown to customer', strpos($p['body'], 'Recharge successful') !== false);
    $r = http('cust', 'POST', '/customer/route.php?action=requestSend', array('number' => '01812345679', 'type' => '1', 'amount' => '11', 'pincode' => TestKit::PIN));
    TestKit::eq('recharge POST without same-origin headers (CSRF) -> 403', 403, $r['code']);
    $r = http('cust', 'POST', '/customer/route.php?action=requestSend', array('number' => '01812345670', 'type' => '1', 'amount' => '11', 'pincode' => '999999'), sameOrigin());
    TestKit::eq('wrong PIN: nothing debited', '489.00', TestKit::balance('qa_l0', 'balance2'));

    // =====================================================================
    TestKit::section('Customer: digital vouchers');
    \Midhub\Voucher\VoucherService::syncCatalog();
    Db::exec("UPDATE voucher_products SET active = 1, sell_price = 95.00 WHERE product_code = 'PUBG-60'");
    $pid = (int) Db::scalar("SELECT id FROM voucher_products WHERE product_code = 'PUBG-60'");
    TestKit::setBalance('qa_l0', 'balance', '300.00');
    $p = http('cust', 'GET', '/customer/vouchers.php');
    TestKit::check('vouchers page renders product', $p['code'] === 200 && strpos($p['body'], 'PUBG Mobile 60 UC') !== false && phpErrors($p['body']) === '', phpErrors($p['body']));
    preg_match('/name="nonce" value="([a-f0-9]+)"/', $p['body'], $nm);
    http('cust', 'POST', '/customer/vouchers.php', array('_csrf' => csrf($p['body']), 'nonce' => $nm[1], 'product_id' => $pid, 'pincode' => TestKit::PIN), sameOrigin());
    $p = http('cust', 'GET', '/customer/vouchers.php');
    TestKit::check('voucher delivered into My Vouchers with TEST code', strpos($p['body'], 'Purchase successful') !== false && preg_match('/TEST-[0-9A-F]{8}-[0-9A-F]{8}/', $p['body']));
    TestKit::eq('Main wallet debited 95', '205.00', TestKit::balance('qa_l0', 'balance'));
    $tokV = csrf($p['body']);
    $last = '';
    for ($i = 0; $i < 6; $i++) {
        preg_match('/name="nonce" value="([a-f0-9]+)"/', $p['body'], $nm);
        http('cust', 'POST', '/customer/vouchers.php', array('_csrf' => $tokV, 'nonce' => $nm[1], 'product_id' => $pid, 'pincode' => $i < 5 ? '000000' : TestKit::PIN), sameOrigin());
        $p = http('cust', 'GET', '/customer/vouchers.php');
        $last = preg_match('/alert-danger[^>]*>(.*?)<button/s', $p['body'], $m) ? trim(strip_tags($m[1])) : '';
    }
    TestKit::check('5 wrong PINs lock purchases even with the right PIN', strpos($last, 'Too many wrong PIN attempts') !== false && TestKit::balance('qa_l0', 'balance') === '205.00', $last);

    // =====================================================================
    TestKit::section('Admin manual queue: a request is settled only once');
    TestKit::configure('paywell', array('status' => 'inactive'));
    TestKit::setBalance('qa_l3', 'balance2', '400.00');
    $q = \Midhub\Recharge\RechargeService::submit($u['qa_l3'], 3, '01912340000', '100', '1', '0.01');
    TestKit::eq('manual-queue recharge debited', '300.00', TestKit::balance('qa_l3', 'balance2'));
    $cancel = '/admin/route.php?action=recancel&id=' . $q['id'] . '&number=01912340000&amount=100&remsg=qa%20cancel&ucid=' . $u['qa_l3'] . '&company=banglalink&sttype=pending';
    $r1 = http('adm', 'GET', $cancel, null, sameOrigin());
    $r2 = http('adm', 'GET', $cancel, null, sameOrigin());
    TestKit::eq('admin double-click Cancel refunds exactly once', '400.00', TestKit::balance('qa_l3', 'balance2'));
    $p = http('adm', 'GET', '/admin/pending_recharge.php');
    TestKit::check('second click reports "already processed"', strpos($p['body'], 'already processed') !== false);
    http('adm', 'GET', '/admin/route.php?action=resuccess&id=' . $q['id'] . '&number=01912340000&amount=100&txnid=QA1&msg=ok&ucid=' . $u['qa_l3'] . '&company=banglalink', null, sameOrigin());
    TestKit::eq('a cancelled (refunded) request cannot then be marked successful', '3', Db::scalar('SELECT status FROM send_query WHERE id = ?', 'i', array($q['id'])));

    // =====================================================================
    TestKit::section('Reseller HTTP API: injection, auth, double-spend');
    Db::exec("UPDATE user SET api_status = '1', api_key = 'qaapikey123456' WHERE id = ?", 'i', array($u['qa_l0']));
    Db::exec("UPDATE user SET api_status = '1', api_key = 'qaotherkey987654' WHERE id = ?", 'i', array($u['qa_l1']));
    TestKit::setBalance('qa_l0', 'balance2', '300.00');
    $r = http('anon', 'GET', '/api/balance.php?user=qa_l0&key=qaapikey123456');
    TestKit::check('valid API key accepted', strpos($r['body'], '300') !== false, substr($r['body'], 0, 120));
    foreach (array("x' OR (id=" . $u['qa_l0'] . " AND api_status='1') -- -", "qa_l0' -- -") as $inj) {
        $r = http('anon', 'GET', '/api/balance.php?user=' . rawurlencode($inj) . '&key=anything');
        TestKit::check('SQL injection in user is rejected: ' . substr($inj, 0, 18), strpos($r['body'], 'INVALID REQUEST') !== false && strpos($r['body'], '300') === false, substr($r['body'], 0, 120));
    }
    $r = http('anon', 'GET', '/api/balance.php?user=qa_l0&key=wrongkey12345');
    TestKit::check('wrong API key rejected', strpos($r['body'], '300') === false);
    $mh = curl_multi_init();
    $handles = array();
    for ($i = 0; $i < 8; $i++) {
        $ch = curl_init($BASE . '/api/recharge.php?user=qa_l0&key=qaapikey123456&number=0171990000' . $i . '&amount=100&type=1&rid=' . (900000 + $i + mt_rand(0, 90000) * 10));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($mh, $ch);
        $handles[] = $ch;
    }
    do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);
    $okApi = 0;
    foreach ($handles as $ch) {
        if (strpos((string) curl_multi_getcontent($ch), 'SUCCESS') !== false) { $okApi++; }
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);
    TestKit::eq('8 parallel API recharges of 100 from 300: exactly 3 accepted', 3, $okApi);
    TestKit::eq('API balance never goes negative (0.00)', '0.00', TestKit::balance('qa_l0', 'balance2'));
    $rid = (int) Db::scalar("SELECT api_reid FROM send_query WHERE user_id = ? AND rfrom = 'API' ORDER BY id DESC LIMIT 1", 'i', array($u['qa_l0']));
    $r = http('anon', 'GET', '/api/recharge_status.php?user=qa_l1&key=qaotherkey987654&rid=' . $rid);
    TestKit::check("another reseller cannot read qa_l0's recharge status", strpos($r['body'], '0171990000') === false, substr($r['body'], 0, 160));

    // =====================================================================
    TestKit::section('Customer portal on mobile: bill pages, storefront search, add money');
    foreach (array('/customer/bill_tv.php' => 'Akash DTH', '/customer/bill_internet.php' => 'Link3', '/customer/bill_electricity.php' => 'DPDC', '/customer/add_money.php' => 'Add Money', '/customer/send_bill.php' => 'bill_tv.php') as $path => $needle) {
        $p = http('cust', 'GET', $path);
        TestKit::check("$path renders (viewport meta, no PHP errors)", $p['code'] === 200 && phpErrors($p['body']) === '' && stripos($p['body'], $needle) !== false && strpos($p['body'], 'name="viewport"') !== false,
            $p['code'] . ' ' . phpErrors($p['body']) . ' ' . $p['location']);
    }
    $p = http('cust', 'GET', '/customer/add_money.php');
    TestKit::check('Add Money shows the USD rate actually credited (122 - 2 margin = 120), same as the server', strpos($p['body'], '1 USD = 120.00 BDT') !== false && strpos($p['body'], 'data-rate="120.0000"') !== false);
    $p = http('cust', 'GET', '/customer/vouchers.php?q=PUBG');
    TestKit::check('storefront search finds the product and offers category chips', $p['code'] === 200 && strpos($p['body'], 'PUBG Mobile 60 UC') !== false && strpos($p['body'], 'aria-label="Search vouchers"') !== false && phpErrors($p['body']) === '', phpErrors($p['body']));
    $p = http('cust', 'GET', '/customer/vouchers.php?q=' . rawurlencode('<script>x</script>'));
    TestKit::check('storefront search is escaped (no reflected XSS)', $p['code'] === 200 && strpos($p['body'], '<script>x</script>') === false && strpos($p['body'], 'Nothing matches') !== false);

    // =====================================================================
    TestKit::section('Bill pay over HTTP: PDF money receipt for the owner only');
    TestKit::configure('billpay', array('status' => 'active', 'mode' => 'sandbox', 'sandbox_base_url' => TestKit::MOCK . '/billpay', 'creds' => array('token' => 'qa_bill_token'), 'extra' => array('payment_mode' => 'api')));
    TestKit::setBalance('qa_l0', 'bkash_balance', '3000.00');
    $_SESSION = array();
    $bf = \Midhub\BillPay\BillPayment::fetch($u['qa_l0'], array('company' => 'DESCO', 'company_type' => 'Postpaid', 'type' => 'Electricity', 'number' => '1234567', 'contact_number' => '01711111111', 'cs_name' => 'QA', 'month' => 'October 2026', 'pincode' => TestKit::PIN));
    $bp = \Midhub\BillPay\BillPayment::pay($u['qa_l0'], (string) ($bf['bill_ref']['bill_payment_id'] ?? ''));
    $rid = (int) preg_replace('/\D/', '', (string) ($bp['receipt_url'] ?? ''));
    $p = http('cust', 'GET', '/customer/bill_receipt.php?id=' . $rid);
    TestKit::check('paid bill -> PDF receipt download', $rid > 0 && $p['code'] === 200 && strpos($p['body'], '%PDF-1.4') === 0 && strpos($p['body'], '%%EOF') !== false, json_encode($bp) . ' ' . $p['code']);
    http('cust2', 'POST', '/login.php', array('username' => 'qa_l1', 'password' => TestKit::PASSWORD));
    $p = http('cust2', 'GET', '/customer/bill_receipt.php?id=' . $rid);
    TestKit::check('another customer cannot open it (404)', $p['code'] === 404 && strpos($p['body'], '%PDF') === false, (string) $p['code']);
    TestKit::eq('anonymous cannot open it', true, http('anon', 'GET', '/customer/bill_receipt.php?id=' . $rid)['code'] !== 200);

    // =====================================================================
    TestKit::section('SIM modem endpoints over HTTP (bearer token)');
    list($mdmId, $mdmToken) = \Midhub\Modem\ModemGateway::createDevice('QA Smoke Modem', 'teletalk', 'qa_admin');
    $r = http('anon', 'GET', '/apimobile/pending_status.php?operator=teletalk');
    TestKit::eq('no token -> 401', 401, $r['code']);
    $r = http('anon', 'GET', '/apimobile/pending_status.php?operator=teletalk', null, array('Authorization' => 'Bearer mdm_' . str_repeat('a', 48)));
    TestKit::eq('wrong token -> 401', 401, $r['code']);
    $r = http('anon', 'GET', '/apimobile/pending_status.php?operator=teletalk', null, array('Authorization' => 'Bearer ' . $mdmToken));
    TestKit::check('valid token -> JSON (no job queued)', $r['code'] === 200 && strpos($r['body'], 'NOT FOUND') !== false, $r['code'] . ' ' . substr($r['body'], 0, 120));
    foreach (array('/apimobile/autoload.php', '/apimobile/function.php') as $legacy) {
        TestKit::eq("legacy $legacy closed", 404, http('anon', 'GET', $legacy)['code']);
    }

    // =====================================================================
    TestKit::section('Login brute force: 3 wrong passwords lock the account');
    for ($i = 0; $i < 3; $i++) {
        http('brute', 'POST', '/login.php', array('username' => 'qa_l2', 'password' => 'wrong-' . $i));
    }
    $r = http('brute', 'POST', '/login.php', array('username' => 'qa_l2', 'password' => TestKit::PASSWORD));
    $home = http('brute', 'GET', '/customer/home.php');
    TestKit::check('even the right password is refused afterwards', $home['code'] !== 200 || strpos($home['body'], 'logout') === false, $r['code'] . ' ' . $home['code'] . ' ' . substr(strip_tags($r['body']), 0, 120));
    TestKit::check('the user sees a clear lock message', stripos($r['body'], 'blocked') !== false, substr(strip_tags($r['body']), 0, 200));

    // =====================================================================
    TestKit::section('Webhook endpoint');
    TestKit::eq('GET webhook -> 405', 405, http('anon', 'GET', '/gateway/webhook.php?gw=stripe')['code']);
    $r = http('anon', 'POST', '/gateway/webhook.php?gw=stripe', null, array('Stripe-Signature' => 't=1,v1=bad', 'Content-Type' => 'application/json'));
    TestKit::eq('Stripe webhook with bad signature -> 400', 400, $r['code']);
    $d = \Midhub\Payment\DepositService::create($u['qa_l1'], 'stripe', 'balance', '20', bin2hex(random_bytes(16)));
    $txn = \Midhub\Payment\DepositService::find($d['invoice_no']);
    TestKit::mock('/__succeed/stripe?cs=' . $txn['gateway_payment_id']);
    $event = json_encode(array('id' => 'evt_smoke_' . bin2hex(random_bytes(4)), 'type' => 'checkout.session.completed', 'livemode' => false,
        'data' => array('object' => array('id' => $txn['gateway_payment_id'], 'object' => 'checkout.session', 'client_reference_id' => $d['invoice_no'], 'metadata' => array('invoice_no' => $d['invoice_no'])))));
    $ts = time();
    $sig = 't=' . $ts . ',v1=' . hash_hmac('sha256', $ts . '.' . $event, 'whsec_qa_secret');
    $ch = curl_init($BASE . '/gateway/webhook.php?gw=stripe');
    curl_setopt_array($ch, array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $event, CURLOPT_RETURNTRANSFER => 1, CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Stripe-Signature: ' . $sig)));
    $out1 = curl_exec($ch);
    $code1 = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $out2 = curl_exec($ch);
    curl_close($ch);
    TestKit::check('signed Stripe webhook -> 200 completed', $code1 === 200 && strpos((string) $out1, 'completed') !== false, $code1 . ' ' . $out1);
    TestKit::check('redelivered event is de-duplicated', strpos((string) $out2, 'duplicate') !== false, (string) $out2);
    TestKit::check('Checkout Session id (cs_test_) is what was verified', strpos((string) $txn['gateway_payment_id'], 'cs_test_') === 0, (string) $txn['gateway_payment_id']);
    TestKit::eq('credited once: 20 USD x (122 - 2 safety margin) = 2400.00 BDT', '2400.00', TestKit::balance('qa_l1', 'balance'));
} catch (\Throwable $e) {
    TestKit::check('smoke suite aborted: ' . get_class($e), false, $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
} finally {
    TestKit::tearDown();
    foreach ($jars as $j) {
        @unlink($j);
    }
}
echo "\n" . TestKit::$pass . ' passed, ' . TestKit::$fail . " failed\n";
foreach (TestKit::$failures as $f) {
    echo "  - $f\n";
}
exit(TestKit::$fail === 0 ? 0 : 1);
