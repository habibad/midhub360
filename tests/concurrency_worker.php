<?php
/**
 * One contender in a concurrency test. All contenders sleep until the same start time,
 * then race. Prints a single line: OK / NO / ERR <message>.
 *   php concurrency_worker.php <start_ts> wallet_debit <user_id> <amount>
 *   php concurrency_worker.php <start_ts> legacy_debit <user_id> <amount>
 *   php concurrency_worker.php <start_ts> deposit_settle <invoice_no>
 *   php concurrency_worker.php <start_ts> recharge_settle_fail <send_query_id>
 */
if (PHP_SAPI !== 'cli') {
    exit;
}
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Db;
use Midhub\Payment\DepositService;
use Midhub\Recharge\PaywellService;
use Midhub\Recharge\RechargeService;
use Midhub\Wallet\InsufficientBalanceException;
use Midhub\Wallet\Wallet;

$start = (float) $argv[1];
$op = $argv[2];
Db::raw("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
Db::conn(); // connect before the barrier so connection time doesn't serialize the race
while (microtime(true) < $start) {
    usleep(200);
}
try {
    switch ($op) {
        case 'wallet_debit':
            Db::transaction(function () use ($argv) {
                Wallet::debit((int) $argv[3], Wallet::RECHARGE, $argv[4], 'qa_race', 'race', 'concurrency test');
                usleep(20000); // widen the window while holding the lock
            });
            echo 'OK';
            break;
        case 'legacy_debit':
            // exactly the SQL that Request::atomic_debit() runs
            $n = Db::exec('UPDATE user SET `balance2` = `balance2` - ? WHERE id = ? AND `balance2` >= ?', 'did', array((float) $argv[4], (int) $argv[3], (float) $argv[4]));
            echo $n === 1 ? 'OK' : 'NO';
            break;
        case 'deposit_settle':
            $r = DepositService::settle($argv[3], 'reconcile');
            echo 'OK ' . $r['status'] . ' ' . $r['message'];
            break;
        case 'recharge_settle_fail':
            $s = RechargeService::settle((int) $argv[3], array('outcome' => PaywellService::FAILED, 'status_code' => '405', 'message' => 'qa forced fail', 'trx_id' => null, 'raw' => null));
            echo 'OK ' . $s;
            break;
        case 'transfer':
            \Midhub\Wallet\TransferService::toDownline((int) $argv[3], (int) $argv[4], 'balance2', $argv[5], 'race');
            echo 'OK';
            break;
        case 'modem_claim':
            $dev = \Midhub\Db::one('SELECT * FROM modem_devices WHERE id = ?', 'i', array((int) $argv[3]));
            $job = \Midhub\Modem\ModemGateway::claimNext($dev, $argv[4]);
            echo $job ? 'OK ' . $job['id'] : 'NO';
            break;
        default:
            echo 'ERR unknown op';
    }
} catch (InsufficientBalanceException $e) {
    echo 'NO';
} catch (\Throwable $e) {
    echo 'ERR ' . $e->getMessage();
}
