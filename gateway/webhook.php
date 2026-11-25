<?php
/**
 * Server-to-server notifications (IPN / webhooks).
 *
 *   gateway/webhook.php?gw=stripe           Stripe-Signature HMAC-SHA256 verified, 5-min replay window
 *   gateway/webhook.php?gw=uddoktapay       RT-UDDOKTAPAY-API-KEY header verified (constant-time)
 *   gateway/webhook.php?gw=paywell          trigger only: outcome fetched via enquiry
 *   gateway/webhook.php?gw=popular_voucher  X-Signature HMAC verified, then status fetched
 *
 * Even an authenticated webhook only TRIGGERS a server-to-server verification. Events are
 * de-duplicated in webhook_events, and settlement is exactly-once under a row lock.
 * Non-2xx responses make the provider retry, so we return 500 only on transient errors.
 */
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Db;
use Midhub\Gateway\GatewayConfig;
use Midhub\Gateway\GatewayException;
use Midhub\Gateway\GatewayFactory;
use Midhub\Gateway\WebhookCapableInterface;
use Midhub\Http\HttpClient;
use Midhub\Payment\DepositService;
use Midhub\Recharge\RechargeService;
use Midhub\Voucher\PopularVoucherService;
use Midhub\Voucher\VoucherService;

header('Content-Type: application/json');

function wh_respond(int $code, string $msg): void
{
    http_response_code($code);
    echo json_encode(array('result' => $msg));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    wh_respond(405, 'POST only');
}
$gw = isset($_GET['gw']) ? (string) $_GET['gw'] : '';
$raw = (string) file_get_contents('php://input');
if (strlen($raw) > 256000) {
    wh_respond(413, 'payload too large');
}
$headers = array();
foreach ($_SERVER as $k => $v) {
    if (strpos($k, 'HTTP_') === 0) {
        $headers[strtolower(str_replace('_', '-', substr($k, 5)))] = (string) $v;
    }
}
if (isset($_SERVER['CONTENT_TYPE'])) {
    $headers['content-type'] = (string) $_SERVER['CONTENT_TYPE'];
}
$decoded = json_decode($raw, true);
HttpClient::log($gw ?: 'unknown', 'webhook_in', null, 'gateway/webhook.php?gw=' . $gw, array('headers' => $headers), is_array($decoded) ? $decoded : substr($raw, 0, 2000));

