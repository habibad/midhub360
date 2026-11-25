<?php
namespace Midhub\Modem;

use Midhub\Commission\CommissionService;
use Midhub\Db;
use Midhub\Money;
use Midhub\Wallet\Wallet;

/**
 * SIM-modem gateway: Android phones with operator SIMs pull queued recharges and report results.
 *
 * Security (replaces the expired legacy scripts):
 *  - Every device has its own random bearer token, issued in Admin > SIM Modem Devices.
 *    Only its SHA-256 hash is stored; a device can be revoked without affecting others.
 *    Sent as "Authorization: Bearer <token>" (or the legacy "key" parameter for old apps).
 *  - All queries are prepared (the legacy key check was SQL-injectable).
 *  - Jobs are CLAIMED atomically (status 0 -> 4), so two phones never get the same recharge.
 *  - Success / failure settle a claimed row exactly once; failure refunds the Recharge wallet,
 *    success pays the 5-level commission. The legacy success marked EVERY row with the same
 *    phone+amount as successful, including old refunded ones.
 */
final class ModemGateway
{
    const ALIASES = array('gp' => 'grameenphone', 'grameenphone' => 'grameenphone', 'robi' => 'robi', 'rb' => 'robi',
        'bl' => 'banglalink', 'banglalink' => 'banglalink', 'airtel' => 'airtel', 'at' => 'airtel', 'teletalk' => 'teletalk', 'tt' => 'teletalk');

    /** Issues a new device token. Returns [device id, plaintext token (shown once)]. */
    public static function createDevice(string $name, string $operators, string $by): array
    {
        $token = 'mdm_' . bin2hex(random_bytes(24));
        $ops = array();
        foreach (preg_split('/[\s,]+/', strtolower($operators)) as $o) {
            if ($o !== '' && isset(self::ALIASES[$o])) {
                $ops[] = self::ALIASES[$o];
            }
        }
        $id = Db::insert('INSERT INTO modem_devices (name, token_hash, token_hint, operators, created_by) VALUES (?, ?, ?, ?, ?)', 'sssss',
            array(substr($name, 0, 80), hash('sha256', $token), substr($token, -6), implode(',', array_unique($ops)), $by));
        return array($id, $token);
    }

