<?php
namespace Midhub\Gateway;

use Midhub\Http\HttpClient;
use Midhub\Money;

/**
 * Stripe Checkout Sessions for international (USD) top-ups (legacy bare-PaymentIntent invoices still verify).
 *
 *  - Test vs Live is purely the key pair (sk_test_/pk_test_ vs sk_live_/pk_live_) chosen by the admin mode.
 *  - 3-D Secure is requested on every card payment (payment_method_options[card][request_three_d_secure]=any).
 *  - Customers pay on Stripe's hosted Checkout page (cards, Apple Pay, Google Pay); card data never touches our server.
 *  - Credit happens only after PaymentIntent.status == succeeded is confirmed server-side
 *    (on return and via the signed checkout.session.completed webhook).
 */
final class StripeService implements DepositGatewayInterface, WebhookCapableInterface
{
    const API_VERSION = '2024-06-20';
    const SIGNATURE_TOLERANCE = 300; // seconds

    /** @var GatewayConfig */
    private $cfg;

    public function __construct(GatewayConfig $cfg)
    {
        $this->cfg = $cfg;
    }

    public function publishableKey(): string
    {
        return $this->cfg->cred('publishable_key');
    }

    /**
     * Stripe Checkout Session (hosted): cards + Apple Pay / Google Pay (enable them in the Stripe
     * dashboard; no payment_method_types is sent, so Stripe shows every enabled method).
     */
    public function createCheckout(array $txn, array $customer): array
    {
        $inv = rawurlencode($txn['invoice_no']);
        $params = array(
            'mode' => 'payment',
            'client_reference_id' => $txn['invoice_no'],
            'success_url' => GatewayConfig::publicUrl('gateway/return.php?gw=stripe&inv=' . $inv) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => GatewayConfig::publicUrl('gateway/return.php?gw=stripe&cancel=1&inv=' . $inv),
            'expires_at' => time() + 1800,
            'line_items' => array(array(
                'quantity' => 1,
                'price_data' => array(
                    'currency' => 'usd',
                    'unit_amount' => Money::toMinorUnits($txn['payable_amount']),
                    'product_data' => array('name' => 'Midhub360 Wallet Top-up', 'description' => 'Credits ' . $txn['credit_amount'] . ' BDT to your wallet'),
                ),
            )),
            'metadata' => array('invoice_no' => $txn['invoice_no'], 'user_id' => (string) $txn['user_id'], 'credit_bdt' => (string) $txn['credit_amount']),
            'payment_intent_data' => array(
                'description' => 'Midhub360 wallet top-up ' . $txn['invoice_no'],
                'metadata' => array('invoice_no' => $txn['invoice_no'], 'user_id' => (string) $txn['user_id']),
            ),
            'payment_method_options' => array('card' => array('request_three_d_secure' => 'any')),
        );
        $email = isset($customer['email']) ? (string) $customer['email'] : '';
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && substr($email, -11) !== '@qa.invalid') {
            $params['customer_email'] = $email;
        }
        $cs = $this->api('POST', '/v1/checkout/sessions', $params, 'create_session', $txn['invoice_no'], $txn['invoice_no']);
        if (empty($cs['id']) || empty($cs['url'])) {
            throw new GatewayException('Stripe Checkout Session create failed: ' . $this->errorText($cs), 'Card payments are unavailable right now. Please try again later.');
        }
        return array('redirect_url' => (string) $cs['url'], 'gateway_payment_id' => (string) $cs['id'], 'state' => array());
    }

    public function completeReturn(array $txn, array $params): PaymentResult
    {
        if (isset($params['session_id']) && !hash_equals((string) $txn['gateway_payment_id'], (string) $params['session_id'])) {
            return PaymentResult::make(PaymentResult::FAILED, 'Payment reference mismatch.');
        }
        if (!empty($params['cancel']) && strpos((string) $txn['gateway_payment_id'], 'cs_') === 0) {
            // Expire the session first, so it can't be paid after we mark the invoice cancelled.
            try {
                $this->api('POST', '/v1/checkout/sessions/' . $txn['gateway_payment_id'] . '/expire', array(), 'expire_session', $txn['invoice_no']);
            } catch (GatewayException $e) {
                // already complete/expired: verify() below decides
            }
        }
        return $this->verify($txn);
    }

    public function verify(array $txn): PaymentResult
    {
        $id = (string) $txn['gateway_payment_id'];
        if ($id === '') {
            return PaymentResult::make(PaymentResult::FAILED, 'No Stripe payment was created for this invoice.');
        }
        if (strpos($id, 'pi_') === 0) {
            return $this->verifyIntent($txn);
        }
        if (!preg_match('/^cs_[A-Za-z0-9_]+$/', $id)) {
            throw new GatewayException('Invalid Checkout Session id');
        }
        $cs = $this->api('GET', '/v1/checkout/sessions/' . $id . '?expand%5B%5D=payment_intent', null, 'retrieve_session', $txn['invoice_no']);
        if (empty($cs['id'])) {
            throw new GatewayException('Stripe session retrieve failed: ' . $this->errorText($cs));
        }
        $pi = isset($cs['payment_intent']) && is_array($cs['payment_intent']) ? $cs['payment_intent'] : array();
        $r = PaymentResult::make(PaymentResult::PENDING, (string) (isset($cs['payment_status']) ? $cs['payment_status'] : ''), $this->slim($cs));
        $r->gatewayPaymentId = (string) $cs['id'];
        $r->gatewayTrxId = !empty($pi['latest_charge']) ? (string) $pi['latest_charge'] : (!empty($pi['id']) ? (string) $pi['id'] : (is_string(isset($cs['payment_intent']) ? $cs['payment_intent'] : null) ? $cs['payment_intent'] : null));
        $r->paidAmount = isset($cs['amount_total']) ? Money::fromPaisa((int) $cs['amount_total']) : null;
        $r->currency = isset($cs['currency']) ? strtoupper((string) $cs['currency']) : null;
        $r->invoiceNo = isset($cs['metadata']['invoice_no']) ? (string) $cs['metadata']['invoice_no'] : null;
        $status = isset($cs['status']) ? (string) $cs['status'] : '';
        $paid = isset($cs['payment_status']) ? (string) $cs['payment_status'] : '';
        if ($status === 'complete' && $paid === 'paid') {
            $r->status = PaymentResult::COMPLETED;
        } elseif ($status === 'expired') {
            $r->status = PaymentResult::CANCELLED;
            $r->message = 'Payment was cancelled or the session expired.';
        }
        return $r;
    }

    /** Legacy invoices created with a bare PaymentIntent. */
    private function verifyIntent(array $txn): PaymentResult
    {
        $pi = $this->retrieve((string) $txn['gateway_payment_id'], $txn['invoice_no']);
        $status = isset($pi['status']) ? (string) $pi['status'] : '';
        $r = PaymentResult::make(PaymentResult::PENDING, $status, $this->slim($pi));
        $r->gatewayPaymentId = isset($pi['id']) ? (string) $pi['id'] : null;
        $r->gatewayTrxId = isset($pi['latest_charge']) ? (string) $pi['latest_charge'] : (isset($pi['id']) ? (string) $pi['id'] : null);
        $r->paidAmount = isset($pi['amount_received']) ? Money::fromPaisa((int) $pi['amount_received']) : null;
        $r->currency = isset($pi['currency']) ? strtoupper((string) $pi['currency']) : null;
        $r->invoiceNo = isset($pi['metadata']['invoice_no']) ? (string) $pi['metadata']['invoice_no'] : null;
        if ($status === 'succeeded') {
            $r->status = PaymentResult::COMPLETED;
        } elseif ($status === 'canceled') {
            $r->status = PaymentResult::CANCELLED;
        } elseif ($status === 'requires_payment_method' && !empty($pi['last_payment_error'])) {
            $r->status = PaymentResult::FAILED;
            $r->message = isset($pi['last_payment_error']['message']) ? (string) $pi['last_payment_error']['message'] : 'Card payment failed.';
        }
        return $r;
    }

    /**
     * Authenticates the webhook and extracts identifiers.
     */
    public function parseWebhook(string $rawBody, array $headers): array
    {
        $header = isset($headers['stripe-signature']) ? (string) $headers['stripe-signature'] : '';
        if (!self::verifySignature($rawBody, $header, $this->cfg->cred('webhook_secret'), time())) {
            throw new GatewayException('Stripe webhook signature verification failed');
        }
        $event = json_decode($rawBody, true);
        if (!is_array($event) || empty($event['id']) || empty($event['type'])) {
            throw new GatewayException('Stripe webhook payload invalid');
        }
        $obj = isset($event['data']['object']) ? $event['data']['object'] : array();
        if (!empty($event['livemode']) !== $this->cfg->isLive()) {
            throw new GatewayException('Stripe webhook livemode does not match the configured mode');
        }
        $kind = isset($obj['object']) ? (string) $obj['object'] : '';
        return array(
            'event_id' => (string) $event['id'],
            'event_type' => (string) $event['type'],
            'invoice_no' => isset($obj['metadata']['invoice_no']) ? (string) $obj['metadata']['invoice_no'] : (isset($obj['client_reference_id']) ? (string) $obj['client_reference_id'] : null),
            'gateway_payment_id' => ($kind === 'checkout.session' || $kind === 'payment_intent') && isset($obj['id']) ? (string) $obj['id'] : null,
        );
    }
    /**
     * Verifies the Stripe-Signature header: t=<ts>,v1=<hex hmac-sha256("<ts>.<body>", whsec)>.
     * Rejects bad signatures and timestamps outside the tolerance window (replay protection).
     */
    public static function verifySignature(string $payload, string $header, string $secret, int $now): bool
    {
        if ($header === '' || $secret === '') {
            return false;
        }
        $ts = null;
        $sigs = array();
        foreach (explode(',', $header) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) !== 2) {
                continue;
            }
            if ($kv[0] === 't') {
                $ts = ctype_digit($kv[1]) ? (int) $kv[1] : null;
            } elseif ($kv[0] === 'v1') {
                $sigs[] = $kv[1];
            }
        }
        if ($ts === null || !$sigs || abs($now - $ts) > self::SIGNATURE_TOLERANCE) {
            return false;
        }
        $expected = hash_hmac('sha256', $ts . '.' . $payload, $secret);
        foreach ($sigs as $s) {
            if (hash_equals($expected, $s)) {
                return true;
            }
        }
        return false;
    }

    public function testConnection(): array
    {
        try {
            $b = $this->api('GET', '/v1/balance', null, 'test_balance', null);
            if (isset($b['object']) && $b['object'] === 'balance') {
                $live = !empty($b['livemode']);
                $ok = ($live === $this->cfg->isLive());
                return array('ok' => $ok, 'message' => $ok ? 'Secret key accepted (' . ($live ? 'live' : 'test') . ' mode).' : 'Key works but is a ' . ($live ? 'LIVE' : 'TEST') . ' key while the gateway is in ' . $this->cfg->mode . ' mode.');
            }
            return array('ok' => false, 'message' => $this->errorText($b));
        } catch (GatewayException $e) {
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    // ------------------------------------------------------------------------

    /** @return array<string,mixed> */
    private function retrieve(string $id, ?string $ref): array
    {
        if (!preg_match('/^pi_[A-Za-z0-9]+$/', $id)) {
            throw new GatewayException('Invalid PaymentIntent id');
        }
        $pi = $this->api('GET', '/v1/payment_intents/' . $id, null, 'retrieve_intent', $ref);
        if (empty($pi['id'])) {
            throw new GatewayException('Stripe retrieve failed: ' . $this->errorText($pi));
        }
        return $pi;
    }

    /** @return array<string,mixed> */
    private function api(string $method, string $path, ?array $params, string $title, ?string $ref, ?string $idempotencyKey = null): array
    {
        $headers = array(
            'Authorization' => 'Bearer ' . $this->cfg->cred('secret_key'),
            'Stripe-Version' => self::API_VERSION,
        );
        if ($idempotencyKey !== null) {
            $headers['Idempotency-Key'] = 'midhub-' . $idempotencyKey; // a retried create never makes a second intent
        }
        $resp = HttpClient::request($method, $this->cfg->url($path), $headers, $params, array(
            'timeout' => 30, 'log_type' => 'stripe', 'log_title' => $title, 'log_ref' => $ref,
        ));
        $json = $resp->json();
        if ($json === null) {
            throw new GatewayException("Stripe $title transport error: HTTP {$resp->status} {$resp->error}");
        }
        return $json;
    }

    private function slim(array $pi): array
    {
        $keep = array('id', 'object', 'status', 'payment_status', 'amount', 'amount_total', 'amount_received', 'currency', 'latest_charge', 'livemode', 'metadata');
        return array_intersect_key($pi, array_flip($keep));
    }

    private function errorText(array $res): string
    {
        if (isset($res['error']['message'])) {
            return (string) $res['error']['message'];
        }
        return json_encode($res);
    }
}