try {
    switch ($gw) {
        case 'stripe':
        case 'uddoktapay':
            $cfg = GatewayConfig::load($gw, true);
            $svc = GatewayFactory::deposit($gw, $cfg);
            if (!$svc instanceof WebhookCapableInterface) {
                wh_respond(404, 'no webhook support');
            }
            try {
                $info = $svc->parseWebhook($raw, $headers);
            } catch (GatewayException $e) {
                error_log("webhook $gw rejected: " . $e->getMessage());
                wh_respond(400, 'invalid signature');
            }
            // de-duplicate: each provider event is processed once
            Db::exec('INSERT IGNORE INTO webhook_events (gateway_slug, event_id, event_type, ref) VALUES (?, ?, ?, ?)', 'ssss',
                array($gw, substr($info['event_id'], 0, 150), substr($info['event_type'], 0, 80), $info['invoice_no']));
            $ev = Db::one('SELECT id, processed FROM webhook_events WHERE gateway_slug = ? AND event_id = ?', 'ss', array($gw, substr($info['event_id'], 0, 150)));
            if ($ev && (int) $ev['processed'] === 1) {
                wh_respond(200, 'duplicate ignored');
            }
            if ($gw === 'stripe' && !in_array($info['event_type'], array('checkout.session.completed', 'checkout.session.async_payment_succeeded', 'checkout.session.async_payment_failed', 'checkout.session.expired', 'payment_intent.succeeded', 'payment_intent.payment_failed', 'payment_intent.canceled'), true)) {
                Db::exec('UPDATE webhook_events SET processed = 1, result = ? WHERE id = ?', 'si', array('ignored type', (int) $ev['id']));
                wh_respond(200, 'event type ignored');
            }
            $txn = null;
            if (!empty($info['invoice_no']) && preg_match('/^MH\d{18}$/', $info['invoice_no'])) {
                $txn = DepositService::find($info['invoice_no']);
            }
            if (!$txn && !empty($info['gateway_payment_id'])) {
                $txn = DepositService::findByGatewayPaymentId($gw, $info['gateway_payment_id']);
            }
            if (!$txn || $txn['gateway_slug'] !== $gw) {
                Db::exec('UPDATE webhook_events SET processed = 1, result = ? WHERE id = ?', 'si', array('no matching invoice', (int) $ev['id']));
                wh_respond(200, 'no matching invoice');
            }
            if ($gw === 'uddoktapay' && empty($txn['gateway_payment_id']) && !empty($info['gateway_payment_id'])) {
                // Candidate id from an authenticated webhook; settle() re-verifies that the provider
                // echoes OUR invoice for it before anything is credited.
                Db::exec('UPDATE gateway_transactions SET gateway_payment_id = ? WHERE id = ? AND gateway_payment_id IS NULL', 'si', array($info['gateway_payment_id'], (int) $txn['id']));
            }
            $r = DepositService::settle($txn['invoice_no'], 'webhook');
            if ($r['status'] === 'completed') {
                DepositService::runIntent($txn['invoice_no']);
            }
            Db::exec('UPDATE webhook_events SET processed = 1, result = ? WHERE id = ?', 'si', array(substr($txn['invoice_no'] . ' ' . $r['status'], 0, 255), (int) $ev['id']));
            wh_respond(200, $r['status']);
            break;

        case 'paywell':
            $data = is_array($decoded) ? $decoded : array();
            if (!$data) {
                parse_str($raw, $data);
            }
            $ref = '';
            foreach (array('ref_id', 'refId', 'reference', 'ref', 'client_ref') as $k) {
                if (!empty($data[$k]) && is_string($data[$k])) {
                    $ref = $data[$k];
                    break;
                }
                if (!empty($data['data'][$k]) && is_string($data['data'][$k])) {
                    $ref = $data['data'][$k];
                    break;
                }
            }
            if (!preg_match('/^RC\d{18}$/', $ref)) {
                wh_respond(200, 'no recognizable reference');
            }
            $counts = RechargeService::poll(1, $ref);
            wh_respond(200, 'checked ' . json_encode($counts));
            break;

        case 'popular_voucher':
            $svc = PopularVoucherService::load();
            if (!$svc->verifyWebhook($raw, $headers)) {
                wh_respond(400, 'invalid signature');
            }
            $data = is_array($decoded) ? $decoded : array();
            $ref = '';
            foreach (array('reference', 'order_ref', 'client_reference') as $k) {
                if (!empty($data[$k]) && is_string($data[$k])) {
                    $ref = $data[$k];
                }
                if (!empty($data['data'][$k]) && is_string($data['data'][$k])) {
                    $ref = $data['data'][$k];
                }
            }
            $order = preg_match('/^VO\d{18}$/', $ref) ? Db::one("SELECT id, order_no, provider_order_id, mode FROM voucher_orders WHERE order_no = ? AND status = 'processing'", 's', array($ref)) : null;
            if (!$order || $order['mode'] !== $svc->config()->mode) {
                wh_respond(200, 'no matching processing order');
            }
            $res = $svc->orderStatus((string) $order['provider_order_id'], $order['order_no']);
            wh_respond(200, VoucherService::settle((int) $order['id'], $res));
            break;

        default:
            wh_respond(404, 'unknown gateway');
    }
} catch (\Throwable $e) {
    error_log("webhook $gw error: " . $e->getMessage());
    wh_respond(500, 'temporary error, please retry');
}
