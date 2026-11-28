<?php
/**
 * Local mock of the external providers, for end-to-end tests only.
 *   php -S 127.0.0.1:8765 tests/mock_providers.php
 *
 * Behaviour is driven by the request so tests stay deterministic:
 *   Paywell top-up amount 11 -> success, 12 -> pending (then enquiry success), 13 -> rejected,
 *                         15 -> HTTP 500 (ambiguous, must stay pending)
 *   The Paywell bearer HMAC is verified exactly like the real API (status 602 on mismatch).
 * State (bKash payments, Paywell refs, voucher orders) is kept in a JSON file in the temp dir.
 */
const PW_API_USER = 'qa_api_user';
const PW_API_PASS = 'qa_api_pass';
const PW_API_KEY = 'qa_api_key_123';
const PW_ENC_KEY = 'qa_encryption_key_456';
const BK_APP_KEY = 'qa_bkash_app_key';

$stateFile = sys_get_temp_dir() . '/midhub_mock_state.json';
$state = is_file($stateFile) ? (json_decode((string) file_get_contents($stateFile), true) ?: array()) : array();
$save = function () use (&$state, $stateFile) {
    file_put_contents($stateFile, json_encode($state), LOCK_EX);
};
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$raw = (string) file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) {
    parse_str($raw, $body);
}
if ($_POST) {
    $body = $_POST; // multipart/form-data (php://input is empty for multipart)
}
$hdr = function ($name) {
    $k = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    return isset($_SERVER[$k]) ? (string) $_SERVER[$k] : '';
};
$json = function ($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
};

// ---------------- Paywell ----------------
if (strpos($path, '/paywell/') === 0) {
    $p = substr($path, strlen('/paywell'));
    if ($p === '/Authentication/PaywellAuth/getToken') {
        if ($hdr('Authorization') !== 'Basic ' . base64_encode(PW_API_USER . ':' . PW_API_PASS)) {
            $json(array('status' => 401, 'message' => 'bad credentials'), 401);
        }
        $json(array('status' => 200, 'token' => array('security_token' => 'pw-token-1', 'token_exp_time' => 3600)));
    }
    // verify the signed bearer like the real API
    $auth = $hdr('Authorization');
    $decoded = base64_decode(substr($auth, 7));
    $parts = explode(':', (string) $decoded, 3);
    if (count($parts) !== 3 || $parts[0] !== 'pw-token-1' || $parts[1] !== PW_API_KEY || !hash_equals(hash_hmac('sha256', $raw, PW_ENC_KEY), $parts[2])) {
        $json(array('status' => 602, 'message' => 'hash mismatch'));
    }
    if ($p === '/Recharge/mobileRecharge/singleTopup') {
        $ref = (string) $body['ref_id'];
        $amt = (int) $body['amount'];
        $state['pw'][$ref] = array('amount' => $amt, 'operator' => $body['operator'], 'con_type' => $body['con_type'], 'msisdn' => $body['msisdn']);
        if ($amt === 11) {
            $state['pw'][$ref]['final'] = 'success';
            $save();
            $json(array('status' => 200, 'message' => 'Recharge successful', 'data' => array('trx_id' => 'PWTRX' . substr($ref, -6))));
        }
        if ($amt === 12) {
            $state['pw'][$ref]['final'] = 'success_later';
            $save();
            $json(array('status' => 100, 'message' => 'Request accepted, processing'));
        }
        if ($amt === 13) {
            $state['pw'][$ref]['final'] = 'failed';
            $save();
            $json(array('status' => 405, 'message' => 'Invalid operator for number'));
        }
        if ($amt === 15) {
            $state['pw'][$ref]['final'] = 'success_later';
            $save();
            http_response_code(500);
            echo 'Internal Server Error';
            exit();
        }
        $state['pw'][$ref]['final'] = 'success';
        $save();
        $json(array('status' => 200, 'message' => 'OK', 'data' => array('trx_id' => 'PWTRX' . substr($ref, -6))));
    }
    if ($p === '/Recharge/mobileRecharge/rechargeEnquiry') {
        $ref = (string) $body['ref_id'];
        if (!isset($state['pw'][$ref])) {
            $json(array('status' => 404, 'message' => 'Transaction not found'));
        }
        $f = $state['pw'][$ref]['final'];
        if ($f === 'failed') {
            $json(array('status' => 405, 'message' => 'Recharge failed'));
        }
        $json(array('status' => 200, 'message' => 'Recharge successful', 'data' => array('trx_id' => 'PWENQ' . substr($ref, -6))));
    }
    if ($p === '/Retailer/RetailerService/retailerBalance') {
        $json(array('status' => 200, 'balanceData' => array('balance' => '15000.50')));
    }
    $json(array('status' => 404, 'message' => 'unknown paywell endpoint ' . $p), 404);
}

