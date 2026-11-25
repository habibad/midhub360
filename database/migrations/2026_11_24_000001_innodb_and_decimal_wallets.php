<?php
/**
 * 1) Convert every MyISAM table to InnoDB.
 *    MyISAM has no transactions and no row locks, so `START TRANSACTION` and
 *    `SELECT ... FOR UPDATE` silently do nothing on it. The `user` table (all three
 *    wallets) was MyISAM, which made atomic wallet operations impossible.
 * 2) Store the three wallets as DECIMAL(14,2) instead of INT / VARCHAR.
 *    INT truncated fractional commission/charges; VARCHAR compared as strings.
 */
use Midhub\Db;
use Midhub\Migration\Schema;

return function () {
    $notes = array();

    // --- 1. Engine conversion -------------------------------------------------
    $myisam = Db::all("SELECT TABLE_NAME AS t FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND ENGINE = 'MyISAM'");
    $failed = array();
    foreach ($myisam as $row) {
        $t = $row['t'];
        try {
            Db::raw("ALTER TABLE `$t` ENGINE=InnoDB");
        } catch (\Throwable $e) {
            $failed[$t] = $e->getMessage();
        }
    }
    foreach (array('user', 'bkash_send', 'send_query', 'reseller_balance', 'add_fund') as $critical) {
        if (Schema::hasTable($critical) && Schema::engine($critical) !== 'innodb') {
            throw new \RuntimeException("Table `$critical` must be InnoDB for atomic wallet operations: " . (isset($failed[$critical]) ? $failed[$critical] : 'conversion failed'));
        }
    }
    $notes[] = count($myisam) . ' MyISAM table(s) converted';
    if ($failed) {
        $notes[] = 'non-critical tables left as MyISAM: ' . implode(', ', array_keys($failed));
    }

    // --- 2. Wallet column types ------------------------------------------------
    foreach (array('balance', 'balance2', 'bkash_balance') as $col) {
        $type = Schema::columnType('user', $col);
        if (strpos($type, 'decimal(14,2)') === 0) {
            continue;
        }
        // Pre-flight: refuse to convert if any value is not a plain number.
        $bad = (int) Db::scalar("SELECT COUNT(*) FROM `user` WHERE TRIM(`$col`) NOT REGEXP '^-?[0-9]+(\\\\.[0-9]+)?$'");
        if ($bad > 0) {
            throw new \RuntimeException("user.$col has $bad non-numeric value(s). Fix them first: SELECT id, `$col` FROM user WHERE TRIM(`$col`) NOT REGEXP '^-?[0-9]+(\\\\.[0-9]+)?$'");
        }
        Db::raw("ALTER TABLE `user` MODIFY `$col` DECIMAL(14,2) NOT NULL DEFAULT 0.00");
        $notes[] = "user.$col: $type -> decimal(14,2)";
    }

    return implode('; ', $notes);
};
