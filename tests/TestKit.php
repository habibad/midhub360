<?php
/**
 * Shared fixtures for tests/run.php and tests/http_smoke.php (CLI only).
 *  - QA user hierarchy (qa_admin L5 -> qa_l4 -> qa_l3 -> qa_l2 -> qa_l1 -> qa_l0)
 *  - payment_gateways backup/restore, so tests never leave the real config changed
 *  - the local mock provider server (tests/mock_providers.php on 127.0.0.1:8765)
 *  - cleanup of every row the tests create
 */
if (PHP_SAPI !== 'cli') {
    exit;
}
require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Midhub\Crypto;
use Midhub\Db;
use Midhub\Gateway\GatewayConfig;

final class TestKit
{
    const MOCK = 'http://127.0.0.1:8765';
    const PASSWORD = 'QaPass!2026';
    const PIN = '246810';

    /** @var array<string,int> */ public static $users = array();
    /** @var array */ private static $gatewayBackup = array();
    /** @var resource|null */ private static $server = null;
    /** @var int */ public static $pass = 0;
    /** @var int */ public static $fail = 0;
    /** @var array */ public static $failures = array();
    /** @var int */ public static $productMaxId = 0;
    /** @var array */ private static $settingsBackup = array();
    /** @var array */ private static $ratesBackup = array();
    /** @var string */ private static $startedAt = '';