// ---------------- bKash tokenized ----------------
if (strpos($path, '/bkash/') === 0) {
    $p = substr($path, strlen('/bkash'));
    if ($p === '/tokenized/checkout/token/grant') {
        $ok = $hdr('username') === 'qa_bk_user' && $hdr('password') === 'qa_bk_pass' && $body['app_key'] === BK_APP_KEY && $body['app_secret'] === 'qa_bk_secret';
        if (!$ok) {
            $json(array('statusCode' => '2079', 'statusMessage' => 'Invalid username and password'));
        }
        $json(array('statusCode' => '0000', 'id_token' => 'bk-id-token', 'token_type' => 'Bearer', 'expires_in' => 3600, 'refresh_token' => 'bk-refresh'));
    }
    if ($hdr('Authorization') !== 'bk-id-token' || $hdr('X-APP-Key') !== BK_APP_KEY) {
        $json(array('message' => 'Unauthorized'), 401);
    }
    if ($p === '/tokenized/checkout/create') {
        if (!empty($state['bk_reject_callback'])) {
            // what bKash answers when callbackURL is not the merchant's registered domain
            $json(array('statusCode' => '2049', 'statusMessage' => 'Invalid Merchant Callback URL'));
        }
        $pid = 'TR00' . bin2hex(random_bytes(6));
        $state['bk'][$pid] = array('amount' => $body['amount'], 'inv' => $body['merchantInvoiceNumber'], 'status' => 'Initiated', 'executed' => 0, 'callback' => $body['callbackURL']);
        $save();
        $json(array('statusCode' => '0000', 'paymentID' => $pid, 'bkashURL' => 'http://127.0.0.1:8765/bkash-pay?paymentID=' . $pid, 'amount' => $body['amount'], 'transactionStatus' => 'Initiated'));
    }
    if ($p === '/tokenized/checkout/execute') {
        $pid = (string) $body['paymentID'];
        if (!isset($state['bk'][$pid])) {
            $json(array('statusCode' => '2056', 'statusMessage' => 'Invalid Payment State'));
        }
        if ($state['bk'][$pid]['executed']) {
            $json(array('statusCode' => '2117', 'statusMessage' => 'Payment execution already been called before'));
        }
        $state['bk'][$pid]['executed'] = 1;
        $state['bk'][$pid]['status'] = 'Completed';
        $state['bk'][$pid]['trx'] = 'BK' . strtoupper(bin2hex(random_bytes(4)));
        $save();
        $b = $state['bk'][$pid];
        $json(array('statusCode' => '0000', 'paymentID' => $pid, 'trxID' => $b['trx'], 'transactionStatus' => 'Completed', 'amount' => $b['amount'], 'currency' => 'BDT', 'merchantInvoiceNumber' => $b['inv']));
    }
    if ($p === '/tokenized/checkout/payment/status') {
        $pid = (string) $body['paymentID'];
        if (!isset($state['bk'][$pid])) {
            $json(array('statusCode' => '2056', 'statusMessage' => 'Invalid Payment State'));
        }
        $b = $state['bk'][$pid];
        $out = array('statusCode' => '0000', 'paymentID' => $pid, 'transactionStatus' => $b['status'], 'amount' => $b['amount'], 'currency' => 'BDT', 'merchantInvoiceNumber' => $b['inv']);
        if (!empty($b['trx'])) {
            $out['trxID'] = $b['trx'];
        }
        $json($out);
    }
    $json(array('statusCode' => '9999', 'statusMessage' => 'unknown'), 404);
}
// tests can make bKash reject every callbackURL (error 2049), as the public demo merchant does
if ($path === '/__bkash/reject_callback') {
    $state['bk_reject_callback'] = !empty($_GET['on']);
    $save();
    $json(array('ok' => true));
}
// tests can tamper with a bKash payment's recorded amount to simulate a mismatch
if ($path === '/__tamper/bkash') {
    $state['bk'][$_GET['pid']]['amount'] = $_GET['amount'];
    $save();
    $json(array('ok' => true));
}

