<?php
namespace Midhub\Security;

use Midhub\Db;

/**
 * Transaction-PIN verification with lockout, for any account level.
 * 5 wrong PINs within 15 minutes lock PIN-protected actions for 15 minutes. Attempts
 * are counted in the database, so a new session or browser does not reset the counter.
 */
final class PinVerifier
{
    const MAX_FAILURES = 5;
    const WINDOW_MINUTES = 15;

    /** @return array{ok:bool,error:string} */
    public static function verify(int $userId, string $pin, ?int $requiredLevel = null): array
    {
        // Temporary IP ban: 5 wrong PINs from one IP (any account) in 15 min block PIN actions
        // from that IP, so one attacker cannot rotate through many accounts.
        $ip = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : 'cli';
        if ($ip !== 'cli') {
            $ipFailures = (int) Db::scalar(
                'SELECT COUNT(*) FROM security_pin_attempts WHERE ip = ? AND success = 0 AND attempted_at > (NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE)',
                's', array($ip)
            );
            if ($ipFailures >= self::MAX_FAILURES) {
                return array('ok' => false, 'error' => 'Too many wrong PIN attempts from your network. Please wait ' . self::WINDOW_MINUTES . ' minutes and try again.');
            }
        }
        $failures = (int) Db::scalar(
            'SELECT COUNT(*) FROM security_pin_attempts WHERE user_id = ? AND success = 0 AND attempted_at > (NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE)',
            'i', array($userId)
        );
        if ($failures >= self::MAX_FAILURES) {
            return array('ok' => false, 'error' => 'Too many wrong PIN attempts. Please wait ' . self::WINDOW_MINUTES . ' minutes and try again.');
        }
        if (!preg_match('/^\d{4,8}$/', $pin)) {
            self::record($userId, false);
            return array('ok' => false, 'error' => 'Invalid PIN.');
        }
        $row = Db::one('SELECT pin_code, level FROM user WHERE id = ? LIMIT 1', 'i', array($userId));
        $stored = $row ? (string) $row['pin_code'] : '';
        $levelOk = $row && ($requiredLevel === null || (int) $row['level'] === $requiredLevel);
        // Legacy PINs are unsalted MD5; password_hash() values are accepted for a future upgrade.
        $ok = $levelOk && $stored !== '' && (hash_equals($stored, md5($pin)) || (strlen($stored) > 32 && password_verify($pin, $stored)));
        self::record($userId, $ok);
        if (!$ok) {
            $left = self::MAX_FAILURES - $failures - 1;
            return array('ok' => false, 'error' => 'Wrong PIN.' . ($left > 0 ? " $left attempt(s) left before a temporary lock." : ' PIN actions are now locked for ' . self::WINDOW_MINUTES . ' minutes.'));
        }
        return array('ok' => true, 'error' => '');
    }

    private static function record(int $userId, bool $success): void
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : 'cli';
        Db::insert('INSERT INTO security_pin_attempts (user_id, success, ip) VALUES (?, ?, ?)', 'iis', array($userId, $success ? 1 : 0, $ip));
    }
}