    public static function strictSql(): void
    {
        // Production MySQL 8 defaults to strict mode; local XAMPP does not. Test as production.
        Db::raw("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public static function setUp(): void
    {
        self::strictSql();
        self::cleanupUsers(); // leftovers from an interrupted earlier run
        self::$gatewayBackup = Db::all('SELECT * FROM payment_gateways');
        self::snapshotProducts();
        self::$productMaxId = (int) Db::scalar('SELECT COALESCE(MAX(id),0) FROM voucher_products');
        self::$settingsBackup = Db::all('SELECT * FROM platform_settings');
        self::$ratesBackup = Db::all('SELECT * FROM recharge_commission_rates');
        self::$startedAt = (string) Db::scalar('SELECT NOW() - INTERVAL 2 SECOND');
        self::startMock();
        $chain = array('qa_admin' => 5, 'qa_l4' => 4, 'qa_l3' => 3, 'qa_l2' => 2, 'qa_l1' => 1, 'qa_l0' => 0);
        $parent = 0;
        foreach ($chain as $name => $level) {
            $id = Db::insert(
                "INSERT INTO user (country, fullname, user, pass, email, phone, address, last_ip, active, pin_code, level, city, created_by, pin, level3, level2, limit_balance)
                 VALUES ('Bangladesh', ?, ?, ?, ?, '01700000000', 'QA', '127.0.0.1', '1', ?, ?, 'Dhaka', ?, '0', 0, 0, '0')",
                'sssssii', array('QA ' . $name, $name, md5(self::PASSWORD), $name . '@qa.invalid', md5(self::PIN), $level, $parent)
            );
            self::$users[$name] = $id;
            $parent = $id;
        }
        Db::exec('DELETE FROM gateway_tokens');
        Db::exec('UPDATE user SET limit_balance = \'0\' WHERE id IN (' . implode(',', self::$users) . ')');
    }

    public static function tearDown(): void
    {
        try {
            self::restoreGateways();
        } catch (\Throwable $e) {
            echo "!! gateway restore failed: " . $e->getMessage() . "\n";
        }
        try {
            self::cleanupUsers();
            Db::exec('DELETE FROM voucher_products WHERE id > ?', 'i', array(self::$productMaxId));
            self::restoreProducts();
            Db::exec("DELETE FROM admin_alerts WHERE message LIKE '%QA-TEST%'");
            Db::exec('DELETE FROM circuit_breakers WHERE updated_at >= ?', 's', array(self::$startedAt));
            Db::exec("DELETE FROM admin_alerts WHERE alert_key LIKE 'breaker:%' AND created_at >= ?", 's', array(self::$startedAt));
            Db::exec("DELETE FROM admin_alerts WHERE (alert_key LIKE 'voucher\_%' OR alert_key = 'low_balance:popular_voucher') AND created_at >= ?", 's', array(self::$startedAt));
            Db::exec("DELETE FROM modem_devices WHERE created_by LIKE 'qa\_%' OR name LIKE 'QA %'");
            $keep = array_map(function ($r) { return $r['setting_key']; }, self::$settingsBackup);
            foreach (Db::all('SELECT setting_key FROM platform_settings') as $r) {
                if (!in_array($r['setting_key'], $keep, true)) {
                    Db::exec('DELETE FROM platform_settings WHERE setting_key = ?', 's', array($r['setting_key']));
                }
            }
            foreach (self::$settingsBackup as $r) {
                Db::exec('UPDATE platform_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?', 'sss', array($r['setting_value'], $r['updated_by'], $r['setting_key']));
            }
            foreach (self::$ratesBackup as $r) {
                Db::exec('UPDATE recharge_commission_rates SET rate = ? WHERE level = ?', 'si', array($r['rate'], (int) $r['level']));
            }
            Db::exec('DELETE FROM gateway_tokens');
        } catch (\Throwable $e) {
            echo "!! cleanup failed: " . $e->getMessage() . "\n";
        }
        self::stopMock();
    }

    private static function cleanupUsers(): void
    {
        $ids = array_map('intval', array_column(Db::all("SELECT id FROM user WHERE user LIKE 'qa\\_%' AND email LIKE '%@qa.invalid'"), 'id'));
        if (!$ids) {
            return;
        }
        $in = implode(',', $ids);
        $invoices = array_column(Db::all("SELECT invoice_no FROM gateway_transactions WHERE user_id IN ($in)"), 'invoice_no');
        if ($invoices) {
            $q = "'" . implode("','", array_map(function ($i) { return preg_replace('/[^A-Z0-9]/', '', $i); }, $invoices)) . "'";
            Db::raw("DELETE FROM webhook_events WHERE ref IN ($q)");
            Db::raw("DELETE FROM admin_alerts WHERE alert_key IN (" . implode(',', array_map(function ($i) { return "'deposit_review:" . preg_replace('/[^A-Z0-9]/', '', $i) . "'"; }, $invoices)) . ")");
        }
        $refs = array_column(Db::all("SELECT gateway_ref FROM send_query WHERE user_id IN ($in) AND gateway_ref IS NOT NULL"), 'gateway_ref');
        if ($refs) {
            Db::raw("DELETE FROM admin_alerts WHERE alert_key IN (" . implode(',', array_map(function ($r) { return "'recharge_stuck:" . preg_replace('/[^A-Z0-9]/', '', $r) . "'"; }, $refs)) . ")");
        }
        Db::raw("DELETE FROM reportserver WHERE ucid IN ('" . implode("','", $ids) . "')");
        Db::raw("DELETE FROM commission_payouts WHERE beneficiary_id IN ($in)");
        foreach (array('billpay_request' => 'user_id', 'send_query' => 'user_id', 'wallet_ledger' => 'user_id', 'gateway_transactions' => 'user_id', 'voucher_orders' => 'user_id',
                     'add_fund' => 'user_id', 'notification' => 'UserId', 'security_pin_attempts' => 'user_id', 'login_logs' => null, 'user_blocklist' => null) as $t => $col) {
            if ($col === null) {
                Db::raw("DELETE FROM `$t` WHERE username LIKE 'qa\\_%'");
            } else {
                Db::raw("DELETE FROM `$t` WHERE `$col` IN ($in)");
            }
        }
        Db::raw("DELETE FROM user WHERE id IN ($in)");
    }

    /**
     * The catalog sync marks unlisted products out of stock and re-prices marked-up ones, so the
     * real products are copied aside and restored column-for-column afterwards. A copy left by an
     * interrupted run is restored first.
     */
    private static function snapshotProducts(): void
    {
        if (Db::scalar("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'qa_voucher_products_backup'")) {
            echo "(restoring voucher products left by an interrupted test run)
";
            self::restoreProducts();
        }
        Db::raw('CREATE TABLE qa_voucher_products_backup AS SELECT * FROM voucher_products');
    }

    private static function restoreProducts(): void
    {
        if (!Db::scalar("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'qa_voucher_products_backup'")) {
            return;
        }
        $cols = array();
        foreach (Db::all('SHOW COLUMNS FROM qa_voucher_products_backup') as $col) {
            if ($col['Field'] !== 'id') {
                $cols[] = 'v.`' . $col['Field'] . '` = b.`' . $col['Field'] . '`';
            }
        }
        Db::raw('UPDATE voucher_products v JOIN qa_voucher_products_backup b ON b.id = v.id SET ' . implode(', ', $cols));
        Db::raw('DROP TABLE qa_voucher_products_backup');
    }

    private static function restoreGateways(): void
    {
        foreach (self::$gatewayBackup as $r) {
            Db::exec('UPDATE payment_gateways SET status=?, mode=?, sandbox_base_url=?, live_base_url=?, sandbox_credentials=?, live_credentials=?, extra_config=?,
                      charge_percentage=?, min_amount=?, max_amount=?, exchange_rate=?, updated_by=?, last_balance=?, last_balance_at=? WHERE id=?',
                'ssssssssssssssi', array($r['status'], $r['mode'], $r['sandbox_base_url'], $r['live_base_url'], $r['sandbox_credentials'], $r['live_credentials'],
                    $r['extra_config'], $r['charge_percentage'], $r['min_amount'], $r['max_amount'], $r['exchange_rate'], $r['updated_by'], $r['last_balance'], $r['last_balance_at'], (int) $r['id']));
        }
        Db::exec("DELETE FROM gateway_audit_log WHERE admin_user LIKE 'qa\\_%'");
        GatewayConfig::clearCache();
    }

    /** Point a gateway's SANDBOX side at the mock server with test credentials. */
    public static function configure(string $slug, array $over): void
    {
        $sets = array();
        $types = '';
        $vals = array();
        foreach ($over as $k => $v) {
            if ($k === 'creds') {
                $k = 'sandbox_credentials';
                $v = Crypto::encryptJson($v);
            } elseif ($k === 'extra') {
                $cur = json_decode((string) Db::scalar('SELECT extra_config FROM payment_gateways WHERE gateway_slug = ?', 's', array($slug)), true) ?: array();
                $v = json_encode(array_merge($cur, $v));
                $k = 'extra_config';
            }
            $sets[] = "`$k` = ?";
            $types .= 's';
            $vals[] = $v === null ? null : (string) $v;
        }
        $vals[] = $slug;
        Db::exec('UPDATE payment_gateways SET ' . implode(', ', $sets) . ' WHERE gateway_slug = ?', $types . 's', $vals);
        GatewayConfig::clearCache();
        Db::exec('DELETE FROM gateway_tokens WHERE gateway_slug = ?', 's', array($slug));
    }

    public static function setBalance(string $user, string $wallet, string $amount): void
    {
        Db::exec("UPDATE user SET `$wallet` = ?, limit_balance = '0' WHERE id = ?", 'si', array($amount, self::$users[$user]));
    }

    public static function balance(string $user, string $wallet): string
    {
        return (string) Db::scalar("SELECT `$wallet` FROM user WHERE id = ?", 'i', array(self::$users[$user]));
    }

    // ---------------- mock server ----------------
    private static function startMock(): void
    {
        @file_get_contents(self::MOCK . '/__reset');
        if (self::ping()) {
            return; // already running (e.g. started by another runner)
        }
        $php = PHP_BINARY;
        $cmd = escapeshellarg($php) . ' -S 127.0.0.1:8765 ' . escapeshellarg(__DIR__ . '/mock_providers.php');
        $spec = array(0 => array('pipe', 'r'), 1 => array('file', sys_get_temp_dir() . '/midhub_mock.log', 'a'), 2 => array('file', sys_get_temp_dir() . '/midhub_mock.log', 'a'));
        self::$server = proc_open($cmd, $spec, $pipes, __DIR__, null, array('bypass_shell' => true));
        for ($i = 0; $i < 50 && !self::ping(); $i++) {
            usleep(100000);
        }
        if (!self::ping()) {
            throw new \RuntimeException('mock provider server did not start');
        }
        @file_get_contents(self::MOCK . '/__reset');
    }

    private static function ping(): bool
    {
        $ctx = stream_context_create(array('http' => array('timeout' => 1, 'ignore_errors' => true)));
        $r = @file_get_contents(self::MOCK . '/__reset', false, $ctx);
        return $r !== false;
    }

    private static function stopMock(): void
    {
        if (is_resource(self::$server)) {
            $st = proc_get_status(self::$server);
            if (!empty($st['pid'])) {
                if (DIRECTORY_SEPARATOR === '\\') {
                    exec('taskkill /F /T /PID ' . (int) $st['pid'] . ' 2>NUL');
                } else {
                    posix_kill($st['pid'], 15);
                }
            }
            proc_close(self::$server);
        }
    }

    public static function mock(string $path): array
    {
        return json_decode((string) file_get_contents(self::MOCK . $path), true) ?: array();
    }

    // ---------------- assertions ----------------
    public static function check(string $name, bool $ok, string $detail = ''): void
    {
        if ($ok) {
            self::$pass++;
            echo "  \033[32mPASS\033[0m $name\n";
        } else {
            self::$fail++;
            self::$failures[] = $name . ($detail !== '' ? " -- $detail" : '');
            echo "  \033[31mFAIL\033[0m $name" . ($detail !== '' ? "\n       $detail" : '') . "\n";
        }
    }

    public static function eq(string $name, $expected, $actual): void
    {
        self::check($name, $expected == $actual, 'expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }

    public static function section(string $title): void
    {
        echo "\n\033[1m== $title ==\033[0m\n";
    }

    /** Runs $n copies of tests/concurrency_worker.php simultaneously; returns their stdout lines. */
    public static function parallel(int $n, array $args): array
    {
        $start = microtime(true) + 1.5; // shared start barrier
        $procs = array();
        for ($i = 0; $i < $n; $i++) {
            $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/concurrency_worker.php') . ' ' . sprintf('%.4f', $start) . ' ' . implode(' ', array_map('escapeshellarg', $args));
            $p = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, __DIR__, null, array('bypass_shell' => true));
            $procs[] = array($p, $pipes);
        }
        $out = array();
        foreach ($procs as $pp) {
            $out[] = trim(stream_get_contents($pp[1][1]) . stream_get_contents($pp[1][2]));
            proc_close($pp[0]);
        }
        return $out;
    }
}