// ---------------- Stripe ----------------
if (strpos($path, '/stripe/v1/') === 0) {
    if ($hdr('Authorization') !== 'Bearer sk_test_qa123') {
        $json(array('error' => array('message' => 'Invalid API Key provided')), 401);
    }
    $p = substr($path, strlen('/stripe'));
    if ($p === '/v1/checkout/sessions' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = 'cs_test_' . bin2hex(random_bytes(8));
        $state['cs'][$id] = array('amount' => (int) $body['line_items'][0]['price_data']['unit_amount'], 'currency' => $body['line_items'][0]['price_data']['currency'],
            'metadata' => $body['metadata'], 'three_ds' => isset($body['payment_method_options']['card']['request_three_d_secure']) ? $body['payment_method_options']['card']['request_three_d_secure'] : null,
            'success_url' => $body['success_url'], 'status' => 'open', 'payment_status' => 'unpaid', 'idem' => $hdr('Idempotency-Key'),
            'pmt' => isset($body['payment_method_types']) ? $body['payment_method_types'] : null);
        $save();
        $json(array('id' => $id, 'object' => 'checkout.session', 'url' => 'http://127.0.0.1:8765/stripe-pay/' . $id, 'status' => 'open'));
    }
    if (preg_match('#^/v1/checkout/sessions/(cs_test_[a-f0-9]+)(/expire)?$#', $p, $m)) {
        if (!isset($state['cs'][$m[1]])) {
            $json(array('error' => array('message' => 'No such checkout session')), 404);
        }
        if (!empty($m[2])) {
            if ($state['cs'][$m[1]]['status'] !== 'open') {
                $json(array('error' => array('message' => 'Only open sessions can be expired')), 400);
            }
            $state['cs'][$m[1]]['status'] = 'expired';
            $save();
        }
        $c = $state['cs'][$m[1]];
        $paid = $c['payment_status'] === 'paid';
        $json(array('id' => $m[1], 'object' => 'checkout.session', 'status' => $c['status'], 'payment_status' => $c['payment_status'], 'amount_total' => $c['amount'],
            'currency' => $c['currency'], 'metadata' => $c['metadata'], 'client_reference_id' => $c['metadata']['invoice_no'], 'livemode' => false,
            'payment_intent' => $paid ? array('id' => 'pi_' . substr($m[1], 8), 'latest_charge' => 'ch_' . substr($m[1], 8)) : null));
    }
    if ($p === '/v1/balance') {
        $json(array('object' => 'balance', 'livemode' => false));
    }
    if ($p === '/v1/payment_intents' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = 'pi_' . bin2hex(random_bytes(8));
        $state['st'][$id] = array('amount' => (int) $body['amount'], 'currency' => $body['currency'], 'metadata' => $body['metadata'],
            'three_ds' => isset($body['payment_method_options']['card']['request_three_d_secure']) ? $body['payment_method_options']['card']['request_three_d_secure'] : null,
            'status' => 'requires_payment_method', 'idem' => $hdr('Idempotency-Key'));
        $save();
        $json(array('id' => $id, 'object' => 'payment_intent', 'client_secret' => $id . '_secret_x', 'status' => 'requires_payment_method', 'amount' => (int) $body['amount']));
    }
    if (preg_match('#^/v1/payment_intents/(pi_[a-z0-9]+)$#', $p, $m)) {
        if (!isset($state['st'][$m[1]])) {
            $json(array('error' => array('message' => 'No such payment_intent')), 404);
        }
        $s = $state['st'][$m[1]];
        $json(array('id' => $m[1], 'object' => 'payment_intent', 'status' => $s['status'], 'amount' => $s['amount'],
            'amount_received' => $s['status'] === 'succeeded' ? $s['amount'] : 0, 'currency' => $s['currency'], 'client_secret' => $m[1] . '_secret_x',
            'latest_charge' => $s['status'] === 'succeeded' ? 'ch_' . substr($m[1], 3) : null, 'livemode' => false, 'metadata' => $s['metadata']));
    }
    $json(array('error' => array('message' => 'unknown')), 404);
}
if ($path === '/__succeed/stripe') {
    if (!empty($_GET['cs'])) {
        $state['cs'][$_GET['cs']]['status'] = 'complete';
        $state['cs'][$_GET['cs']]['payment_status'] = 'paid';
        $save();
        $c = $state['cs'][$_GET['cs']];
        $json(array('ok' => true, 'three_ds' => $c['three_ds'], 'idem' => $c['idem'], 'success_url' => $c['success_url'], 'pmt' => $c['pmt']));
    }
    $state['st'][$_GET['pi']]['status'] = 'succeeded';
    $save();
    $json(array('ok' => true, 'three_ds' => $state['st'][$_GET['pi']]['three_ds'], 'idem' => $state['st'][$_GET['pi']]['idem']));
}