    /** @return array<string,mixed>|null the authenticated, active device */
    public static function authenticate(?string $token)
    {
        if ($token === null || !preg_match('/^mdm_[a-f0-9]{48}$/', $token)) {
            return null;
        }
        $d = Db::one('SELECT * FROM modem_devices WHERE token_hash = ? AND active = 1', 's', array(hash('sha256', $token)));
        if ($d) {
            Db::exec('UPDATE modem_devices SET last_seen_at = NOW(), last_ip = ? WHERE id = ?', 'si',
                array(substr(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli', 0, 45), (int) $d['id']));
        }
        return $d ?: null;
    }

    /** Token from "Authorization: Bearer ..." or the legacy key parameter. */
    public static function tokenFromRequest(): ?string
    {
        $h = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : '');
        if ($h === '' && function_exists('getallheaders')) {
            // Apache mod_php does not expose Authorization in $_SERVER (nginx + php-fpm does)
            foreach ((array) getallheaders() as $name => $value) {
                if (strcasecmp((string) $name, 'Authorization') === 0) {
                    $h = (string) $value;
                    break;
                }
            }
        }
        if (preg_match('/^Bearer\s+(\S+)$/i', $h, $m)) {
            return $m[1];
        }
        $k = isset($_REQUEST['key']) ? $_REQUEST['key'] : null;
        return is_string($k) ? trim($k) : null;
    }

    public static function normalizeOperator(string $op): ?string
    {
        $op = strtolower(trim($op));
        return isset(self::ALIASES[$op]) ? self::ALIASES[$op] : null;
    }

    /**
     * Claims the oldest queued recharge for an operator this device serves.
     * @return array<string,mixed>|null the claimed send_query row
     */
    public static function claimNext(array $device, string $operator)
    {
        $op = self::normalizeOperator($operator);
        $allowed = array_filter(explode(',', (string) $device['operators']));
        if ($op === null || ($allowed && !in_array($op, $allowed, true))) {
            return null;
        }
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $row = Db::one("SELECT id FROM send_query WHERE status = '0' AND operator = ? AND (gateway IS NULL OR gateway = '') ORDER BY id ASC LIMIT 1", 's', array($op));
            if (!$row) {
                return null;
            }
            $claimed = Db::exec("UPDATE send_query SET status = '4', gateway = 'modem', modem_device_id = ?, status_checked_at = NOW() WHERE id = ? AND status = '0'",
                'ii', array((int) $device['id'], (int) $row['id']));
            if ($claimed === 1) {
                return Db::one('SELECT * FROM send_query WHERE id = ?', 'i', array((int) $row['id']));
            }
            // another phone won this row; try the next one
        }
        return null;
    }

    /** Legacy update.php: mark a row as being processed by this device. */
    public static function markWaiting(array $device, int $id): bool
    {
        $n = Db::exec("UPDATE send_query SET status = '4', gateway = 'modem', modem_device_id = ?, status_checked_at = NOW() WHERE id = ? AND status = '0'",
            'ii', array((int) $device['id'], $id));
        if ($n === 1) {
            return true;
        }
        return (int) Db::scalar("SELECT COUNT(*) FROM send_query WHERE id = ? AND status = '4' AND modem_device_id = ?", 'ii', array($id, (int) $device['id'])) === 1;
    }

    /** Finds the in-progress row: by id, or the oldest one with this phone + amount on this device. */
    private static function findInProgress(array $device, ?int $id, string $number, string $amount): ?array
    {
        if ($id) {
            $r = Db::one("SELECT * FROM send_query WHERE id = ? AND status = '4' AND (modem_device_id = ? OR modem_device_id IS NULL) FOR UPDATE", 'ii', array($id, (int) $device['id']));
            return $r ?: null;
        }
        $number = self::normalizeNumber($number);
        if ($number === null || !preg_match('/^\d{1,6}$/', $amount)) {
            return null;
        }
        $r = Db::one("SELECT * FROM send_query WHERE phone = ? AND balance = ? AND status = '4' AND (modem_device_id = ? OR modem_device_id IS NULL) ORDER BY id ASC LIMIT 1 FOR UPDATE",
            'sii', array($number, (int) $amount, (int) $device['id']));
        return $r ?: null;
    }

    /** @return string 'ok' | 'not_found' */
    public static function success(array $device, ?int $id, string $number, string $amount, string $txn, string $msg): string
    {
        $done = Db::transaction(function () use ($device, $id, $number, $amount, $txn, $msg) {
            $row = self::findInProgress($device, $id, $number, $amount);
            if (!$row) {
                return null;
            }
            Db::exec("UPDATE send_query SET status = '1', txnid = ?, msg = ? WHERE id = ? AND status = '4'", 'ssi',
                array(substr($txn !== '' ? $txn : 'MODEM' . $row['id'], 0, 150), substr($msg !== '' ? $msg : 'Recharge done by SIM modem', 0, 250), (int) $row['id']));
            self::report($row, '1', $txn, $msg !== '' ? $msg : 'SIM modem success');
            return (int) $row['id'];
        });
        if ($done === null) {
            return 'not_found';
        }
        CommissionService::payForRecharge($done);
        return 'ok';
    }

    /** @return string 'ok' | 'not_found' */
    public static function failed(array $device, ?int $id, string $number, string $amount, string $reason): string
    {
        $done = Db::transaction(function () use ($device, $id, $number, $amount, $reason) {
            $row = self::findInProgress($device, $id, $number, $amount);
            if (!$row) {
                return false;
            }
            Db::exec("UPDATE send_query SET status = '3', msg = ? WHERE id = ? AND status = '4'", 'si', array(substr('Modem failed: ' . $reason, 0, 250), (int) $row['id']));
            Wallet::credit((int) $row['user_id'], Wallet::RECHARGE, Money::fromPaisa(Money::toPaisa((string) $row['balance'])), 'recharge_refund', 'SQ' . $row['id'], 'SIM modem failed: ' . substr($reason, 0, 80));
            self::report($row, '3', 'N/A', 'SIM modem failed, refunded: ' . $reason);
            return true;
        });
        return $done ? 'ok' : 'not_found';
    }

    /** Legacy report_msg.php: store the operator SMS against the matching recharge (no status change). */
    public static function storeReport(array $device, string $number, string $amount, string $from, string $msg, string $txnid, string $simBalance): string
    {
        $number = self::normalizeNumber($number);
        if ($number === null || !preg_match('/^\d{1,6}$/', $amount)) {
            return 'invalid';
        }
        $row = Db::one('SELECT * FROM send_query WHERE phone = ? AND balance = ? ORDER BY id DESC LIMIT 1', 'si', array($number, (int) $amount));
        if (!$row) {
            return 'not_found';
        }
        Db::insert('INSERT INTO reportserver (company, msgbody, ucid, amt, transactionid, number, f_id, datetime, status, balance, date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), \'1\', ?, CURDATE())',
            'ssssssss', array(substr($from, 0, 50), substr($msg, 0, 1000), (string) $row['user_id'], substr($amount, 0, 10), substr($txnid, 0, 50), $number, (string) $row['id'], substr($simBalance, 0, 50)));
        return 'ok';
    }

    public static function normalizeNumber(string $n): ?string
    {
        $n = preg_replace('/\D/', '', $n);
        if (strlen($n) === 13 && strpos($n, '880') === 0) {
            $n = substr($n, 2);
        } elseif (strlen($n) === 10) {
            $n = '0' . $n; // legacy app sent numbers without the leading 0
        }
        return preg_match('/^01[3-9]\d{8}$/', $n) ? $n : null;
    }

    private static function report(array $row, string $status, string $trx, string $msg): void
    {
        Db::insert('INSERT INTO reportserver (company, msgbody, ucid, amt, transactionid, number, f_id, datetime, status, balance, date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, \'0\', CURDATE())',
            'ssssssss', array(substr((string) $row['operator'], 0, 50), substr($msg, 0, 1000), (string) $row['user_id'], (string) $row['balance'],
                substr($trx !== '' ? $trx : 'N/A', 0, 50), (string) $row['phone'], (string) $row['id'], $status));
    }
}
