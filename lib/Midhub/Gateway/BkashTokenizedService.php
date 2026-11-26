<?php
namespace Midhub\Gateway;

use Midhub\Http\HttpClient;
use Midhub\Money;

/**
 * bKash Tokenized Checkout (API v1.2.0-beta).
 *
 *   1. Grant Token     POST /tokenized/checkout/token/grant      (cached ~1h per mode)
 *   2. Create Payment  POST /tokenized/checkout/create           -> bkashURL, paymentID
 *   3. Customer pays on bKash and is redirected to our callbackURL with paymentID + status
 *   4. Execute Payment POST /tokenized/checkout/execute          (captures the money)
 *   5. Query Payment   POST /tokenized/checkout/payment/status   (fallback when execute is ambiguous)
 *
 * Sandbox vs Live = base URL + credential set, both chosen by the admin's mode switch.
 */
final class BkashTokenizedService implements DepositGatewayInterface
{
    /** Create Payment: "Invalid Merchant Callback URL" (callback domain not registered for the merchant) */
    const ERR_CALLBACK = '2049';

    /** @var GatewayConfig */
    private $cfg;

    public function __construct(GatewayConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    public function createCheckout(array $txn, array $customer): array
    {
        $body = array(
            'mode' => '0011',
            'payerReference' => substr(preg_replace('/\D/', '', (string) $customer['phone']) ?: (string) $customer['id'], 0, 20),
            // query-free: bKash appends its own ?paymentID=&status= and validates this URL
            // against the merchant's registered domain (error 2049 otherwise)
            'callbackURL' => GatewayConfig::publicUrl('gateway/bkash_callback.php'),
            'amount' => Money::parse($txn['payable_amount']),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $txn['invoice_no'],
        );
        $res = $this->call('/tokenized/checkout/create', $body, 'create', $txn['invoice_no']);
        if (empty($res['bkashURL']) || empty($res['paymentID'])) {
            throw new GatewayException('bKash create failed: ' . $this->errorText($res) . $this->callbackHint($res, $body['callbackURL']), $this->customerError($res));
        }
        return array('redirect_url' => (string) $res['bkashURL'], 'gateway_payment_id' => (string) $res['paymentID'], 'state' => array());
    }

    public function completeReturn(array $txn, array $params): PaymentResult
    {
        $paymentId = isset($params['paymentID']) ? (string) $params['paymentID'] : '';
        $status = isset($params['status']) ? strtolower((string) $params['status']) : '';

        // The paymentID in the redirect must be the one we created for this invoice.
        if ($paymentId === '' || !hash_equals((string) $txn['gateway_payment_id'], $paymentId)) {
            return PaymentResult::make(PaymentResult::FAILED, 'Payment reference mismatch.');
        }
        if ($status === 'cancel') {
            return PaymentResult::make(PaymentResult::CANCELLED, 'Payment was cancelled.');
        }
        if ($status !== 'success') {
            return PaymentResult::make(PaymentResult::FAILED, 'Payment was not completed on bKash.');
        }

        // Execute captures the payment. Anything other than a clean "Completed" (error,
        // timeout, "already executed" 2117, rejection) is settled by Query Payment, the
        // authoritative state, so an ambiguous execute never credits or loses a payment.
        try {
            $res = $this->call('/tokenized/checkout/execute', array('paymentID' => $paymentId), 'execute', $txn['invoice_no'], 45);
            $result = $this->toResult($res, $txn);
            if ($result->isCompleted()) {
                return $result;
            }
        } catch (GatewayException $e) {
            // fall through to query
        }
        return $this->verify($txn);
    }

    public function verify(array $txn): PaymentResult
    {
        if (empty($txn['gateway_payment_id'])) {
            return PaymentResult::make(PaymentResult::FAILED, 'No bKash payment was created for this invoice.');
        }
        $res = $this->call('/tokenized/checkout/payment/status', array('paymentID' => $txn['gateway_payment_id']), 'query', $txn['invoice_no']);
        return $this->toResult($res, $txn);
    }

    public function testConnection(): array
    {
        try {
            TokenCache::forget('bkash');
            $this->token();
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
        if ($this->cfg->mode !== 'sandbox') {
            return array('ok' => true, 'message' => 'Grant Token succeeded (live). Create Payment is not tested in live mode; make one small real payment to confirm.');
        }
        // A token alone does not prove checkout works: on every Create Payment bKash also checks
        // the callbackURL against the domain registered for this merchant (error 2049). So the
        // sandbox test creates one payment (never executed, nothing is charged).
        $callback = GatewayConfig::publicUrl('gateway/bkash_callback.php');
        try {
            $res = $this->call('/tokenized/checkout/create', array(
                'mode' => '0011',
                'payerReference' => '01700000000',
                'callbackURL' => $callback,
                'amount' => '10.00',
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => 'TEST' . date('ymdHis'),
            ), 'test_create', null);
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => 'Grant Token OK, but Create Payment failed: ' . $e->getMessage());
        }
        if (!empty($res['paymentID']) && !empty($res['bkashURL'])) {
            return array('ok' => true, 'message' => "Grant Token and Create Payment succeeded (sandbox). Callback URL: $callback");
        }
        return array('ok' => false, 'message' => 'Grant Token OK, but Create Payment was rejected: ' . $this->errorText($res) . $this->callbackHint($res, $callback));
    }

    // ------------------------------------------------------------------------

    private function toResult(array $res, array $txn): PaymentResult
    {
        $ts = isset($res['transactionStatus']) ? (string) $res['transactionStatus'] : '';
        $r = PaymentResult::make(PaymentResult::PENDING, $ts, $res);
        $r->gatewayPaymentId = isset($res['paymentID']) ? (string) $res['paymentID'] : null;
        $r->gatewayTrxId = isset($res['trxID']) ? (string) $res['trxID'] : null;
        $r->paidAmount = isset($res['amount']) ? (string) $res['amount'] : null;
        $r->currency = isset($res['currency']) ? (string) $res['currency'] : 'BDT';
        $r->invoiceNo = isset($res['merchantInvoiceNumber']) ? (string) $res['merchantInvoiceNumber'] : null;

        if ($ts === 'Completed' && $r->gatewayTrxId) {
            $r->status = PaymentResult::COMPLETED;
        } elseif (in_array($ts, array('Initiated', 'Authorized', 'Pending'), true)) {
            $r->status = PaymentResult::PENDING;
        } elseif ($ts !== '' || (isset($res['statusCode']) && $res['statusCode'] !== '0000')) {
            $r->status = PaymentResult::FAILED;
            $r->message = $this->errorText($res);
        }
        return $r;
    }

    /** @return array<string,mixed> */
    private function call(string $path, array $body, string $title, ?string $ref, int $timeout = 30): array
    {
        $headers = array(
            'Accept' => 'application/json',
            'Authorization' => $this->token(),
            'X-APP-Key' => $this->cfg->cred('app_key'),
        );
        $resp = HttpClient::request('POST', $this->cfg->url($path), $headers, $body, array(
            'json' => true, 'timeout' => $timeout, 'log_type' => 'bkash', 'log_title' => $title, 'log_ref' => $ref,
        ));
        if ($resp->status === 401) {
            TokenCache::forget('bkash'); // token revoked early: next call re-authenticates
        }
        $json = $resp->json();
        if ($resp->errno !== 0 || $json === null) {
            throw new GatewayException("bKash $title transport error: HTTP {$resp->status} {$resp->error}");
        }
        return $json;
    }

    private function token(): string
    {
        $cached = TokenCache::get('bkash', $this->cfg->mode);
        if ($cached) {
            return $cached['token'];
        }
        $resp = HttpClient::request('POST', $this->cfg->url('/tokenized/checkout/token/grant'), array(
            'Accept' => 'application/json',
            'username' => $this->cfg->cred('username'),
            'password' => $this->cfg->cred('password'),
        ), array(
            'app_key' => $this->cfg->cred('app_key'),
            'app_secret' => $this->cfg->cred('app_secret'),
        ), array('json' => true, 'timeout' => 20, 'log_type' => 'bkash', 'log_title' => 'grant_token'));
        $json = $resp->json();
        if (!$json || empty($json['id_token'])) {
            throw new GatewayException('bKash Grant Token failed: ' . ($json ? $this->errorText($json) : "HTTP {$resp->status} {$resp->error}"));
        }
        TokenCache::put('bkash', $this->cfg->mode, (string) $json['id_token'], (string) (isset($json['refresh_token']) ? $json['refresh_token'] : ''), (int) (isset($json['expires_in']) ? $json['expires_in'] : 3600));
        return (string) $json['id_token'];
    }

    private function errorText(array $res): string
    {
        $code = isset($res['statusCode']) ? $res['statusCode'] : (isset($res['errorCode']) ? $res['errorCode'] : '?');
        $msg = isset($res['statusMessage']) ? $res['statusMessage'] : (isset($res['errorMessage']) ? $res['errorMessage'] : (isset($res['msg']) ? $res['msg'] : 'unknown error'));
        return "[$code] $msg";
    }

    /** Admin-facing explanation of error 2049 (callback URL not registered for the merchant). */
    private function callbackHint(array $res, string $callback): string
    {
        if (!isset($res['statusCode']) || (string) $res['statusCode'] !== self::ERR_CALLBACK) {
            return '';
        }
        $site = parse_url($callback, PHP_URL_SCHEME) . '://' . parse_url($callback, PHP_URL_HOST);
        $hint = " bKash accepts only the callback domain registered for this merchant; $callback is not.";
        if ($this->cfg->cred('username') === 'sandboxTokenizedUser02') {
            $hint .= ' These are bKash\'s shared public demo credentials, which reject every callback URL:'
                . ' ask bKash (merchant onboarding) for this merchant\'s own sandbox credentials with ' . $site . ' registered.';
        } else {
            $hint .= ' Ask bKash to register ' . $site . ' for this merchant, or set APP_URL in .env to the registered domain.';
        }
        return $hint;
    }

    private function customerError(array $res): string
    {
        if (isset($res['statusCode']) && (string) $res['statusCode'] === self::ERR_CALLBACK) {
            // merchant set-up problem: nothing the customer can fix, so no technical text
            return 'bKash payment is not available right now. Please choose another payment method.';
        }
        $msg = isset($res['statusMessage']) ? (string) $res['statusMessage'] : '';
        return $msg !== '' ? 'bKash: ' . $msg : 'bKash is unavailable right now. Please try again later.';
    }
}