// ---------------- Bill pay aggregator (shl-style) ----------------
// account 1234567 -> pays OK, 7654321 -> payment rejected, 5555555 -> pending then OK, 9999999 -> already paid
if (preg_match('#^/billpay/api/appapi/billpay/(fetch|payment|status)/(sandbox|live)/([a-z0-9-]+)$#', $path, $m)) {
    if ($hdr('token') !== 'qa_bill_token') {
        $json(array('result' => 'error', 'message' => 'Invalid token'), 401);
    }
    $acc = isset($body['bill_no']) ? $body['bill_no'] : (isset($body['biller_acc_no']) ? $body['biller_acc_no'] : (isset($body['meter_no']) ? $body['meter_no'] : ''));
    if ($m[1] === 'fetch') {
        $bpid = 'BPID' . substr($acc, -4) . mt_rand(1000, 9999);
        $state['bp'][$bpid] = array('acc' => $acc, 'slug' => $m[3], 'paid' => false, 'polls' => 0);
        $save();
        $json(array('result' => 'success', 'bill_ref' => array('bill_payment_id' => $bpid, 'bill_refer_id' => 'REF' . $bpid),
            'data' => array('bill_name' => 'QA Customer', 'bill_no' => $acc, 'biller_acc_no' => $acc, 'biller_mobile' => '01700000000', 'bill_amount' => '1500.00', 'bill_vat' => '15.00',
                'charge' => '5.00', 'bill_total_amount' => '1520.00', 'is_bill_paid' => $acc === '9999999' ? 'Y' : 'N', 'bllr_id' => strtoupper($m[3]), 'bill_month' => '09', 'bill_year' => '2026'),
            'invoice' => array(array('label_en' => 'Bill Amount', 'value' => '1500.00'), array('label_en' => 'Total', 'value' => '1520.00', 'is_total_row' => true))));
    }
    $bpid = (string) $body['bill_payment_id'];
    if (!isset($state['bp'][$bpid])) {
        $json(array('result' => 'error', 'message' => 'Unknown bill'));
    }
    $b = &$state['bp'][$bpid];
    if ($m[1] === 'payment') {
        $b['amount'] = $body['amount'];
        if ($b['acc'] === '7654321') { $save(); $json(array('result' => 'failed', 'message' => 'Biller rejected payment')); }
        if ($b['acc'] === '5555555') { $b['pending'] = true; $save(); $json(array('result' => 'pending', 'message' => 'Processing')); }
        $b['paid'] = true; $save();
        $json(array('result' => 'success', 'message' => 'Bill paid', 'data' => array('transaction_id' => 'BILLTRX' . substr($bpid, -6), 'is_bill_paid' => 'Y')));
    }
    $b['polls']++; $b['paid'] = true; $save();
    $json(array('result' => 'success', 'message' => 'Bill paid', 'data' => array('transaction_id' => 'BILLTRXP' . substr($bpid, -6), 'is_bill_paid' => 'Y')));
}

// ---------------- SMS gateway ----------------
if ($path === '/sms/api/smsapi') {
    if (!isset($_GET['api_key']) || $_GET['api_key'] !== 'qa_sms_key') {
        $json(array('response_code' => 1002, 'error' => 'invalid key'));
    }
    $state['sms'][] = array('to' => $_GET['number'], 'msg' => $_GET['message']);
    $save();
    echo 'SMS SUBMITTED: ID - ' . count($state['sms']);
    exit();
}

