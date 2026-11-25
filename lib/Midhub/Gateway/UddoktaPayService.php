<?php
namespace Midhub\Gateway;

use Midhub\Http\HttpClient;
use Midhub\Money;

/**
 * UddoktaPay MFS aggregator (bKash / Nagad / Rocket / Upay via one checkout).
 *
 *   Create  POST {base}/api/checkout-v2    (header RT-UDDOKTAPAY-API-KEY) -> payment_url
 *   Return  redirect_url?invoice_id=<uddoktapay invoice>
 *   Verify  POST {base}/api/verify-payment  {invoice_id}                 (authoritative)
 *   Webhook POST JSON, header RT-UDDOKTAPAY-API-KEY = our API key
 *
 * UddoktaPay does not HMAC-sign webhooks. The header is compared in constant time, and
 * the payment is then re-verified with Verify Payment, so a forged webhook can only
 * trigger a lookup, never a credit.
 */
final class UddoktaPayService implements DepositGatewayInterface, WebhookCapableInterface
{
    /** @var GatewayConfig */
    private $cfg;

    public function __construct(GatewayConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    public function createCheckout(array $txn, array $customer): array
    {
        $inv = rawurlencode($txn['invoice_no']);
        $email = filter_var((string) $customer['email'], FILTER_VALIDATE_EMAIL) ? (string) $customer['email'] : 'customer' . (int) $customer['id'] . '@midhub360.com';
        $res = $this->post('/api/checkout-v2', array(
            'full_name' => (string) ($customer['fullname'] ?: $customer['user']),
            'email' => $email,
            'amount' => Money::parse($txn['payable_amount']),
            'metadata' => array('invoice_no' => $txn['invoice_no'], 'user_id' => (string) $txn['user_id']),
            'redirect_url' => GatewayConfig::publicUrl('gateway/return.php?gw=uddoktapay&inv=' . $inv),
            'cancel_url' => GatewayConfig::publicUrl('gateway/return.php?gw=uddoktapay&cancel=1&inv=' . $inv),
            'webhook_url' => GatewayConfig::publicUrl('gateway/webhook.php?gw=uddoktapay'),
            'return_type' => 'GET',
        ), 'checkout', $txn['invoice_no']);
        if (empty($res['payment_url'])) {
            throw new GatewayException('UddoktaPay checkout failed: ' . (isset($res['message']) ? $res['message'] : json_encode($res)), 'The payment page could not be opened. Please try again later.');
        }
        return array('redirect_url' => (string) $res['payment_url'], 'gateway_payment_id' => null, 'state' => array());
    }

    public function completeReturn(array $txn, array $params): PaymentResult
    {
        if (!empty($params['cancel'])) {
            return PaymentResult::make(PaymentResult::CANCELLED, 'Payment was cancelled.');
        }
        $invoiceId = isset($params['invoice_id']) ? (string) $params['invoice_id'] : '';
        if ($invoiceId === '' || !preg_match('/^[A-Za-z0-9_-]{4,64}$/', $invoiceId)) {
            return PaymentResult::make(PaymentResult::PENDING, 'Waiting for payment confirmation.');
        }
        $txn['gateway_payment_id'] = $invoiceId;
        return $this->verify($txn);
    }

    public function verify(array $txn): PaymentResult
    {
        if (empty($txn['gateway_payment_id'])) {
            return PaymentResult::make(PaymentResult::PENDING, 'Waiting for payment confirmation.');
        }
        $res = $this->post('/api/verify-payment', array('invoice_id' => (string) $txn['gateway_payment_id']), 'verify', $txn['invoice_no']);
        $status = strtoupper((string) (isset($res['status']) ? $res['status'] : ''));
        $r = PaymentResult::make(PaymentResult::PENDING, $status, $res);
        $r->gatewayPaymentId = isset($res['invoice_id']) ? (string) $res['invoice_id'] : (string) $txn['gateway_payment_id'];
        $r->gatewayTrxId = isset($res['transaction_id']) ? (string) $res['transaction_id'] : null;
        $r->paidAmount = isset($res['amount']) ? (string) $res['amount'] : null;
        $r->currency = 'BDT';
        $r->invoiceNo = isset($res['metadata']['invoice_no']) ? (string) $res['metadata']['invoice_no'] : null;
        if ($status === 'COMPLETED') {
            $r->status = PaymentResult::COMPLETED;
        } elseif (in_array($status, array('ERROR', 'FAILED', 'CANCELLED'), true)) {
            $r->status = $status === 'CANCELLED' ? PaymentResult::CANCELLED : PaymentResult::FAILED;
        }
        return $r;
    }

    public function parseWebhook(string $rawBody, array $headers): array
    {
        $given = isset($headers['rt-uddoktapay-api-key']) ? (string) $headers['rt-uddoktapay-api-key'] : '';
        if ($given === '' || !hash_equals($this->cfg->cred('api_key'), $given)) {
            throw new GatewayException('UddoktaPay webhook API key mismatch');
        }
        $data = json_decode($rawBody, true);
        if (!is_array($data) || empty($data['invoice_id'])) {
            throw new GatewayException('UddoktaPay webhook payload invalid');
        }
        return array(
            'event_id' => (string) $data['invoice_id'] . ':' . strtoupper((string) (isset($data['status']) ? $data['status'] : '')),
            'event_type' => 'payment.' . strtolower((string) (isset($data['status']) ? $data['status'] : 'unknown')),
            'invoice_no' => isset($data['metadata']['invoice_no']) ? (string) $data['metadata']['invoice_no'] : null,
            'gateway_payment_id' => (string) $data['invoice_id'],
        );
    }

    public function testConnection(): array
    {
        try {
            $res = $this->post('/api/verify-payment', array('invoice_id' => 'connection-test'), 'test', null);
            $msg = isset($res['message']) ? (string) $res['message'] : json_encode($res);
            $authFailed = stripos($msg, 'unauthor') !== false || stripos($msg, 'api key') !== false;
            return array('ok' => !$authFailed, 'message' => ($authFailed ? 'API key rejected: ' : 'API reachable and key accepted (dummy invoice lookup said: ') . $msg . ($authFailed ? '' : ')'));
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    /** @return array<string,mixed> */
    private function post(string $path, array $body, string $title, ?string $ref): array
    {
        $resp = HttpClient::request('POST', $this->cfg->url($path), array(
            'Accept' => 'application/json',
            'RT-UDDOKTAPAY-API-KEY' => $this->cfg->cred('api_key'),
        ), $body, array('json' => true, 'timeout' => 30, 'log_type' => 'uddoktapay', 'log_title' => $title, 'log_ref' => $ref));
        $json = $resp->json();
        if ($json === null) {
            throw new GatewayException("UddoktaPay $title transport error: HTTP {$resp->status} {$resp->error}");
        }
        return $json;
    }
}
