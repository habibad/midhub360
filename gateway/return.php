<?php
/**
 * Customer return from a hosted checkout (bKash, Nagad, Upay, UddoktaPay, Stripe).
 *
 *   gateway/return.php?gw=<slug>&inv=<our invoice>[&provider params...]
 *
 * The redirect is only a TRIGGER. DepositService::settle() locks the invoice and asks the
 * gateway server-to-server for the real status before crediting anything, so a forged or
 * replayed return URL can never add money. Refreshing this page is harmless (idempotent).
 */
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Gateway\GatewayRegistry;
use Midhub\Http\HttpClient;
use Midhub\Payment\DepositService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

$h = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

$params = array();
foreach (array_merge($_GET, $_POST) as $k => $v) {
    if (is_string($v) && strlen($v) <= 512) {
        $params[(string) $k] = $v;
    }
}
// Some providers append "?a=b" to a callback URL that already has a query string, which
// glues their parameters onto our last parameter ("inv"). Split them back out.
$inv = isset($params['inv']) ? $params['inv'] : '';
if (strpos($inv, '?') !== false) {
    list($inv, $tail) = explode('?', $inv, 2);
    parse_str($tail, $extra);
    foreach ((array) $extra as $k => $v) {
        if (is_string($v) && !isset($params[$k])) {
            $params[$k] = $v;
        }
    }
}
$gw = isset($params['gw']) ? $params['gw'] : (defined('MIDHUB_RETURN_GW') ? MIDHUB_RETURN_GW : '');

// Query-free callbacks (gateway/bkash_callback.php, gateway/nagad_callback.php): resolve our
// invoice from the provider's own identifiers. settle() still verifies everything server-side.
if ($inv === '' && $gw === 'bkash' && !empty($params['paymentID']) && preg_match('/^[A-Za-z0-9]{6,64}$/', $params['paymentID'])) {
    $t = DepositService::findByGatewayPaymentId('bkash', $params['paymentID']);
    $inv = $t ? $t['invoice_no'] : '';
} elseif ($inv === '' && $gw === 'nagad') {
    if (!empty($params['order_id']) && preg_match('/^MH\d{18}$/', $params['order_id'])) {
        $inv = $params['order_id'];
    } elseif (!empty($params['payment_ref_id']) && preg_match('/^[A-Za-z0-9_-]{6,80}$/', $params['payment_ref_id'])) {
        $t = DepositService::findByGatewayPaymentId('nagad', $params['payment_ref_id']);
        $inv = $t ? $t['invoice_no'] : '';
    }
}

$state = 'error';
$message = 'This payment link is invalid.';
$txn = null;

if (GatewayRegistry::exists($gw) && preg_match('/^MH\d{18}$/', $inv)) {
    HttpClient::log($gw, 'return', $inv, 'gateway/return.php', $params, null);
    $txn = DepositService::find($inv);
    if (!$txn || $txn['gateway_slug'] !== $gw) {
        $txn = null;
        $message = 'Payment not found.';
    } else {
        try {
            $r = DepositService::settle($inv, 'return', $params);
            $state = $r['status'];
            $message = $r['message'];
            $txn = $r['txn'];
            if ($state === 'completed') {
                // follow-up action paid for by this deposit (e.g. a voucher / Player ID top-up)
                $intentResult = DepositService::runIntent($inv);
                if (is_array($intentResult)) {
                    $message .= ' ' . $intentResult['message'];
                }
            }
        } catch (\Throwable $e) {
            error_log("gateway return $gw $inv: " . $e->getMessage());
            $state = 'pending';
            $message = 'We could not confirm your payment with the provider yet. If money was deducted it will be credited automatically within a few minutes. Your invoice number is below.';
        }
    }
}

$isOwner = $txn && isset($_SESSION['us_id']) && (int) $_SESSION['us_id'] === (int) $txn['user_id'];
$styles = array(
    'completed' => array('ok', 'Payment successful'),
    'pending' => array('wait', 'Payment processing'),
    'review' => array('wait', 'Payment under review'),
    'cancelled' => array('fail', 'Payment cancelled'),
    'failed' => array('fail', 'Payment failed'),
    'error' => array('fail', 'Something went wrong'),
);
list($cls, $title) = isset($styles[$state]) ? $styles[$state] : $styles['error'];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $h($title) ?> - Midhub360</title>
<style>
  :root { --bg:#f4f6f8; --card:#fff; --text:#1f2933; --muted:#616e7c; --ok:#0f7b4b; --okbg:#e3f6ec; --wait:#8a5a00; --waitbg:#fff4d6; --fail:#b42318; --failbg:#fdecea; --line:#e4e7eb; --btn:#2563eb; }
  @media (prefers-color-scheme: dark) { :root { --bg:#111418; --card:#1b1f24; --text:#e4e7eb; --muted:#9aa5b1; --okbg:#12301f; --ok:#6ee7a8; --waitbg:#33290f; --wait:#fcd34d; --failbg:#3a1714; --fail:#fca5a5; --line:#2d333b; --btn:#3b82f6; } }
  body { margin:0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background:var(--bg); color:var(--text); }
  .wrap { max-width:480px; margin:48px auto; padding:0 16px; }
  .card { background:var(--card); border:1px solid var(--line); border-radius:12px; padding:28px 24px; text-align:center; }
  .pill { display:inline-block; padding:8px 16px; border-radius:999px; font-weight:600; margin-bottom:14px; }
  .ok { background:var(--okbg); color:var(--ok); } .wait { background:var(--waitbg); color:var(--wait); } .fail { background:var(--failbg); color:var(--fail); }
  p { color:var(--muted); line-height:1.5; }
  table { width:100%; border-collapse:collapse; margin:18px 0 6px; font-size:14px; text-align:left; }
  td { padding:8px 4px; border-top:1px solid var(--line); } td:last-child { text-align:right; font-variant-numeric: tabular-nums; }
  a.btn { display:inline-block; margin-top:18px; background:var(--btn); color:#fff; text-decoration:none; padding:10px 18px; border-radius:8px; }
</style>
<?php if ($state === 'pending'): ?><meta http-equiv="refresh" content="8"><?php endif; ?>
</head>
<body>
<div class="wrap"><div class="card">
  <div class="pill <?= $cls ?>"><?= $h($title) ?></div>
  <p><?= $h($message) ?></p>
  <?php if ($txn): ?>
  <table>
    <tr><td>Invoice</td><td><?= $h($txn['invoice_no']) ?></td></tr>
    <?php if ($isOwner): ?>
    <tr><td>Paid</td><td><?= $h($txn['payable_amount'] . ' ' . $txn['currency']) ?></td></tr>
    <tr><td>Wallet credit</td><td><?= $h($txn['credit_amount']) ?> BDT</td></tr>
    <?php if (!empty($txn['gateway_trx_id'])): ?><tr><td>Transaction ID</td><td><?= $h($txn['gateway_trx_id']) ?></td></tr><?php endif; ?>
    <?php endif; ?>
  </table>
  <?php endif; ?>
  <?php if ($state === 'pending'): ?><p style="font-size:13px">This page refreshes automatically.</p><?php endif; ?>
  <?php $isVoucher = $txn && !empty($txn['intent']); ?>
  <a class="btn" href="<?= $h(rtrim(APP_URL, '/') . ($isVoucher ? '/customer/vouchers.php' : '/customer/add_money.php')) ?>"><?= $isVoucher ? 'View my vouchers' : 'Back to Add Money' ?></a>
</div></div>
</body>
</html>