// ---------------- Nagad remote-payment-gateway v-0.2.0 ----------------
// RSA exactly as the real PGW: request sensitiveData encrypted with the PG public key and signed
// (SHA256withRSA) with the merchant private key; responses encrypted for the merchant and signed by the PG.
$nagadKeys = function () use (&$state, $save) {
    if (empty($state['ng_keys'])) {
        $opt = array('private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA);
        $cnf = getenv('OPENSSL_CONF') ?: dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf';
        if (is_file($cnf)) {
            $opt['config'] = $cnf;
        }
        foreach (array('pg', 'merchant') as $who) {
            $k = openssl_pkey_new($opt);
            openssl_pkey_export($k, $priv, null, $opt);
            $state['ng_keys'][$who] = array('private' => $priv, 'public' => openssl_pkey_get_details($k)['key']);
        }
        $save();
    }
    return $state['ng_keys'];
};
if ($path === '/__nagad_keys') {
    $k = $nagadKeys();
    $json(array('merchant_private_key' => $k['merchant']['private'], 'nagad_public_key' => $k['pg']['public']));
}
if ($path === '/__nagad') {
    foreach (array('badsig', 'outcome') as $knob) {
        if (isset($_GET[$knob])) {
            $state['ng_' . $knob] = (string) $_GET[$knob];
        }
    }
    $save();
    $json(array('ok' => true));
}
if (strpos($path, '/nagad/') === 0) {
    $k = $nagadKeys();
    $p = substr($path, strlen('/nagad'));
    if ($hdr('X-KM-Api-Version') === '' || $hdr('X-KM-Client-Type') === '' || $hdr('X-KM-IP-V4') === '') {
        $json(array('reason' => 'Missing X-KM headers', 'message' => 'Invalid request'), 400);
    }
    $open = function (array $b) use ($k) {
        $plain = '';
        if (empty($b['sensitiveData']) || !openssl_private_decrypt((string) base64_decode((string) $b['sensitiveData']), $plain, $k['pg']['private'], OPENSSL_PKCS1_PADDING)) {
            return null;
        }
        if (empty($b['signature']) || openssl_verify($plain, (string) base64_decode((string) $b['signature']), $k['merchant']['public'], OPENSSL_ALGO_SHA256) !== 1) {
            return null;
        }
        return json_decode($plain, true);
    };
    if (preg_match('#^/api/dfs/check-out/initialize/([0-9]+)/([A-Za-z0-9]+)$#', $p, $m)) {
        $d = $open($body);
        if (!$d || $d['merchantId'] !== $m[1] || $d['orderId'] !== $m[2] || empty($d['challenge'])) {
            $json(array('reason' => 'Invalid Request', 'message' => 'Signature or sensitive data invalid'), 400);
        }
        $ref = 'NG' . strtoupper(bin2hex(random_bytes(8)));
        $challenge = bin2hex(random_bytes(10));
        $state['ng'][$ref] = array('orderId' => $m[2], 'merchantId' => $m[1], 'challenge' => $challenge, 'amount' => null);
        $save();
        $plain = json_encode(array('paymentReferenceId' => $ref, 'challenge' => $challenge, 'acceptDateTime' => date('YmdHis')));
        openssl_public_encrypt($plain, $enc, $k['merchant']['public'], OPENSSL_PKCS1_PADDING);
        openssl_sign(!empty($state['ng_badsig']) ? $plain . 'x' : $plain, $sig, $k['pg']['private'], OPENSSL_ALGO_SHA256);
        $json(array('sensitiveData' => base64_encode($enc), 'signature' => base64_encode($sig)));
    }
    if (preg_match('#^/api/dfs/check-out/complete/(NG[0-9A-F]{16})$#', $p, $m) && isset($state['ng'][$m[1]])) {
        $d = $open($body);
        $s = $state['ng'][$m[1]];
        if (!$d || $d['challenge'] !== $s['challenge'] || $d['orderId'] !== $s['orderId'] || $d['currencyCode'] !== '050' || empty($body['merchantCallbackURL'])) {
            $json(array('status' => 'Failed', 'reason' => 'Invalid Request', 'message' => 'challenge / order mismatch'), 400);
        }
        $state['ng'][$m[1]]['amount'] = (string) $d['amount'];
        $save();
        $json(array('status' => 'Success', 'callBackUrl' => 'http://127.0.0.1:8765/nagad-pay?ref=' . $m[1]));
    }
    if (preg_match('#^/api/dfs/verify/payment/(NG[0-9A-F]{16})$#', $p, $m) && isset($state['ng'][$m[1]])) {
        $s = $state['ng'][$m[1]];
        $outcome = !empty($state['ng_outcome']) ? $state['ng_outcome'] : 'Success';
        $json(array('merchantId' => $s['merchantId'], 'orderId' => $s['orderId'], 'paymentRefId' => $m[1], 'amount' => $s['amount'],
            'issuerPaymentRefNo' => $outcome === 'Success' ? 'NGTRX' . substr($m[1], -8) : null, 'status' => $outcome, 'statusCode' => $outcome === 'Success' ? '000' : '001'));
    }
    $json(array('reason' => 'Not found', 'message' => 'unknown nagad endpoint ' . $p), 404);
}

// ---------------- Upay merchant checkout (paths as configured in the registry) ----------------
if (strpos($path, '/upay/') === 0) {
    $p = substr($path, strlen('/upay'));
    if ($p === '/payment/merchant-auth/') {
        if (($body['merchant_id'] ?? '') !== 'qa_up_mid' || ($body['merchant_key'] ?? '') !== 'qa_up_key') {
            $json(array('code' => 'MAF4001', 'message' => 'Invalid merchant credentials'), 401);
        }
        $json(array('code' => 'MAS2001', 'message' => 'Authorized', 'data' => array('token' => 'upay-qa-token')));
    }
    if ($hdr('Authorization') !== 'UPAY upay-qa-token') {
        $json(array('code' => 'AUTH401', 'message' => 'Unauthorized'), 401);
    }
    if ($p === '/payment/merchant-payment-init/') {
        $trx = 'UP' . strtoupper(bin2hex(random_bytes(5)));
        $state['up'][(string) $body['txn_id']] = array('amount' => (string) $body['amount'], 'trx' => $trx, 'redirect' => (string) $body['redirect_url']);
        $save();
        $json(array('code' => 'MPIS2002', 'message' => 'Payment initiated', 'data' => array('gateway_url' => 'http://127.0.0.1:8765/upay-pay?trx=' . $trx, 'trx_id' => $trx)));
    }
    if (preg_match('#^/payment/single-payment-status/([A-Za-z0-9]+)/$#', $p, $m) && isset($state['up'][$m[1]])) {
        $s = $state['up'][$m[1]];
        $json(array('code' => 'SPS2001', 'message' => 'ok', 'data' => array('status' => 'success', 'amount' => $s['amount'], 'txn_id' => $m[1], 'trx_id' => $s['trx'])));
    }
    $json(array('code' => 'NF404', 'message' => 'unknown upay endpoint ' . $p), 404);
}

// ---------------- UddoktaPay (checkout-v2 / verify-payment) ----------------
if (strpos($path, '/uddoktapay/') === 0) {
    $p = substr($path, strlen('/uddoktapay'));
    if ($hdr('RT-UDDOKTAPAY-API-KEY') !== 'qa_udp_key') {
        $json(array('status' => false, 'message' => 'Unauthorized Action'), 401);
    }
    if ($p === '/api/checkout-v2') {
        $iid = 'UDP' . strtoupper(bin2hex(random_bytes(6)));
        $state['udp'][$iid] = array('amount' => (string) $body['amount'], 'metadata' => $body['metadata'], 'full_name' => $body['full_name'], 'email' => $body['email']);
        $save();
        $json(array('status' => true, 'message' => 'Payment Url', 'payment_url' => 'http://127.0.0.1:8765/udp-pay?invoice_id=' . $iid));
    }
    if ($p === '/api/verify-payment') {
        $iid = (string) ($body['invoice_id'] ?? '');
        if (!isset($state['udp'][$iid])) {
            $json(array('status' => 'ERROR', 'message' => 'Invoice not found'));
        }
        $s = $state['udp'][$iid];
        $json(array('full_name' => $s['full_name'], 'email' => $s['email'], 'amount' => $s['amount'], 'fee' => '0.00', 'charged_amount' => $s['amount'], 'invoice_id' => $iid,
            'metadata' => $s['metadata'], 'payment_method' => 'bkash', 'sender_number' => '01700000000', 'transaction_id' => 'UDTRX' . substr($iid, -6), 'date' => date('Y-m-d H:i:s'), 'status' => 'COMPLETED'));
    }
    $json(array('status' => false, 'message' => 'unknown uddoktapay endpoint ' . $p), 404);
}

// ---------------- Voucher provider: Popular Vouchers Catalogue & Distributor API v1.1 shape ----------------
if ($path === '/__pv') {
    // test knobs: count, bump (price increase), drop (variant to omit), env (account environment)
    foreach (array('count', 'bump', 'drop', 'env') as $k) {
        if (isset($_GET[$k])) {
            $state['pv'][$k] = $_GET[$k];
        }
    }
    $save();
    $json(array('ok' => true, 'pv' => isset($state['pv']) ? $state['pv'] : array()));
}
if (strpos($path, '/voucher/') === 0) {
    if ($hdr('Authorization') !== 'Bearer qa_voucher_key') {
        $json(array('error' => array('message' => 'unauthorized')), 401);
    }
    $pv = isset($state['pv']) ? $state['pv'] : array();
    $p = substr($path, strlen('/voucher'));
    if ($p === '/distributor/account') {
        $json(array('account' => array('id' => 'dist_QA', 'name' => 'QA Distributor', 'code' => 'QA', 'status' => 'active', 'balance' => 5000,
            'currency_code' => 'bdt', 'environment' => isset($pv['env']) ? $pv['env'] : 'sandbox')));
    }
    if ($p === '/distributor/catalog') {
        $bump = isset($pv['bump']) ? (int) $pv['bump'] : 0;
        $phone = array(array('name' => 'DestNo', 'type' => 'tel', 'label' => 'Mobile / Account Number', 'required' => true, 'max_length' => 64, 'destination_semantics' => 'phone', 'dial_code' => '+880'));
        $all = array(
            array('variant_id' => 'variant_QA_FFID', 'product_label' => 'Free Fire', 'variant_label' => '100 Diamonds', 'title' => 'Free Fire - 100 Diamonds (UID)', 'unit_amount' => 80, 'channel' => 'gift_card',
                'country_id' => 'bd', 'country_label' => 'Bangladesh', 'delivery_schema' => array(array('name' => 'User', 'type' => 'text', 'label' => 'Player ID (UID)', 'required' => true, 'max_length' => 20))),
            array('variant_id' => 'variant_QA_GP100', 'product_label' => 'Grameenphone', 'variant_label' => '100 BDT', 'title' => 'Grameenphone Recharge - 100 BDT', 'unit_amount' => 99, 'channel' => 'mobile_recharge',
                'country_id' => 'bd', 'country_label' => 'Bangladesh', 'delivery_schema' => $phone),
            array('variant_id' => 'variant_QA_GPLAY', 'product_label' => 'Google Play', 'variant_label' => 'USD 5', 'title' => 'Google Play (US) - USD 5', 'unit_amount' => 500 + $bump, 'channel' => 'gift_card',
                'country_id' => 'us', 'country_label' => 'United States', 'thumbnail' => 'https://medback.popularvouchers.com/static/gplay.png', 'delivery_schema' => array()),
            array('variant_id' => 'variant_QA_OOS', 'product_label' => 'Steam', 'variant_label' => 'USD 10', 'title' => 'Steam - USD 10', 'unit_amount' => 1300, 'channel' => 'gift_card', 'delivery_schema' => array()),
            array('variant_id' => 'variant_QA_PEND', 'product_label' => 'Netflix', 'variant_label' => '1 Month', 'title' => 'Netflix - 1 Month', 'unit_amount' => 900, 'channel' => 'gift_card', 'delivery_schema' => array()),
            array('variant_id' => 'variant_QA_USD', 'product_label' => 'Foreign', 'variant_label' => '5', 'title' => 'Priced in USD', 'unit_amount' => 5, 'currency_code' => 'usd', 'channel' => 'gift_card', 'delivery_schema' => array()),
            array('variant_id' => 'variant_QA_MULTI', 'product_label' => 'Mobile Legends', 'variant_label' => '86 Diamonds', 'title' => 'Mobile Legends - 86 Diamonds', 'unit_amount' => 150, 'channel' => 'gift_card',
                'delivery_schema' => array(array('name' => 'User', 'type' => 'text', 'required' => true), array('name' => 'Zone', 'type' => 'text', 'required' => true))),
            array('variant_id' => 'variant_QA_409', 'product_label' => 'Spotify', 'variant_label' => '1 Month', 'title' => 'Spotify - 1 Month', 'unit_amount' => 300, 'channel' => 'gift_card', 'delivery_schema' => array()),
        );
        $count = isset($pv['count']) ? (int) $pv['count'] : count($all);
        for ($i = count($all); $i < $count; $i++) {
            $all[] = array('variant_id' => sprintf('variant_QA_F%03d', $i), 'product_label' => 'Filler', 'variant_label' => (string) $i, 'title' => 'Filler card ' . $i,
                'unit_amount' => 100 + $i + $bump, 'channel' => 'gift_card', 'delivery_schema' => array());
        }
        foreach ($all as &$x) {
            if (!isset($x['currency_code'])) {
                $x['currency_code'] = 'bdt';
            }
        }
        unset($x);
        if (!empty($pv['drop'])) {
            $all = array_values(array_filter($all, function ($x) use ($pv) { return $x['variant_id'] !== $pv['drop']; }));
        }
        $limit = isset($_GET['limit']) ? min(100, (int) $_GET['limit']) : 100;
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
        $page = array_slice($all, $offset, $limit);
        $json(array('products' => $page, 'count' => count($page), 'limit' => $limit, 'offset' => $offset, 'metadata' => array('skip' => $offset, 'take' => $limit, 'count' => count($all))));
    }
    // Orders, exactly as the real API answered our sandbox orders on 2026-11-28:
    // POST /distributor/transactions (Idempotency-Key required, replay returns the same transaction),
    // 202 {transaction:{id:'dtx_..', status:'accepted'}}, then GET /distributor/transactions/{id}.
    if ($p === '/distributor/transactions' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $idem = $hdr('Idempotency-Key');
        $state['pv_last_order'] = array('body' => $body, 'idem' => $idem);
        $v = isset($body['variant_id']) ? (string) $body['variant_id'] : '';
        if ($idem === '' || $v === '' || !isset($body['quantity']) || (int) $body['quantity'] !== 1) {
            $save();
            $json(array('error' => array('code' => 'invalid_request', 'message' => 'Idempotency-Key and variant_id are required; quantity must be 1')), 400);
        }
        if (isset($state['pv_key'][$idem])) {
            $state['pv_posts'][$idem] = (isset($state['pv_posts'][$idem]) ? $state['pv_posts'][$idem] : 0) + 1;
            $save();
            $json(array('transaction' => $state['pv_tx'][$state['pv_key'][$idem]], 'balance' => 5000, 'replayed' => true), 200);
        }
        if (strpos($v, 'variant_QA_') !== 0) {
            $save();
            $json(array('error' => array('code' => 'product_not_found', 'message' => 'Product variant is unavailable')), 404);
        }
        if ($v === 'variant_QA_OOS') {
            $save();
            $json(array('error' => array('code' => 'out_of_stock', 'message' => 'Variant out of stock')), 422);
        }
        if ($v === 'variant_QA_409') {
            $save();
            $json(array('error' => array('code' => 'conflict', 'message' => 'A transaction with this key is already being processed')), 409);
        }
        $need = $v === 'variant_QA_FFID' ? 'User' : ($v === 'variant_QA_GP100' ? 'DestNo' : '');
        $delivery = isset($body['delivery_details']) && is_array($body['delivery_details']) ? $body['delivery_details'] : array();
        if ($need !== '' && (!isset($delivery[$need]) || (string) $delivery[$need] === '')) {
            $save();
            $json(array('error' => array('code' => 'invalid_delivery_details', 'message' => 'Delivery details do not match the supplier requirements', 'fields' => array($need))), 400);
        }
        $id = 'dtx_' . bin2hex(random_bytes(8));
        $tx = array('id' => $id, 'order_id' => null, 'external_id' => $idem, 'product_variant_id' => $v, 'quantity' => 1, 'currency_code' => 'bdt',
            'status' => 'accepted', 'delivery_details' => $need !== '' ? $delivery : array(), 'result' => null, 'failure_code' => null, 'failure_message' => null,
            'delivery_channel' => 'email', 'buyer_email' => null);
        if ($v === 'variant_QA_PEND') {
            $tx['code'] = 'ORD-' . substr($idem, -6); // transaction-level "code" is a reference, NOT a voucher code
        }
        $behavior = isset($state['pv_next']) ? $state['pv_next'] : '';
        unset($state['pv_next']);
        $state['pv_tx'][$id] = $tx;
        $state['pv_meta'][$id] = array('behavior' => $behavior, 'variant' => $v, 'dest' => $need !== '' ? (string) $delivery[$need] : '');
        $state['pv_key'][$idem] = $id;
        $state['pv_posts'][$idem] = 1;
        $save();
        if ($behavior === 'timeout') {
            $json(array('message' => 'upstream timeout'), 504); // created, but the reply is lost
        }
        $json(array('transaction' => $tx, 'balance' => 5000, 'replayed' => false), 202);
    }
    if (preg_match('#^/distributor/transactions/(dtx_[0-9a-f]{16})$#', $p, $mm)) {
        $id = $mm[1];
        if (!isset($state['pv_tx'][$id])) {
            $json(array('error' => array('code' => 'not_found', 'message' => 'Transaction not found')), 404);
        }
        $tx = $state['pv_tx'][$id];
        $meta = $state['pv_meta'][$id];
        if ($tx['status'] === 'accepted' && ($meta['variant'] !== 'variant_QA_PEND' || !empty($state['pv_release'][$tx['external_id']]))) {
            $ref = substr($tx['external_id'], -6);
            if ($meta['dest'] !== '' && trim($meta['dest'], '0') === '') {
                $tx['status'] = 'failed';
                $tx['failure_code'] = 'invalid_destination';
                $tx['failure_message'] = 'Invalid destination';
            } elseif ($meta['dest'] !== '') {
                $tx['status'] = 'completed';
                $tx['result'] = array('sandbox' => false, 'delivery' => array('message' => 'Top-up delivered', 'reference' => 'sup_' . $ref));
            } elseif ($meta['behavior'] === 'nocode') {
                $tx['status'] = 'completed';
                $tx['result'] = array('sandbox' => false, 'delivery' => array('message' => 'Delivered by email', 'reference' => 'sup_' . $ref));
            } elseif ($meta['behavior'] === 'sandbox') {
                $tx['status'] = 'completed';
                $tx['result'] = array('test' => false, 'sandbox' => true, 'provider' => 'sandbox',
                    'delivery' => array('message' => 'Simulated fulfillment completed successfully. A test delivery email was sent - no real supplier order was created.', 'reference' => 'sandbox_' . $id));
            } else {
                $tx['status'] = 'completed';
                $prefix = $meta['variant'] === 'variant_QA_PEND' ? 'PENDCODE-' : 'LIVECODE-';
                $tx['delivery_details'] = array(array('code' => $prefix . $ref, 'pin' => $meta['variant'] === 'variant_QA_PEND' ? '9999' : '4455', 'serial' => 'S1', 'expires_at' => '2027-12-31'));
                $tx['result'] = array('sandbox' => false, 'delivery' => array('message' => 'Delivered', 'reference' => 'sup_' . $ref));
            }
            $state['pv_tx'][$id] = $tx;
            $save();
        }
        $json(array('transaction' => $tx));
    }
    $json(array('error' => array('message' => 'not found')), 404);
}

// tests: behaviour of the NEXT Popular Vouchers transaction (timeout | nocode | sandbox), and
// releasing a transaction that stays "accepted" (variant_QA_PEND) by its Idempotency-Key
if ($path === '/__pv_next') {
    $state['pv_next'] = (string) $_GET['behavior'];
    $save();
    $json(array('ok' => true));
}
if ($path === '/__pv_release') {
    $state['pv_release'][(string) $_GET['key']] = 1;
    $save();
    $json(array('ok' => true));
}
if ($path === '/__pw_final') {
    $state['pw'][$_GET['ref']]['final'] = $_GET['final'];
    $save();
    $json(array('ok' => true));
}
if ($path === '/__state') {
    $json($state);
}
if ($path === '/__reset') {
    $state = array();
    $save();
    $json(array('ok' => true));
}
$json(array('error' => 'no mock for ' . $path), 404);
