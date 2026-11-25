<?php
namespace Midhub\Ops;

use Midhub\Alert\AlertService;
use Midhub\Db;

/**
 * Automatic kill switch ("circuit breaker").
 *
 * After $threshold consecutive failures of a provider (or of one operator at a provider),
 * the breaker OPENS for $cooldown seconds: the service is taken out of rotation, money is
 * not put at risk, and the admin is alerted (dashboard + SMS). After the cooldown the next
 * request is allowed through; a success closes the breaker, another failure re-opens it.
 *
 * Keys in use: paywell, paywell:<operator>, deposit:<gateway>, voucher.
 */
final class CircuitBreaker
{
    const THRESHOLD = 5;
    const COOLDOWN = 600; // 10 minutes

    public static function isOpen(string $key): bool
    {
        $until = Db::scalar('SELECT opened_until FROM circuit_breakers WHERE cb_key = ? AND opened_until > NOW()', 's', array($key));
        return $until !== null;
    }

    public static function success(string $key): void
    {
        Db::exec('UPDATE circuit_breakers SET failures = 0, opened_until = NULL WHERE cb_key = ? AND (failures > 0 OR opened_until IS NOT NULL)', 's', array($key));
    }

    /** Records a failure; returns true when this failure opened the breaker. */
    public static function failure(string $key, string $error, int $threshold = self::THRESHOLD, int $cooldown = self::COOLDOWN): bool
    {
        Db::exec('INSERT INTO circuit_breakers (cb_key, failures, last_error) VALUES (?, 1, ?)
                  ON DUPLICATE KEY UPDATE failures = failures + 1, last_error = VALUES(last_error)', 'ss', array($key, substr($error, 0, 255)));
        $row = Db::one('SELECT failures, opened_until FROM circuit_breakers WHERE cb_key = ?', 's', array($key));
        $alreadyOpen = $row && $row['opened_until'] !== null && strtotime($row['opened_until']) > time();
        if (!$row || (int) $row['failures'] < $threshold || $alreadyOpen) {
            return false;
        }
        Db::exec('UPDATE circuit_breakers SET opened_until = NOW() + INTERVAL ? SECOND, failures = 0 WHERE cb_key = ?', 'is', array($cooldown, $key));
        AlertService::raise('breaker:' . $key, 'critical', 'Service paused automatically: ' . $key,
            "$threshold consecutive failures (last: " . substr($error, 0, 120) . "). '$key' is paused for " . intdiv($cooldown, 60) . ' minutes; requests are routed to the manual queue or hidden meanwhile.', 30);
        return true;
    }

    public static function reset(string $key): void
    {
        Db::exec('DELETE FROM circuit_breakers WHERE cb_key = ?', 's', array($key));
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(): array
    {
        return Db::all('SELECT cb_key, failures, opened_until, last_error, updated_at, (opened_until > NOW()) AS is_open FROM circuit_breakers ORDER BY is_open DESC, updated_at DESC');
    }
}
