<?php
/**
 * Midhub360 background worker. Run every minute from cron:
 *
 *   * * * * * www-data /usr/bin/php /var/www/midhub360.com/cron/worker.php >> /var/log/midhub-worker.log 2>&1
 *
 * Tasks (each isolated: one failing task never blocks the others):
 *   1. Paywell recharges stuck in "processing" -> enquiry -> settle (never blind refunds)
 *   2. Online deposits still pending           -> server-to-server verify -> settle
 *   3. Voucher orders still processing         -> provider status -> settle
 *   4. Provider low-balance check (every 15 min) -> admin alert below the threshold
 *
 * An exclusive lock prevents overlapping runs if one ever takes longer than a minute.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Alert\AlertService;
use Midhub\Db;
use Midhub\Gateway\GatewayConfig;
use Midhub\Payment\DepositService;
use Midhub\Recharge\PaywellService;
use Midhub\Recharge\RechargeService;
use Midhub\Voucher\VoucherService;

$lockFile = MIDHUB_ROOT . '/storage/worker.lock';
if (!is_dir(dirname($lockFile))) {
    @mkdir(dirname($lockFile), 0700, true);
}
$lock = fopen($lockFile, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    echo date('c') . " worker already running, skipping\n";
    exit(0);
}

$log = function ($task, $msg) {
    echo date('Y-m-d H:i:s') . " [$task] $msg\n";
};
$task = function ($name, callable $fn) use ($log) {
    try {
        $out = $fn();
        if ($out !== null && $out !== '') {
            $log($name, is_string($out) ? $out : json_encode($out));
        }
    } catch (\Throwable $e) {
        $log($name, 'ERROR ' . $e->getMessage());
    }
};

// 1. Recharge status polling
$task('recharge', function () {
    $c = RechargeService::poll(50);
    return array_sum($c) ? $c : null;
});

// 2. Deposit reconciliation
$task('deposits', function () {
    $rows = Db::all("SELECT invoice_no, created_at, gateway_payment_id FROM gateway_transactions
                     WHERE status IN ('pending','initiated') AND created_at < NOW() - INTERVAL 3 MINUTE
                       AND updated_at < NOW() - INTERVAL 2 MINUTE
                     ORDER BY id LIMIT 30");
    $out = array();
    foreach ($rows as $r) {
        $age = time() - strtotime($r['created_at']);
        try {
            $res = DepositService::settle($r['invoice_no'], 'reconcile');
            $status = $res['status'];
            if ($status === 'completed') {
                DepositService::runIntent($r['invoice_no']);
            }
        } catch (\Throwable $e) {
            $status = 'error: ' . substr($e->getMessage(), 0, 80);
        }
        if ($status === 'pending') {
            // touch updated_at so the row waits before the next check
            Db::exec('UPDATE gateway_transactions SET updated_at = NOW() WHERE invoice_no = ?', 's', array($r['invoice_no']));
            if ($age > 86400) {
                // checkout sessions expire long before 24h: the customer never paid
                Db::exec("UPDATE gateway_transactions SET status = 'cancelled', failure_reason = 'expired: no confirmed payment within 24h' WHERE invoice_no = ? AND status IN ('pending','initiated')", 's', array($r['invoice_no']));
                $status = 'expired';
            }
        }
        $out[$r['invoice_no']] = $status;
    }
    return $out ?: null;
});

// 2b. Intents of completed deposits that have not run yet
$task('intents', function () {
    $rows = Db::all("SELECT invoice_no FROM gateway_transactions WHERE status = 'completed' AND intent IS NOT NULL AND intent_done_at IS NULL AND completed_at < NOW() - INTERVAL 1 MINUTE LIMIT 20");
    foreach ($rows as $r) {
        DepositService::runIntent($r['invoice_no']);
    }
    return $rows ? count($rows) . ' intent(s) run' : null;
});

// 2c. Utility bills processing at the aggregator
$task('billpay', function () {
    $c = \Midhub\BillPay\BillPayment::poll(30);
    return array_sum($c) ? $c : null;
});

// 3. Voucher order polling
$task('vouchers', function () {
    $c = VoucherService::poll(30);
    return array_sum($c) ? $c : null;
});

// 3b. Voucher catalog: continue an unfinished sync; start a fresh pass nightly (03:00-03:59)
$task('voucher_sync', function () {
    $svc = \Midhub\Voucher\PopularVoucherService::load();
    if (!$svc->config()->isActive() || $svc->isMock()) {
        return null;
    }
    $last = json_decode(\Midhub\Ops\Settings::get(VoucherService::SYNC_LAST, ''), true);
    $stale = !is_array($last) || empty($last['at']) || strtotime($last['at']) < time() - 20 * 3600;
    if (!VoucherService::syncProgress() && !($stale && (int) date('G') === 3)) {
        return null;
    }
    $r = VoucherService::syncCatalog(40.0);
    return ($r['done'] ? 'done' : 'in progress') . ": {$r['fetched']}/" . ($r['total'] !== null ? $r['total'] : '?') . ", new {$r['new']}, delisted {$r['delisted']}";
});

// 3c. Popular Vouchers distributor balance (every 15 minutes); also refreshes the key's environment
$task('voucher_balance', function () {
    $svc = \Midhub\Voucher\PopularVoucherService::load();
    if (!$svc->config()->isActive() || $svc->isMock()) {
        return null;
    }
    $row = Db::one("SELECT last_balance_at FROM payment_gateways WHERE gateway_slug = 'popular_voucher'");
    if ($row && $row['last_balance_at'] && strtotime($row['last_balance_at']) > time() - 900) {
        return null;
    }
    $bal = $svc->balance();
    Db::exec("UPDATE payment_gateways SET last_balance = ?, last_balance_at = NOW() WHERE gateway_slug = 'popular_voucher'", 's', array($bal));
    $threshold = (float) $svc->config()->extra('low_balance_threshold');
    if ($bal !== null && $threshold > 0 && (float) $bal < $threshold) {
        AlertService::raise('low_balance:popular_voucher', 'critical', 'Voucher provider balance low',
            "Popular Vouchers distributor balance is $bal BDT, below the alert threshold of " . number_format($threshold, 2, '.', '') . ' BDT. Top up, or voucher orders will fail (and be refunded).', 120);
    }
    if ($svc->checkEnvironment() !== '') {
        AlertService::raise('voucher_env_mismatch', 'critical', 'Voucher store blocked', $svc->checkEnvironment(), 360);
    }
    return $bal !== null ? "popular_voucher balance $bal" : null;
});

// 4. Low provider balance (every 15 minutes)
$task('low_balance', function () {
    $svc = PaywellService::active();
    if (!$svc) {
        return null;
    }
    $row = Db::one("SELECT last_balance_at FROM payment_gateways WHERE gateway_slug = 'paywell'");
    if ($row && $row['last_balance_at'] && strtotime($row['last_balance_at']) > time() - 900) {
        return null;
    }
    $bal = $svc->balance();
    Db::exec("UPDATE payment_gateways SET last_balance = ?, last_balance_at = NOW() WHERE gateway_slug = 'paywell'", 's', array($bal));
    if ($bal === null) {
        AlertService::raise('balance_unknown:paywell', 'warning', 'Paywell balance unavailable', 'The Paywell balance endpoint did not return a balance. Check the gateway settings.', 360);
        return 'paywell balance unavailable';
    }
    $threshold = (float) GatewayConfig::load('paywell')->extra('low_balance_threshold');
    if ($threshold > 0 && (float) $bal < $threshold) {
        AlertService::raise('low_balance:paywell', 'critical', 'Paywell balance low',
            "Paywell retailer balance is $bal BDT, below the alert threshold of " . number_format($threshold, 2, '.', '') . ' BDT. Top up soon, or recharges will start failing.', 120);
    }
    return "paywell balance $bal";
});

flock($lock, LOCK_UN);
fclose($lock);
